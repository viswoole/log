# ViSwoole-Log

ViSwoole日志模块

## 安装

```bash
composer require viswoole/log
```

## 使用

```php
use Visoole\Log\Facade\Log;

Log::info('info');
Log::debug('debug');
Log::error('error');
Log::warning('warning');
Log::notice('notice');
Log::critical('critical');
Log::alert('alert');
Log::emergency('emergency');
Log::log('level','自定义等级事件',['key'=>'value']); // 输出具有任意
```
