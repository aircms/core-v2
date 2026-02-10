<?php

declare(strict_types=1);

namespace Air;

use Air\Type\File;

class SitemapImage
{
  private array $urls = [];

  public function addUrl(string $route, array $files = []): static
  {
    /** @var string[]|File[] $files */

    if (!count($files)) {
      return $this;
    }

    $url = [
      'loc' => $route,
      'images' => []
    ];

    foreach ($files as $file) {

      if ($file instanceof File) {
        $url['images'][] = $file->getSrc();
      } else {
        $url['images'][] = $file;
      }
    }

    $this->urls[] = $url;
    return $this;
  }

  public function render(): string
  {
    $xml = [
      '<?xml version="1.0" encoding="UTF-8"?>',
      '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
      'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">',
    ];

    foreach ($this->urls as $url) {
      $xml[] = '<url>';
      $xml[] = '<loc>' . $url['loc'] . '</loc>';

      foreach ($url['images'] as $image) {
        $xml[] = '<image:image>';
        $xml[] = '<image:loc>' . $image . '</image:loc>';
        $xml[] = '</image:image>';
      }

      $xml[] = '</url>';
    }

    $xml[] = '</urlset>';
    return implode("\n", $xml);
  }
}