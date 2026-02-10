<?php

declare(strict_types=1);

namespace Air\Util;

use Air\Model\Driver\CursorAbstract;
use Generator;

class Arr
{
  /**
   * @template T
   * @param CursorAbstract|array<T> $array
   * @param callable(T):bool $callback
   * @return T|null
   */
  public static function find(array|CursorAbstract $array, callable $callback): mixed
  {
    foreach ($array as $value) {
      if ($callback($value)) {
        return $value;
      }
    }
    return null;
  }

  public static function findIndex(array $array, callable $callback): ?int
  {
    foreach ($array as $key => $value) {
      if ($callback($value)) {
        return $key;
      }
    }
    return null;
  }

  /**
   * @template T
   * @param Generator|CursorAbstract|array<T> $array
   * @param callable(T, int|string):mixed $callback
   * @return array<T>
   */
  public static function map(array|CursorAbstract|Generator $array, callable $callback): array
  {
    $result = [];
    foreach ($array as $key => $item) {
      $result[] = $callback($item, $key);
    }
    return $result;
  }

  /**
   * @template T
   * @param CursorAbstract|array<T> $array
   * @param callable(T):mixed $callback
   * @return array<T>
   */
  public static function filter(CursorAbstract|array $array, callable $callback): array
  {
    $result = [];
    foreach ($array as $key => $item) {
      if ($callback($item)) {
        $result[$key] = $item;
      }
    }
    return $result;
  }

  /**
   * @template T
   * @param CursorAbstract|array<T> $array
   * @param int|string|callable(T):mixed $callback
   * @return array<array<T>>|null
   */
  public static function group(CursorAbstract|array $array, int|string|callable $callback): mixed
  {
    $result = [];
    $cleanKeys = false;

    foreach ($array as $item) {
      if (is_callable($callback)) {
        $res = $callback($item);
        if (is_string($res) || is_int($res)) {
          $key = $res;
        } else {
          $key = serialize($res);
          $cleanKeys = true;
        }
      } else {
        $key = $callback;
      }
      $result[$key] = $result[$key] ?? [];
      $result[$key][] = $item;
    }
    if ($cleanKeys) {
      $result = array_values($result);
    }
    return $result;
  }

  /**
   * @template T
   * @param CursorAbstract|array<T> $array
   * @return T|null
   */
  public static function first(CursorAbstract|array $array): mixed
  {
    $count = count($array);
    if ($count > 0) {
      return $array[0];
    }
    return null;
  }

  /**
   * @template T
   * @param CursorAbstract|array<T> $array
   * @return T|null
   */
  public static function last(CursorAbstract|array $array): mixed
  {
    $count = count($array);
    if ($count > 0) {
      return $array ? $array[$count - 1] : null;
    }
    return null;
  }

  public static function pipe(mixed $value, callable ...$pipes): mixed
  {
    foreach ($pipes as $pipe) {
      $value = $pipe($value);
    }
    return $value;
  }

  /**
   * @template T
   * @template R
   *
   * @param CursorAbstract|array<T> $array
   * @param callable(T, R): R $callback
   * @param R $initial
   * @return R
   */
  public static function reduce(CursorAbstract|array $array, callable $callback, mixed $initial = null): mixed
  {
    $carry = $initial;
    foreach ($array as $item) {
      $carry = $callback($item, $carry);
    }
    return $carry;
  }

  /**
   * @template T
   * @param CursorAbstract|array<T> $array
   * @param int|string|callable(T):bool $callback
   * @return array<T>
   */
  public static function unique(CursorAbstract|array $array, int|string|callable $callback): array
  {
    $seen = [];
    $result = [];

    foreach ($array as $item) {
      if (is_callable($callback)) {
        $key = serialize($callback($item));
      } else {
        $key = $callback;
      }
      if (!array_key_exists($key, $seen)) {
        $seen[$key] = true;
        $result[] = $item;
      }
    }

    return $result;
  }
}