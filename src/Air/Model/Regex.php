<?php

declare(strict_types=1);

namespace Air\Model;

class Regex
{
  public static function starts(string $pattern, bool $caseInsensitive = true): \MongoDB\BSON\Regex
  {
    return new \MongoDB\BSON\Regex(
      "^" . preg_quote($pattern, '/'),
      self::getFlags($caseInsensitive)
    );
  }

  public static function contains(string $pattern, bool $caseInsensitive = true): \MongoDB\BSON\Regex
  {
    return new \MongoDB\BSON\Regex(
      preg_quote($pattern, '/'),
      self::getFlags($caseInsensitive)
    );
  }

  public static function ends(string $pattern, bool $caseInsensitive = true): \MongoDB\BSON\Regex
  {
    return new \MongoDB\BSON\Regex(
      preg_quote($pattern, '/') . "$",
      self::getFlags($caseInsensitive)
    );
  }

  private static function getFlags(bool $caseInsensitive = true): string
  {
    return $caseInsensitive ? "i" : "";
  }
}
