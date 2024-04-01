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
use ViSwoole\Log\Facade\Log;
use ViSwoole\Log\LogManager;

class LogManagerTest extends TestCase
{
  public function testLogManager(): void
  {
    $logManager = new LogManager();
    $data = [];
    $logManager->info('test', $data);
    $logManager->debug('test', $data);
    $logManager->warning('test', $data);
    $logManager->error('test', $data);
    $logManager->critical('test', $data);
    $logManager->alert('test', $data);
    $logManager->emergency('test', $data);
    self::assertTrue(true);
  }

  public function testLogFacade()
  {
    Log::notice('test');
    Log::log('info', 'test');
    self::assertTrue(true);
  }
}
