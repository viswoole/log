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

namespace ViSwoole\Log\Facade;

use Override;
use Stringable;
use ViSwoole\Core\Facade;
use ViSwoole\Log\Contract\LogDriveInterface;
use ViSwoole\Log\LogLevel;
use ViSwoole\Log\LogManager;

/**
 * 日志门面类
 *
 * @method static void emergency(string|Stringable $message, array $context = []) 紧急情况。
 * @method static void log(string $level, string|Stringable $message, array $context = []) 记录具有任意级别的日志。
 * @method static void alert(string|Stringable $message, array $context = []) 必须立即采取行动。
 * @method static void critical(string|Stringable $message, array $context = []) 严重情况。
 * @method static void error(string|Stringable $message, array $context = []) 不需要立即采取行动的运行时错误，但通常应记录和监视。
 * @method static void warning(string|Stringable $message, array $context = []) 不是错误的异常情况。
 * @method static void notice(string|Stringable $message, array $context = []) 正常但重要的事件。
 * @method static void info(string|Stringable $message, array $context = []) 有趣的事件。
 * @method static void debug(string|Stringable $message, array $context = []) 详细的调试信息。
 * @method static void sql(string|Stringable $message, array $context = []) SQL日志。
 * @method static void task(string|Stringable $message, array $context = []) 任务日志。
 * @method static void write(Stringable|string $message, array $context = [], string $level = LogLevel::INFO) 直接写入日志
 * @method static bool save(array $logRecords) 保存日志（无需手动调用, 协程结束会自动调用）
 * @method static bool clearRecord() 清除缓存日志
 * @method static array getRecord() 获取缓存日志
 * @method static LogDriveInterface channel(string $name) 设置日志通道
 * @method static bool hasChannel(string $name) 判断通道是否存在
 * @method static void setDefaultChannel(string $name) 设置默认日志通道
 * @method static void addChannel(string $name, LogDriveInterface $drive) 添加日志通道
 * @method static LogDriveInterface[]|null getChannels(?string $name = null) 获取日志通道
 * @method static void setTraceSource(bool $record = false) 是否跟踪日志来源
 * @method static bool hasTraceSource() 判断是否跟中日志来源
 * @method static void setToConsole(bool $record = false) 设置是否输出到控制台
 * @method static bool hasToConsole() 是否输出到控制台
 * @method static void echoConsole(string $level, string $content) 输出日志到控制台
 * @method static string formatLogDataToString(string $formatRule, array $logData) 格式化日志数据为字符串
 */
class Log extends Facade
{
  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return LogManager::class;
  }
}
