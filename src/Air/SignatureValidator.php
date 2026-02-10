<?php

declare(strict_types=1);

namespace Air;

use Air\Core\Front;
use Air\Exception\NotAllowed;

class SignatureValidator
{
  public static function validate(int $timestamp, string $signature): void
  {
    $config = Front::getInstance()->getConfig()['air']['signatureValidator'] ?? false;

    if (!$config || !($config['enabled'] ?? false)) {
      return;
    }

    $lifetime = $config['lifetime'];
    $secret = $config['secret'];

    if (
      !$timestamp || !$signature
      || abs(time() - $timestamp) > $lifetime
      || !hash_equals(hash_hmac('sha256', (string)$timestamp, $secret), $signature)
    ) {
      throw new NotAllowed();
    }
  }
}
