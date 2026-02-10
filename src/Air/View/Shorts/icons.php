<?php

declare(strict_types=1);

use Air\Type\FaIcon;

function gsIcon($icon = null, $attr = null, $class = null, $fill = 0, $weight = 300, $grad = 2, $opsz = 24, $tag = 'i'): string
{
  $attr = $attr ?? [];
  $attr['style'] = "font-variation-settings: 'FILL' $fill, 'wght' $weight, 'GRAD' $grad, 'opsz' $opsz";

  return tag(
    tagName: $tag,
    attr: $attr,
    class: $class,
    content: $icon
  );
}

function faIcon($icon = null, $style = null, $data = null, $attr = null, $tag = 'i', $class = null): ?string
{
  if ($icon === null) {
    return null;
  }

  if (is_string($icon)) {
    $icon = new FaIcon([
      'icon' => $icon,
    ]);
  }

  $class = (array)$class ?? [];

  if ($icon->isBrand()) {
    $class[] = 'fa-brands';

  } else {
    $class[] = $style ?? $icon->getStyle();
  }

  $class[] = 'fa-' . $icon->getIcon();

  $class = array_unique($class);

  return tag(
    tagName: $tag,
    class: $class,
    data: $data,
    attr: $attr,
  );
}
