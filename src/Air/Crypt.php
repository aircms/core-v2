<?php

declare(strict_types=1);

namespace Air;

use Air\Core\Front;
use RuntimeException;

class Crypt
{
  public static function encode(mixed $data, ?string $secret = null, ?string $cipher = 'AES-256-CBC'): string
  {
    $secret = self::getSecret($secret);

    $plaintext = serialize($data);
    $key = hash('sha256', $secret, true);
    $iv = random_bytes(openssl_cipher_iv_length($cipher));

    $cipherText = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    if ($cipherText === false) {
      throw new RuntimeException('Encryption failed');
    }

    // IV + cipher
    $combined = $iv . $cipherText;

    // base64 + URL-safe
    $b64 = base64_encode($combined);
    $urlSafe = rtrim(strtr($b64, '+/', '-_'), '=');

    return $urlSafe;
  }

  public static function decode(mixed $data, ?string $secret = null, ?string $cipher = 'AES-256-CBC'): mixed
  {
    $secret = self::getSecret($secret);

    // восстанавливаем обычный base64 из URL-safe
    $b64 = strtr($data, '-_', '+/');
    $padding = strlen($b64) % 4;
    if ($padding) {
      $b64 .= str_repeat('=', 4 - $padding);
    }

    $raw = base64_decode($b64, true);
    if ($raw === false) {
      throw new RuntimeException('Invalid base64 input');
    }

    $ivLength = openssl_cipher_iv_length($cipher);
    if (strlen($raw) < $ivLength) {
      throw new RuntimeException('Invalid encrypted data');
    }

    $iv = substr($raw, 0, $ivLength);
    $cipherText = substr($raw, $ivLength);
    $key = hash('sha256', $secret, true);

    $plain = openssl_decrypt($cipherText, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    if ($plain === false) {
      throw new RuntimeException('Decryption failed');
    }

    return unserialize($plain);
  }

  public static function getSecret(?string $secret = null): string
  {
    if (!$secret) {
      $secret = Front::getInstance()->getConfig()['air']['crypt']['secret'] ?? '';
    }

    return $secret;
  }
}
