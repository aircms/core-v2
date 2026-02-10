<?php

declare(strict_types=1);

namespace Air\Type;

use Air\Core\Front;
use Air\Storage;

class File extends TypeAbstract
{
  const string JPG = 'jpg';
  const string PNG = 'png';
  const string WEBP = 'webp';
  const string AVIF = 'avif';

  public int $size = 0;
  public string $mime = '';
  public string $path = '';
  public int $time = 0;
  public string $src = '';
  public string $thumbnail = '';
  public string $title = '';
  public string $alt = '';
  public array $dims = [
    'width' => 0,
    'height' => 0
  ];

  public function getSize(): int
  {
    return $this->size;
  }

  public function getMime(): string
  {
    return $this->mime;
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function getTime(): int
  {
    return $this->time;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getAlt(): string
  {
    return $this->alt;
  }

  public function getDims(): array
  {
    return $this->dims;
  }

  public function getThumbnail(): string
  {
    if (str_starts_with($this->thumbnail, 'http')) {
      return $this->thumbnail;
    }

    $storageUrl = Front::getInstance()->getConfig()['air']['storage']['url'];
    $thumbnail = implode('/', array_filter(explode('/', $this->thumbnail)));

    return $storageUrl . $thumbnail;
  }

  public function getSrc(
    ?int    $width = null,
    ?int    $height = null,
    ?int    $quality = null,
    ?string $format = null
  ): string
  {
    if (str_starts_with($this->src, 'http')) {
      return $this->src;
    }

    $src = $this->src;

    $pathInfo = pathinfo($src);
    $dirname = $pathInfo['dirname'] ?? '';
    $filename = $pathInfo['filename'] ?? '';
    $ext = strtolower($pathInfo['extension'] ?? '');

    $mods = [];
    if ($width !== null) $mods[] = 'w' . $width;
    if ($height !== null) $mods[] = 'h' . $height;
    if ($quality !== null) $mods[] = 'q' . $quality;

    $newFilename = $filename;
    $newExt = $format ? strtolower($format) : $ext;

    if (!empty($mods) || $format) {
      $newFilename .= '_mod_';
    }

    if (!empty($mods)) {
      $newFilename .= implode('_', $mods);
    }

    $src = array_values(array_filter(explode('/', "{$dirname}/{$newFilename}.{$newExt}")));
    $src = implode('/', $src);

    return Front::getInstance()->getConfig()['air']['storage']['url'] . $src;
  }

  public function getFilename(): string
  {
    $src = explode('/', $this->src);
    return array_pop($src);
  }

  public function getSrcContent(): string|false
  {
    return file_get_contents($this->getSrc());
  }

  public function getBase64Content(): string
  {
    return base64_encode($this->getSrcContent());
  }

  public function isImage(): bool
  {
    return str_contains($this->getMime(), 'image');
  }

  public function remove(): bool
  {
    if ($this->mime === 'directory') {
      return Storage::deleteFolder($this->src);
    }
    return Storage::deleteFile($this->src);
  }

  public static function fromArray(?array $file): self
  {
    return new self($file);
  }
}