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

namespace ViSwoole\Log\Contract;

use Stringable;

/**
 * 日志驱动
 * Class LoggerDriveInterface
 */
interface LogDriveInterface extends LogCollectorInterface
{
  /**
   * 清除日志
   *
   * @access public
   * @return void
   */
  public function clearRecord(): void;

  /**
   * 获取缓存日志
   *
   * @access public
   * @return array
   */
  public function getRecord(): array;

  /**
   * 保存日志(协程结束，日志记录器销毁时会自动调用该方法存储日志)
   *
   * @access public
   * @param array{
   *   int,
   *   array{
   *      timestamp:int,
   *      level:string,
   *      message:string,
   *      source:string,
   *      context:array,
   *   }
   * } $logRecords 需要写入日志的记录
   * @return void
   */
  public function save(array $logRecords): void;

  /**
   * 记录日志缓存
   * @access public
   * @param Stringable|string $message 日志消息
   * @param array $context 日志附加信息
   * @param string $level 日志等级
   * @return void
   */
  public function record(
    Stringable|string $message,
    array             $context = [],
    string            $level = 'info'
  ): void;

  /**
   * 实时写入日志
   *
   * @param Stringable|string $message 日志消息
   * @param array $context 日志附加信息
   * @param string $level 日志等级
   * @return void
   */
  public function write(
    Stringable|string $message,
    array             $context = [],
    string            $level = 'info'
  ): void;
}
