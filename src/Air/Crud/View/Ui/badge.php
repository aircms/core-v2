<?php

declare(strict_types=1);

function badge($content, $color = PRIMARY, $url = null): string
{
  $el = span($content, [
    'badge',
    'd-inline-flex',
    'gap-1',
    'badge-' . $color,
  ]);

  if ($url) {
    $el = lnk($el, $url);
  }

  return $el;
}

function badgeCond($condition, $label, $url = null, $trueColor = PRIMARY, $falseColor = DARK): string
{
  return !!$condition
    ? badge($condition . ' ' . $label, color: $trueColor, url: $url)
    : badge('No ' . $label, color: $falseColor);
}