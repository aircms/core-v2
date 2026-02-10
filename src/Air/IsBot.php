<?php

declare(strict_types=1);

namespace Air;

use Air\Core\Front;

class IsBot
{
  public static function byUserAgent(): bool
  {
    $userAgent = Front::getInstance()->getRequest()->getUserAgent();

    $bots = [
      'bot',
      'crawl',
      'slurp',
      'spider',
      'mediapartners',
      'facebook',
      'lighthouse',
      'telegrambot',
      'curl',
      'wget',
      'python-requests',
      'httpclient',
      'ahrefs',
      'semrush',
      'yandex',
      'bing',
      'duckduck',
      'baidu',
      'applebot'
    ];

    $ua = strtolower($userAgent);
    foreach ($bots as $bot) {
      if (str_contains($ua, $bot)) {
        return true;
      }
    }
    return false;
  }
}