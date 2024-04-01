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

namespace ViSwoole\Log\Tests;

use PHPUnit\Framework\TestCase;
use ViSwoole\Log\LogManager;

class LogManagerTest extends TestCase
{
  public function testLogManager(): void
  {
    $logManager = new LogManager();
    $logManager->info('test');
    $logManager->debug('test');
    $logManager->warning('test');
    $logManager->error('test');
    $logManager->critical('test');
    $logManager->alert('test');
    $logManager->emergency('test');
  }
}
