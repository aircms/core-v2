<?php

declare(strict_types=1);

namespace Air;

class Password
{
  const string ALGORITHM = PASSWORD_DEFAULT;

  public static function hash(string $password): string
  {
    return password_hash($password, self::ALGORITHM);
  }

  public static function verify(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }
}
