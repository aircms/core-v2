<?php

declare(strict_types=1);

function row($content = null, $class = null, $attr = null, $bgImage = null, $gt = SPACE1): string
{
  $class = (array)$class ?? [];
  $class[] = 'row gt-' . $gt;

  return div($content, $class, $attr, bgImage: $bgImage);
}

function col(
  $content = null,
  $class = null,
  $attr = null,
  $bgImage = null,
  ?int $col = null,
  ?int $md = null,
  ?int $lg = null,
  ?int $xl = null,
  ?int $xxl = null,
): string
{
  $class = (array)$class ?? [];
  $class = [
    ...$class,
    'col',
    $col ? 'col-' . $col : null,
    $md ? 'col-md-' . $md : null,
    $lg ? 'col-lg-' . $lg : null,
    $xl ? 'col-xl-' . $xl : null,
    $xxl ? 'col-xxl-' . $xxl : null,
  ];

  return div($content, $class, $attr, bgImage: $bgImage);
}