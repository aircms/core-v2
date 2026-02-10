<?php

declare(strict_types=1);

namespace Air;

use Air\Core\Front;
use Air\Core\Request;
use Throwable;

class Log
{
  public static function info(string $title, array $data = []): int
  {
    return self::write($title, $data);
  }

  public static function error(string $title, array $data = []): int
  {
    return self::write($title, $data, Crud\Model\Log::ERROR);
  }

  public static function write(string $title, array $data = [], string $level = Crud\Model\Log::INFO): int
  {
    $logsEnabled = Front::getInstance()->getConfig()['air']['logs']['enabled'] ?? false;

    if ($logsEnabled) {
      $log = new Crud\Model\Log();

      $log->title = $title;
      $log->data = $data;
      $log->level = $level;

      return $log->save();
    }

    return 0;
  }

  public static function requestException(Throwable $exception, Request $request): void
  {
    $params = [
      'params' => [
        'get' => $request->getGetAll(),
        'post' => $request->getPostAll(),
      ],
    ];

    $log = [
      'ip' => $request->getIp(),
      'user-agent' => $request->getUserAgent(),
      'handler' => get_class($exception),
      'trace' => $exception->getTrace(),
      'code' => $exception->getCode(),
    ];

    try {
      Log::error($exception->getMessage(), [...$log, ...$params]);
    } catch (Throwable) {
      try {
        Log::error($exception->getMessage(), $log);
      } catch (Throwable) {
      }
    }
  }
}
