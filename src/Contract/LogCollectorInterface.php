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

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * 描述记录器实例。
 *
 * 消息必须是实现 __toString（） 的字符串或对象。
 *
 * 消息可能包含以下形式的占位符：{foo} 其中 foo
 * 将被键“foo”中的上下文数据替换。
 *
 * 上下文数组可以包含任意数据。唯一的假设是
 * 可以由实现者制作，如果给定一个 Exception 实例
 * 要生成堆栈跟踪，它必须位于名为“exception”的键中。
 *
 * 见 https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * 为完整接口规范。
 */
interface LogCollectorInterface extends LoggerInterface
{
  /**
   * sql日志。
   *
   * @param string|Stringable $message 描述消息
   * @param array $context 上下文
   *
   * @return void
   */
  public function sql(Stringable|string $message, array $context = []): void;

  /**
   * 任务日志。
   *
   * @param string|Stringable $message 描述消息
   * @param array $context 上下文
   *
   * @return void
   */
  public function task(Stringable|string $message, array $context = []): void;
}
