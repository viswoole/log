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
/**
 * 内置日志等级
 * Class LogLevel
 */
class LogLevel extends \Psr\Log\LogLevel
{
  /**
   * 紧急情况
   */
  public const string EMERGENCY = 'emergency';
  /**
   * 必须立即采取行动
   */
  public const string ALERT = 'alert';
  /**
   * 严重情况
   */
  public const string CRITICAL = 'critical';
  /**
   * 运行时错误
   */
  public const string ERROR = 'error';
  /**
   * 运行时警告
   */
  public const string WARNING = 'warning';
  /**
   * 正常但重要的事件
   */
  public const string NOTICE = 'notice';
  /**
   * 有趣的事件
   */
  public const string INFO = 'info';
  /**
   * 详细的调试信息
   */
  public const string DEBUG = 'debug';
  /**
   * sql日志
   */
  public const string SQL = 'sql';
  /**
   * 任务执行日志
   */
  public const string TASK = 'task';
}
