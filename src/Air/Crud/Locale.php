<?php

declare(strict_types=1);

namespace Air\Crud;

use Air\Core\Front;

class Locale
{
  public static ?array $keys = null;

  public static function t(string $key): string
  {
    if (!self::$keys) {
      self::$keys = self::phrases();
    }
    if (isset(self::$keys[$key])) {
      return self::$keys[$key];
    }

    self::$keys[$key] = $key;
    if ($filename = Front::getInstance()->getConfig()['air']['admin']['locale'] ?? false) {
      file_put_contents($filename, json_encode(self::$keys, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    return $key;
  }

  public static function phrases(): array
  {
    if ($filename = (Front::getInstance()->getConfig()['air']['admin']['locale'] ?? false)) {
      return json_decode(file_get_contents($filename), true);
    }
    return [];
  }
}