<?php

declare(strict_types=1);

function label(string $label, ?string $class = null, $color = PRIMARY, $size = MD): string
{
  return span($label, [
    'd-flex',
    getTextColor($color),
    getFontSize($size),
    $class
  ]);
}