<?php
/*
 *  +----------------------------------------------------------------------
 *  | ViSwoole [基于swoole开发的高性能快速开发框架]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2024
 *  +----------------------------------------------------------------------
 *  | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: ZhuChongLin <8210856@qq.com>
 *  +----------------------------------------------------------------------
 */

declare (strict_types=1);

namespace ViSwoole\Log\Drives;

use Override;
use Swoole\Coroutine;
use Swoole\Timer;
use ViSwoole\Log\LogDrive;
use ViSwoole\Log\LogManager;

class File extends LogDrive
{
  /**
   * @param int $storageDays 日志存储的天数（仅在协程环境中有效）
   * @param int $maxFiles 最大日志文件数量（针对日志级别）
   * @param int $fileSize 日志文件大小
   * @param string $dateFormat 日期格式传入timestamp为时间戳格式
   * @param string $logFormat 日志格式
   * @param bool $json 是否json存储
   * @param int $json_flags json格式化参数
   * @param string $log_dir 日志存储目录路径
   */
  public function __construct(
    protected int    $storageDays = 7,
    protected int    $maxFiles = 30,
    protected int    $fileSize = 1024 * 1024 * 10,
    protected string $dateFormat = 'c',
    protected string $logFormat = '[%timestamp][%level]: %message %context in %source',
    protected bool   $json = true,
    protected int    $json_flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    protected string $log_dir = BASE_PATH . '/runtime/logs',
  )
  {
    // 启动定时器删除日志 非协程环境则只在服务启动时清除一次日志文件
    if (Coroutine::getuid() !== -1) {
      $this->startDailyTimer();
    } else {
      $this->clearExpireLog();
    }
  }

  /**
   * 该方法用于启用计时器，在每日凌晨进行删除日志文件
   *
   * @return void
   */
  private function startDailyTimer(): void
  {
    // 计算距离下一个午夜的秒数
    $now = time();
    $nextMidnight = strtotime('tomorrow');
    $secondsUntilMidnight = $nextMidnight - $now;
    // 启动Swoole定时器，在距离午夜的秒数之后执行 deleteExpiredLogs 方法
    Timer::after($secondsUntilMidnight * 1000, function () {
      $this->clearExpireLog();
      // 之后每隔一天（86400 秒）再次执行 deleteExpiredLogs 方法
      Timer::tick(86400 * 1000, function () {
        $this->clearExpireLog();
      });
    });
  }

  /**
   * 清除日志
   *
   * @access public
   * @param int|null $days 天数，大于该天数的文件视为过期
   * @param string|null $level 错误级别
   * @return void
   */
  public function clearExpireLog(?int $days = null, ?string $level = null): void
  {
    $days = is_null($days) ? $this->storageDays : $days;
    $rootDir = rtrim($this->log_dir, '/');
    // 匹配所有文件和目录
    $levelDirs = glob("$rootDir/*");
    // 当前日期
    $currentDate = (int)date('Ymd');
    foreach ($levelDirs as $dateDir) {
      // 目录名则是日期
      $date = (int)basename($dateDir);
      // 如果当前日期减去目录日期 大于最大存储的过期天数 则删除日志
      if ($currentDate - $date > $days) $this->rmdir($dateDir, $level);
    }
  }

  /**
   * 递归删除目录下的文件
   *
   * @param string $dir 文件目录
   * @param string|null $level
   * @return void
   */
  private function rmdir(string $dir, ?string $level = null): void
  {
    $dir = $level ? "$dir/$level" : $dir;
    if (is_dir($dir)) {
      // 列出指定路径内的文件和目录
      $resources = scandir($dir);
      foreach ($resources as $name) {
        if ($name != '.' && $name != '..') {
          // 如果是目录则继续递归，是文件则直接删除
          $subDir = $dir . '/' . $name;
          if (is_dir($subDir)) {
            $this->rmdir($subDir);
          } else {
            unlink($subDir);
          }
        }
      }
      // 如果目录为空，删除目录
      if (count(glob($dir . '/*')) === 0) rmdir($dir);
    }
  }

  /**
   * 保存日志(协程结束内部自动调用)
   *
   * @access public
   * @param array{
   *   int,
   *   array{
   *      timestamp: int,
   *      level: string,
   *      message: string,
   *      context: array,
   *      source: string,
   *   }
   * } $logRecords 需要写入日志的记录
   * @return void
   */
  #[Override] public function save(array $logRecords): void
  {
    $oldestLogFile = [];
    foreach ($logRecords as $logItem) {
      $level = $logItem['level'];
      // 格式化日期
      if ($this->dateFormat !== 'timestamp') {
        $logItem['timestamp'] = date($this->dateFormat, $logItem['timestamp']);
      }
      // 如果以json格式存储则直接转为json字符串
      $logString = $this->json
        ? json_encode($logItem, $this->json_flags)
        : LogManager::formatLogDataToString($this->logFormat, $logItem);
      $logDir = $this->getLogDir($level);
      // 获取当前日志文件名
      $logFiles = glob("$logDir/*.log");
      $logFileCount = count($logFiles);
      $currentLogFile = "$logDir/{$level}_$logFileCount.log";
      if (!file_exists($currentLogFile) || filesize($currentLogFile) >= $this->fileSize) {
        $logFileCount++;
        $currentLogFile = "$logDir/" . $level . '_' . $logFileCount . '.log';
      }
      file_put_contents($currentLogFile, $logString . PHP_EOL, FILE_APPEND);
      if ($logFileCount > $this->maxFiles) {
        // 按文件创建时间排序
        usort($logFiles, function ($a, $b) {
          return filemtime($a) <=> filemtime($b);
        });
        // 如果存在日志文件，则删除最早创建的一个
        if (!empty($logFiles)) {
          $oldestLogFile[] = array_shift($logFiles);
        }
      }
    }
    // 遍历删除旧的日志文件
    foreach ($oldestLogFile as $file) unlink($file);
    clearstatcache();
  }

  /**
   * 获取存储地址
   *
   * @param string $level
   * @return string
   */
  protected function getLogDir(string $level): string
  {
    $date = date('Ymd');
    $logDir = rtrim($this->log_dir, '/');
    $logDir .= "/$date/$level";
    // 创建目录（如果不存在）
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    return $logDir;
  }
}
