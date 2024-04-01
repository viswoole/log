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

namespace ViSwoole\Log;

use Override;
use RuntimeException;
use Stringable;
use Swoole\Coroutine;
use ViSwoole\Log\Contract\LogDriveInterface;

/**
 * 日志驱动器基类
 */
abstract class LogDrive extends LogCollector implements LogDriveInterface
{

  /**
   * @var string 容器记录名
   */
  protected string $contextName;

  /**
   * 清除日志
   *
   * @access public
   * @return void
   */
  #[Override] public function clearRecord(): void
  {
    $this->getRecorder()->clear();
  }

  /**
   * 获取日志记录器
   *
   * @return LogRecorder|null
   */
  private function getRecorder(): ?LogRecorder
  {
    $context = Coroutine::getContext();
    if (!$context) throw new RuntimeException('协程已销毁，无法获取上下文中的日志记录器');
    if (!isset($context[$this->getContextName()])) {
      $context[$this->getContextName()] = new LogRecorder($this);
    }
    return $context[$this->getContextName()];
  }

  /**
   * 获取协程上下文记录键
   *
   * @return string
   */
  private function getContextName(): string
  {
    if (isset($this->contextName)) {
      return $this->contextName;
    } else {
      $className = str_replace('\\', '_', get_class($this));
      $className = uniqid('$log_recorder_' . strtolower($className));
      $this->contextName = $className;
    }
    return $this->contextName;
  }

  /**
   * 获取缓存日志
   *
   * @access public
   * @return array
   */
  #[Override] public function getRecord(): array
  {
    return $this->getRecorder()->getArrayCopy();
  }

  /**
   * 具有任意级别的日志。
   *
   * @param string $level 日志等级
   * @param string|Stringable $message 日志描述
   * @param array $context 上下文
   *
   * @return void
   */
  #[Override] public function log(mixed $level, Stringable|string $message, array $context = []
  ): void
  {
    $this->record($message, $context, $level);
  }

  /**
   * 记录日志缓存
   *
   * @access public
   * @param Stringable|string $message 日志消息
   * @param array $context 日志附加信息
   * @param string $level 日志等级
   * @return void
   */
  #[Override] public function record(
    Stringable|string $message, array $context = [], string $level = 'info'
  ): void
  {
    if (Coroutine::getuid() !== -1) {
      $this->getRecorder()->push($level, $message, $context);
    } else {
      $this->write($message, $context, $level);
    }
  }

  /**
   * 实时写入日志
   *
   * @param Stringable|string $message 日志消息
   * @param array $context 日志附加信息
   * @param string $level 日志等级
   * @return void
   */
  #[Override] public function write(
    Stringable|string $message, array $context = [], string $level = 'info'
  ): void
  {
    $data = LogManager::createLogData($level, $message, $context);
    $this->save([$data]);
  }

  /**
   * 格式化日志数据为字符串
   *
   * @access public
   * @param array{
   *    timestamp: int,
   *    level: string,
   *    message: string,
   *    context: array,
   *    source: string,
   * } $logData 需要写入日志的记录
   * @param string $formatRule 格式化规则，示例:[%timestamp][%level] %message : %context -in %source
   * @return string
   */
  protected function formatLogDataToString(string $formatRule, array $logData): string
  {
    // 通过正则表达式匹配格式化规则中的占位符
    preg_match_all('/%(\w+)/', $formatRule, $matches);
    // 获取匹配到的占位符
    $placeholders = $matches[1];
    // 重新排序 $logData 数组的键
    $sortedData = [];
    foreach ($placeholders as $placeholder) {
      if (array_key_exists($placeholder, $logData)) {
        $sortedData[$placeholder] = $logData[$placeholder];
        unset($logData[$placeholder]);
      }
    }
    // 根据格式化规则生成新的字符串
    $newStr = $formatRule;
    foreach ($sortedData as $key => $value) {
      $value = is_string($value)
        ? $value
        : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $newStr = str_replace("%$key", (string)$value, $newStr);
    }
    return $newStr;
  }
}
