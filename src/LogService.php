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

use ViSwoole\Core\ServiceProvider;
use ViSwoole\Log\Contract\LogDriveInterface;
use ViSwoole\Log\LogManager;

class LogService extends ServiceProvider
{
  /**
   * 该方法会在服务注册时调用，在该方法内通过$this->app->bind('服务名', '服务类名');
   *
   * @return void
   */
  public function register(): void
  {
    $this->app->bind('log', LogManager::class);
    $this->app->bind(Log::class, LogManager::class);
    $this->app->bind(LogDriveInterface::class, LogManager::class);
  }

  /**
   * 该方法是在所有系统服务都绑定完毕过后调用，可以在此方法内注册路由，监听事件等
   *
   * @return void
   */
  public function boot(): void
  {
    $this->app->make('log');
  }
}
