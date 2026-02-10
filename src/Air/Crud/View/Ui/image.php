<?php

declare(strict_types=1);

use Air\Type\File;
use Air\Util\Arr;

function image(File|string|null $image = null, bool $preview = true, $size = SM): ?string
{
  if (!$image) {
    return null;
  }

  $alt = '';
  $title = '';
  $src = $image;
  $mime = '';
  $thumbnail = $image;

  if ($image instanceof File) {
    $alt = $image->getAlt();
    $title = $image->getTitle();
    $src = $image->getSrc();
    $mime = $image->getMime();
    $thumbnail = $image->getThumbnail();
  }

  $embed = $preview ? [
    'admin-embed-modal',
    'admin-embed-modal-alt' => $alt,
    'admin-embed-modal-title' => $title,
    'admin-embed-modal-src' => $src,
    'admin-embed-modal-mime' => $mime,
    'mdb-ripple-init'
  ] : [];

  return div(
    class: 'bg-image rounded-4 shadow-5-strong',
    attr: [
      'role' => 'button',
      'style' => match ($size) {
        SM => 'width: 40px; height: 40px',
        default => null
      }
    ],
    data: [
      'admin-async-image' => $thumbnail,
      ...$embed
    ]
  );
}

function images(?array $images = null, bool $preview = true): ?string
{
  if (!$images) {
    return null;
  }

  return horizontal(content: Arr::map($images, fn($image) => image($image, $preview)));
}