<?php

declare(strict_types=1);

namespace Air\Guard;

use Throwable;

final class Guard
{
  public static function ensure(
    bool|callable|array            $condition,
    callable|Throwable|string|null $fail = null
  ): void
  {
    $failedAt = self::evaluate($condition);

    if ($failedAt === null) {
      return;
    }

    if ($fail !== null) {

      if (is_callable($fail)) {
        $fail($failedAt);
        return;
      }

      if (is_string($fail)) {
        throw new $fail;
      }

      if ($fail instanceof Throwable) {
        throw $fail;
      }
    }

    throw new GuardException($failedAt);
  }

  private static function evaluate(bool|callable|array $condition): ?int
  {
    $index = 1;

    $walker = function ($condition) use (&$walker, &$index): ?int {

      if (is_array($condition)) {
        foreach ($condition as $item) {
          $failed = $walker($item);
          if ($failed !== null) {
            return $failed;
          }
        }
        return null;
      }

      $result = is_callable($condition)
        ? (bool)$condition()
        : (bool)$condition;

      if (!$result) {
        return $index;
      }

      $index++;
      return null;
    };

    return $walker($condition);
  }
}