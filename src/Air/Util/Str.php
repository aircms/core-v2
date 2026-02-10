<?php

declare(strict_types=1);

namespace Air\Util;

class Str
{
  public static function humanize(string $value): string
  {
    if ($value === '') {
      return '';
    }

    // user_flow -> user flow
    $value = str_replace(['_', '-'], ' ', $value);

    // userFlow -> user Flow
    $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);

    // USER_FLOW -> user flow
    $value = strtolower($value);

    // User flow
    return ucfirst(trim($value));
  }
}
