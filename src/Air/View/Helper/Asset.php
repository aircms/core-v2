<?php

declare(strict_types=1);

namespace Air\View\Helper;

use Air\Core\Front;

class Asset extends HelperAbstract
{
  public static ?array $config = null;

  public function call(string|array $assets, ?string $type = null): string
  {
    if (!self::$config) {
      self::$config = array_merge([
        'underscore' => false,
        'prefix' => '',
        'defer' => false,
        'async' => false
      ], Front::getInstance()->getConfig()['air']['asset'] ?? []);
    }

    $assetsHtml = [];
    $assetsType = $type;

    foreach ((array)$assets as $asset) {
      $assetParts = explode('.', explode('?', $asset)[0]);
      $type = $assetsType ?: $assetParts[count($assetParts) - 1];

      $assetsHtml[] = match ($type) {
        'js' => $this->js($asset),
        'ico', 'svg', 'jpg', 'jpeg', 'png', 'webp', 'json', 'woff', 'woff2' => $this->filter($asset),
        default => $this->css($asset),
      };
    }

    return implode("\n", $assetsHtml);
  }

  public function js(string $uri): string
  {
    return script(
      src: $this->filter($uri),
      defer: self::$config['defer'] ?? false,
      async: self::$config['async'] ?? false
    );
  }

  public function css(string $uri): string
  {
    return tag(tagName: 'link', attr: [
      'href' => $this->filter($uri),
      'rel' => 'stylesheet'
    ]);
  }

  public function filter(string $uri): string
  {
    return $this->underscore($this->prefix($uri));
  }

  public function underscore(string $uri): string
  {
    if (self::$config['underscore'] ?? false) {
      if (str_contains($uri, '?')) {
        return $uri . '&_=' . microtime();
      }
      return $uri . '?_=' . microtime();
    }
    return $uri;
  }

  public function prefix(string $uri): string
  {
    if (!empty(self::$config['prefix'])) {
      if (!str_starts_with($uri, '//') && !str_starts_with($uri, 'http://') && !str_starts_with($uri, 'https://')) {
        if (!str_starts_with($uri, '/')) {
          $uri = '/' . $uri;
        }
        $uri = self::$config['prefix'] . $uri;
      }
    }
    return $uri;
  }
}