<?php

declare(strict_types=1);

use Air\Type\File;
use Air\Util\Arr;

function isNonClosingTag(string $tagName): bool
{
  return in_array($tagName, [
    'input',
    'meta',
    'base',
    'br',
    'hr',
    'link',
    'img',
    'source',
  ]);
}

function content(mixed $content = null, string $separator = ''): ?string
{
  if ($content === null) {
    return null;
  }

  if (is_string($content)) {
    $content = (array)$content;

  } else if (is_int($content)) {
    $content = (array)((string)$content);

  } else if (is_float($content)) {
    $content = (array)((string)$content);

  } else if (is_bool($content)) {
    $content = (array)($content ? "True" : "False");

  } else if ($content instanceof Generator) {
    $content = Arr::map($content, fn($item) => content($item));

  } else if ($content instanceof Closure) {
    ob_start();
    if (!($content = $content())) {
      $content = ob_get_contents();
    }
    $content = (array)content($content);
    ob_end_clean();
  }

  return implode($separator, Arr::filter($content, fn($item) => !is_null($item)));
}

function src(File|string $src): string
{
  return $src instanceof File
    ? $src->getSrc()
    : $src;
}

function when($var, $content = null): ?string
{
  if ($var) {
    return content($content);
  }
  return null;
}