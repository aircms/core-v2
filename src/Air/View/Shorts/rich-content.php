<?php

declare(strict_types=1);

use Air\Type\RichContent;

function richContent(
  string|array|null $content,
  Closure           $fileRenderer = null,
  Closure           $filesRenderer = null,
  Closure           $textRenderer = null,
  Closure           $htmlRenderer = null,
  Closure           $embedRenderer = null,
  Closure           $quoteRendered = null,
  ?string           $containerClassName = null,
  ?string           $itemClassName = null,
): ?string
{
  if (is_string($content)) {
    if ($content !== strip_tags($content)) {
      $content = div(class: [$itemClassName, 'html'], content: $content);
    } else {
      $content = div(class: [$itemClassName, 'text'], content: $content);
    }
    return div(class: [$containerClassName], content: $content);
  }

  if (is_array($content)) {
    $rows = [];

    /**
     * @var int $index
     * @var RichContent $item
     */
    foreach ($content as $index => $item) {
      $renderer = match ($item->getType()) {
        'file' => $fileRenderer,
        'files' => $filesRenderer,
        'text' => $textRenderer,
        'html' => $htmlRenderer,
        'embed' => $embedRenderer,
        'quote' => $quoteRendered,
        default => null
      };
      if ($renderer) {
        $rows[] = div(
          class: [$itemClassName, $item->getType()],
          content: content(function () use ($item, $renderer, $index) {
            return $renderer($item->getValue(), $index);
          })
        );
      }
    }

    return div(class: $containerClassName, content: array_filter($rows));
  }

  return null;
}