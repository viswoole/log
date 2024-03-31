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

use BadMethodCallException;
use InvalidArgumentException;
use ViSwoole\Log\Contract\LogCollectorInterface;
use ViSwoole\Log\Contract\LogDriveInterface;
use ViSwoole\Log\Drives\File;

class LogManager
{
  /**
   * @var LogDriveInterface[] 通道列表
   */
  private array $channels;
  /**
   * @var string 默认通道
   */
  private string $defaultChannel;
  /**
   * @var array{string,string} 日志类型指定通道
   */
  private array $type_channel;

  /**
   * @param string $configPath 配置文件路径
   */
  public function __construct(string $configPath = BASE_PATH . '/config/autoload/log.php')
  {
    $config = [];
    if (is_file($configPath)) $config = include($configPath);
    if (!is_array($config)) $config = [];
    $this->defaultChannel = $config['default'] ?? 'default';
    $this->type_channel = $config['type_channel'] ?? [];
    $this->channels = $config['channels'] ?? [
      'default' => new File()
    ];
    foreach ($this->type_channel as $channel) {
      !$this->hasChannel($channel, true);
    }
  }

  /**
   * 判断通道是否存在
   *
   * @access public
   * @param string|array $channel 通过通道名称，判断是否存在
   * @param bool $throw 不存在否抛出InvalidArgumentException异常
   * @return bool
   */
  public function hasChannel(string|array $channel, bool $throw = false): bool
  {
    $channels = is_string($channel) ? [$channel] : $channel;
    $result = true;
    foreach ($channels as $channel) {
      $exists = isset($this->channels[$channel]);
      if (!$exists) {
        if ($throw) {
          throw new InvalidArgumentException(
            "日志通道{$channel}不存在，可选的日志通道：" . implode(',', array_keys($this->channels))
          );
        } else {
          $result = false;
          break;
        }
      }
    }
    return $result;
  }

  /**
   * 获取日志通道
   *
   * @access public
   * @param string|null $name 通道名称不传可获取全部通道
   * @return array|LogDriveInterface[]|null
   */
  public function getChannels(?string $name = null): ?array
  {
    if ($name) return $this->channels[$name] ?? null;
    return $this->channels;
  }

  /**
   * 新增通道
   *
   * @access public
   * @param string $name
   * @param LogDriveInterface $drive
   * @return void
   */
  public function addChannel(string $name, LogDriveInterface $drive): void
  {
    $this->channels[$name] = $drive;
  }

  /**
   * 设置默认日志通道
   *
   * @access public
   * @param string $name
   * @return void
   */
  public function setDefaultChannel(string $name): void
  {
    $this->hasChannel($name, true);
    $this->defaultChannel = $name;
  }

  /**
   * 将调用的方法转发至日志驱动
   *
   * @param string $name
   * @param array $arguments
   * @return mixed
   */
  public function __call(string $name, array $arguments)
  {
    if (method_exists(LogDriveInterface::class, $name)) {
      $level = null;
      if (method_exists(LogCollectorInterface::class, $name)) {
        $level = $name;
      } elseif ($name === 'record' || $name === 'write') {
        $level = $arguments[2] ?? 'info';
      }
      if (isset($this->type_channel[$level])) {
        $channels = is_string($this->type_channel[$level])
          ? [$this->type_channel[$level]]
          : $this->type_channel[$level];
        // 兼容多通道记录日志
        foreach ($channels as $channel) {
          call_user_func_array([$this->channel($channel), $name], $arguments);
        }
      } else {
        // 使用默认通道记录日志
        return call_user_func_array([$this->channel($this->defaultChannel), $name], $arguments);
      }
    }
    throw new BadMethodCallException("$name method not exists.");
  }

  /**
   * 设置日志通道
   *
   * @param string $channel 设置记录日志的通道
   * @return LogDriveInterface
   */
  public function channel(string $channel): LogDriveInterface
  {
    $this->hasChannel($channel, true);
    return $this->channels[$channel];
  }
}
