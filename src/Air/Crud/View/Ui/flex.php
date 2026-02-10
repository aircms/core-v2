<?php

declare(strict_types=1);

function vertical($content, $class = true, $space = SPACE1, $justify = START, $align = START): string
{
  return div($content, [
    'd-flex',
    'flex-column',
    'flex-wrap',
    'justify-items-' . $justify,
    'align-items-' . $align,
    'gap-' . $space,
    $class
  ]);
}

function horizontal($content, $class = null, $space = SPACE1, $justify = START, $align = START): string
{
  return div($content, [
    'd-flex',
    'flex-wrap',
    'justify-items-' . $justify,
    'align-items-' . $align,
    'gap-' . $space,
    $class
  ]);
}