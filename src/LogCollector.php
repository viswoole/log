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
 *
 * @method emergency(Stringable|string $message, array $context = []) 系统不可用
 * @method alert(Stringable|string $message, array $context = []) 必须立即采取行动，这应该会触发短信警报并唤醒您。
 * @method critical(Stringable|string $message, array $context = []) 应用程序组件不可用，意外异常
 * @method error(Stringable|string $message, array $context = []) 不需要立即采取行动，但通常应记录和监视的运行时错误。
 * @method warning(Stringable|string $message, array $context = []) 非错误的异常情况
 * @method notice(Stringable|string $message, array $context = []) 正常但重要的事件。
 * @method info(Stringable|string $message, array $context = []) 普通的日志记录，例如用户登录，注册等。
 * @method debug(Stringable|string $message, array $context = []) 详细的调试信息。
 * @method sql(Stringable|string $message, array $context = []) SQL运行留下的日志。
 * @method task(Stringable|string $message, array $context = []) 任务运行日志。
 */
abstract class LogCollector implements LogCollectorInterface
{
  public function __call(string $name, array $arguments)
  {
    $this->log($name, ...$arguments);
  }
}
