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

use ArrayObject;
use Stringable;
use ViSwoole\Log\Contract\LogDriveInterface;

/**
 * 日志缓存记录器
 */
class LogRecorder extends ArrayObject
{
  public function __construct(protected LogDriveInterface $drive)
  {
    parent::__construct();
  }

  /**
   * 添加一个日志
   *
   * @param string $level 日志等级
   * @param string|Stringable $message 日志消息
   * @param array $context 日志附加信息
   * @return void
   */
  public function push(string $level, string|Stringable $message, array $context = []): void
  {
    $data = self::createLogData(...func_get_args());
    $this->offsetSet(null, $data);
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
   * 协程上下文销毁时会自动触发
   */
  public function __destruct()
  {
    $logRecords = $this->getArrayCopy();
    if (!empty($logRecords)) {
      $this->drive->save($logRecords);
    }
    $this->clear();
  }

  /**
   * 清除缓存的日志
   *
   * @return void
   */
  public function clear(): void
  {
    $this->exchangeArray([]);
  }

  /**
   * 获取缓存的日志数据
   *
   * @return array
   */
  public function get(): array
  {
    return $this->getArrayCopy();
  }
}
