<?php

declare(strict_types=1);

namespace Air;

use Air\Crud\Model\Language;
use App\Helper\Route;

class Sitemap
{
  private Language $defaultLanguage;

  /** @var Language[] $languages */
  private mixed $languages = [];

  private array $urls = [];

  public function __construct()
  {
    $this->defaultLanguage = Language::one(['isDefault' => true]);
    $this->languages = Language::all();
  }

  public function addUrl(array $route, array $params, int $lastMod, string $priority = '0.80'): static
  {
    if (!$lastMod) {
      $lastMod = time();
    }

    $url = [
      'loc' => Route::assembleWithLanguage($this->defaultLanguage, $route, $params, false),
      'priority' => $priority,
      'lastmod' => $lastMod,
      'alternate' => []
    ];

    foreach ($this->languages as $language) {
      if ($language->isDefault) {
        $url['alternate']['x-default'] = Route::assembleWithLanguage($language, $route, $params, false);
      } else {
        $url['alternate'][$language->key] = Route::assembleWithLanguage($language, $route, $params, false);
      }
    }

    $this->urls[] = $url;

    return $this;
  }

  public function getUrls(): array
  {
    return $this->urls;
  }

  public function render(): string
  {
    $xml = [
      '<?xml version="1.0" encoding="UTF-8"?>',
      '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
      'xmlns:xhtml="http://www.w3.org/1999/xhtml">',
    ];

    foreach ($this->urls as $url) {
      $xml[] = '<url>';
      $xml[] = '<loc>' . $url['loc'] . '</loc>';
      $xml[] = '<priority>' . $url['priority'] . '</priority>';
      $xml[] = '<lastmod>' . date('c', $url['lastmod']) . '</lastmod>';

      foreach ($url['alternate'] as $key => $route) {
        $xml[] = '<xhtml:link rel="alternate" hreflang="' . $key . '" href="' . $route . '" />';
      }

      $xml[] = '</url>';
    }

    $xml[] = '</urlset>';
    return implode("\n", $xml);
  }
}
