<?php

declare(strict_types=1);

namespace Air\Model;

use Air\Core\Front;

class Config
{
  private static array $config = [];

  public static function getConfig(): array
  {
    if (!self::$config) {
      self::$config = Front::getInstance()->getConfig()['air']['db'];
    }
    return self::$config;
  }

  public static function setConfig(array $config): void
  {
    self::$config = $config;
  }
}