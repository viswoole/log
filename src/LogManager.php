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

use BadMethodCallException;
use InvalidArgumentException;
use Stringable;
use ViSwoole\Core\Facade;
use ViSwoole\Log\Contract\LogCollectorInterface;
use ViSwoole\Log\Contract\LogDriveInterface;
use ViSwoole\Log\Drives\File;

/**
 * 日志管理器
 *
 * @method void emergency(string|Stringable $message, array $context = []) 紧急情况。
 * @method void log(string $level, string|Stringable $message, array $context = []) 记录具有任意级别的日志。
 * @method void alert(string|Stringable $message, array $context = []) 必须立即采取行动。
 * @method void critical(string|Stringable $message, array $context = []) 严重情况。
 * @method void error(string|Stringable $message, array $context = []) 不需要立即采取行动的运行时错误，但通常应记录和监视。
 * @method void warning(string|Stringable $message, array $context = []) 不是错误的异常情况。
 * @method void notice(string|Stringable $message, array $context = []) 正常但重要的事件。
 * @method void info(string|Stringable $message, array $context = []) 有趣的事件。
 * @method void debug(string|Stringable $message, array $context = []) 详细的调试信息。
 * @method void sql(string|Stringable $message, array $context = []) SQL日志。
 * @method void task(string|Stringable $message, array $context = []) 任务日志。
 * @method void write(Stringable|string $message, array $context = [], string $level = LogLevel::INFO) 直接写入日志
 * @method bool save(array $logRecords) 保存日志（无需手动调用, 协程结束会自动调用）
 * @method bool clearRecord() 清除缓存日志
 * @method array getRecord() 获取缓存日志ß
 * ∂
 */
class LogManager
{
  /**
   * @var LogDriveInterface[] 通道列表
   */
  private array $channels;
  /**
   * @var string 默认通道
   */
  private string $defaultChannel;
  /**
   * @var array{string,string} 日志类型指定通道
   */
  private array $type_channel;
  /**
   * @var bool 是否记录日志来源
   */
  private bool $recordLogTraceSource;
  /**
   * @var bool 是否输出至控制台
   */
  private bool $toTheConsole;

  /**
   * @param string|null $configPath 配置文件路径
   */
  public function __construct(string $configPath = null)
  {
    if (is_null($configPath)) $configPath = getRootPath() . '/config/autoload/log.php';
    $config = [];
    if (is_file($configPath)) $config = include($configPath);
    if (!is_array($config)) $config = [];
    $this->defaultChannel = $config['default'] ?? 'default';
    $this->type_channel = $config['type_channel'] ?? [];
    $this->recordLogTraceSource = $config['trace_source'] ?? false;
    $this->toTheConsole = $config['console'] ?? false;
    $this->channels = $config['channels'] ?? [
      'default' => new File()
    ];
    foreach ($this->type_channel as $channel) {
      !$this->hasChannel($channel, true);
    }
  }

  /**
   * 判断通道是否存在
   *
   * @access public
   * @param string|array $channel 通过通道名称，判断是否存在
   * @param bool $throw 不存在否抛出InvalidArgumentException异常
   * @return bool
   */
  public function hasChannel(string|array $channel, bool $throw = false): bool
  {
    $channels = is_string($channel) ? [$channel] : $channel;
    $result = true;
    foreach ($channels as $channel) {
      $exists = isset($this->channels[$channel]);
      if (!$exists) {
        if ($throw) {
          throw new InvalidArgumentException(
            "日志通道{$channel}不存在，可选的日志通道：" . implode(',', array_keys($this->channels))
          );
        } else {
          $result = false;
          break;
        }
      }
    }
    return $result;
  }

  /**
   * 创建日志数据
   *
   * @param string $level 日志等级
   * @param string|Stringable $message 日志描述
   * @param array $context 日志附加上下文信息
   * @return array{timestamp: int, level: string, message: string, context: array,sourece: string}
   */
  public static function createLogData(
    string            $level,
    string|Stringable $message,
    array             $context = []
  ): array
  {
    $source = $context['__log_trace_source'] ?? '';
    unset($context['__log_trace_source']);
    return [
      'timestamp' => time(),
      'level' => $level,
      'message' => (string)$message,
      'context' => $context,
      'source' => $source
    ];
  }

  /**
   * 获取日志通道
   *
   * @access public
   * @param string|null $name 通道名称不传可获取全部通道
   * @return array|LogDriveInterface[]|null
   */
  public function getChannels(?string $name = null): ?array
  {
    if ($name) return $this->channels[$name] ?? null;
    return $this->channels;
  }

  /**
   * 新增通道
   *
   * @access public
   * @param string $name
   * @param LogDriveInterface $drive
   * @return void
   */
  public function addChannel(string $name, LogDriveInterface $drive): void
  {
    $this->channels[$name] = $drive;
  }

  /**
   * 设置默认日志通道
   *
   * @access public
   * @param string $name
   * @return void
   */
  public function setDefaultChannel(string $name): void
  {
    $this->hasChannel($name, true);
    $this->defaultChannel = $name;
  }

  /**
   * 将调用的方法转发至日志驱动
   *
   * @param string $name
   * @param array $arguments
   * @return mixed
   */
  public function __call(string $name, array $arguments)
  {
    if (method_exists(LogDriveInterface::class, $name)) {
      $level = null;
      if ($name === 'record' || $name === 'write') {
        $level = $arguments[2] ?? 'info';
        $arguments[1] = $this->buildTraceSource($arguments[1] ?? []);
      } elseif ($name === 'log') {
        $level = $arguments[0] ?? null;
        $arguments[2] = $this->buildTraceSource($arguments[2] ?? []);
      } elseif (method_exists(LogCollectorInterface::class, $name)) {
        $level = $name;
        $arguments[1] = $this->buildTraceSource($arguments[1] ?? []);
      }
      if (isset($this->type_channel[$level])) {
        $channels = is_string($this->type_channel[$level])
          ? [$this->type_channel[$level]]
          : $this->type_channel[$level];
        // 兼容多通道记录日志
        foreach ($channels as $channel) {
          call_user_func_array([$this->channel($channel), $name], $arguments);
        }
      } else {
        // 使用默认通道记录日志
        return call_user_func_array([$this->channel($this->defaultChannel), $name], $arguments);
      }
    }
    throw new BadMethodCallException("$name method not exists.");
  }

  /**
   * 在上下文中加入日志来源
   *
   * @param array $context 上下文
   * @return array
   */
  private function buildTraceSource(array $context = []): array
  {
    if ($this->recordLogTraceSource) {
      $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
      foreach (array_reverse($backtrace) as $trace) {
        if ($trace['class'] === Facade::class || $trace['class'] === self::class) {
          $backtrace = $trace;
          break;
        }
      }
      $trace = ($backtrace['file'] ?? '') . ':' . ($backtrace['line']) ?? '';
    } else {
      $trace = '';
    }
    $context['__log_trace_source'] = $trace;
    return $context;
  }

  /**
   * 设置日志通道
   *
   * @param string $channel 设置记录日志的通道
   * @return LogDriveInterface
   */
  public function channel(string $channel): LogDriveInterface
  {
    $this->hasChannel($channel, true);
    return $this->channels[$channel];
  }

  /**
   * 设置是否跟踪日志来源
   *
   * @access public
   * @param bool $record
   * @return void
   */
  public function setTraceSource(bool $record): void
  {
    $this->recordLogTraceSource = $record;
  }

  /**
   * 判断是否跟踪日志来源
   *
   * @access public
   * @return bool 返回true标识需要跟踪日志来源
   */
  public function isTraceSource(): bool
  {
    return $this->recordLogTraceSource;
  }

  /**
   * 判断是否输出到控制台
   *
   * @access public
   * @return bool
   */
  public function isToConsole(): bool
  {
    return $this->toTheConsole;
  }

  /**
   * 设置是否输出到控制台
   *
   * @access public
   * @param bool $return
   * @return void
   */
  public function setToConsole(bool $return = true): void
  {
    $this->toTheConsole = $return;
  }
}
