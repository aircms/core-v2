<?php

declare(strict_types=1);

function card(
  $content = null,
  $title = null,
  $containerClass = null,
  $titleClass = null,
  $bodyClass = null,
  $contentClass = null,
  string $size = SPACE3,
  string $color = SURFACE,
  bool $noLine = false,
  $data = null,
): string
{
  return div(
    class: ['card', 'w-100', 'bg-body-' . $color, $containerClass],
    data: $data,
    content: div(
      class: ['card-body', 'p-' . $size, $bodyClass],
      content: [
        when($title, div($title, ['fw-semibold', $titleClass])),
        when($title && $content, hr(class: $noLine ? 'opacity-0 mb-0' : null)),
        when($content, div($content, $contentClass))
      ]
    )
  );
}