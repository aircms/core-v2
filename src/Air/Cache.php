<?php

declare(strict_types=1);

namespace Air;

use Air\Core\Front;
use Air\Crud\Model\Language;
use Air\Model\ModelAbstract;
use Closure;
use Throwable;

/**
 * @collection AirCache
 *
 * @property string $id
 *
 * @property string $key
 * @property string $value
 * @property integer $lifetime
 */
class Cache extends ModelAbstract
{
  private static array $single = [];
  const int LIFETIME_LONG = 86400;
  const int LIFETIME_TEMP = 7200;
  const int LIFETIME_FAST = 1800;
  const int LIFETIME_QUICK = 600;

  public static function single(mixed $key, Closure $fn): mixed
  {
    $key = md5(serialize($key));
    if (isset(self::$single[$key])) {
      return self::$single[$key];
    }
    self::$single[$key] = $fn();
    return self::$single[$key];
  }

  public static function quick(mixed $key, Closure $fn): mixed
  {
    $lifetime = Front::getInstance()->getConfig()['air']['cache']['quick'] ?? self::LIFETIME_QUICK;
    return self::content($key, $lifetime, $fn);
  }

  public static function fast(mixed $key, Closure $fn): mixed
  {
    $lifetime = Front::getInstance()->getConfig()['air']['cache']['fast'] ?? self::LIFETIME_FAST;
    return self::content($key, $lifetime, $fn);
  }

  public static function temp(mixed $key, Closure $fn): mixed
  {
    $lifetime = Front::getInstance()->getConfig()['air']['cache']['temp'] ?? self::LIFETIME_TEMP;
    return self::content($key, $lifetime, $fn);
  }

  public static function long(mixed $key, Closure $fn): mixed
  {
    $lifetime = Front::getInstance()->getConfig()['air']['cache']['long'] ?? self::LIFETIME_LONG;
    return self::content($key, $lifetime, $fn);
  }

  public static function content(mixed $key, int $lifetime, Closure $fn): mixed
  {
    if (!isset($key['language'])) {
      try {
        $key = (array)$key;
        $key['language'] = Language::getLanguage()->key;
      } catch (Throwable) {
      }
    }
    return self::propagate($key, $lifetime, $fn);
  }

  public static function propagate(mixed $key, int $lifetime, Closure $fn): mixed
  {
    if (!(Front::getInstance()->getConfig()['air']['cache']['enabled'] ?? false)) {
      return self::getContent($fn);
    }

    $key = md5(serialize($key));
    $data = self::one(['key' => $key]);

    if ($data) {
      if ($data->lifetime >= time()) {
        return json_decode($data->value, true);
      }
      $data->remove();
    }

    $content = self::getContent($fn);

    if (!$content) {
      return $content;
    }

    $cache = new self();
    $cache->key = $key;
    $cache->value = json_encode($content);
    $cache->lifetime = time() + $lifetime;

    $cache->save();
    return $content;
  }

  private static function getContent(Closure $fn): mixed
  {
    ob_start();

    $returned = $fn();
    $content = ob_get_contents();

    ob_end_clean();

    return $returned ?? $content;
  }
}