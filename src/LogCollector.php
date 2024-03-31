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

use Stringable;
use ViSwoole\Log\Contract\LogCollectorInterface;

/**
 * 日志收集
 * Class LogCollector
 */
abstract class LogCollector implements LogCollectorInterface
{
  /**
   * 紧急情况。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function emergency(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 必须立即采取行动。
   *
   * 示例：整个网站崩溃，数据库不可用等。这应该触发 SMS 警报并唤醒您。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function alert(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 严重情况。
   *
   * 示例：应用程序组件不可用，意外异常。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function critical(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 不需要立即采取行动的运行时错误，但通常应记录和监视。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function error(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 不是错误的异常情况。
   *
   * 示例：使用不推荐的 API，API 的不良使用，不一定是错误的不希望出现的事情。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function warning(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 正常但重要的事件。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function notice(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 有趣的事件。
   *
   * 示例：用户登录，SQL 日志。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function info(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 详细的调试信息。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function debug(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * sql日志。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function sql(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }

  /**
   * 任务日志。
   *
   * @param string|Stringable $message
   * @param array $context
   *
   * @return void
   */
  public function task(Stringable|string $message, array $context = []): void
  {
    $this->log(__FUNCTION__, $message, $context);
  }
}
