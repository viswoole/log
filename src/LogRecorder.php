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
   * 添加data
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
   * @param string $level
   * @param string|Stringable $message
   * @param array $context
   * @return array
   */
  public static function createLogData(
    string $level, string|Stringable $message, array $context = []
  ): array
  {
    $data = [
      'timestamp' => time(),
      'level' => $level,
      'message' => (string)$message
    ];
    if (isset($context['_source'])) {
      $data['source'] = $context['_source'];
      unset($context['_source']);
    }
    $data['context'] = $context;
    return $data;
  }

  /**
   * 清除data
   *
   * @return void
   */
  public function clear(): void
  {
    $this->exchangeArray([]);
  }

  public function __destruct()
  {
    $logRecords = $this->getArrayCopy();
    if (!empty($logRecords)) {
      $this->drive->save($logRecords);
    }
  }

  /**
   * 获取data
   *
   * @return array
   */
  public function get(): array
  {
    return $this->getArrayCopy();
  }
}
