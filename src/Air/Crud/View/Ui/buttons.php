<?php

declare(strict_types=1);

function html(string $url, $content, ?string $title = ''): string
{
  return div(content: content($content), attr: [
    'onclick' => "modal.htmlAjax('" . $url . "', '" . $title . "');",
    'role' => 'button'
  ]);
}

function modal(string $url, $content): string
{
  return div(content: content($content), attr: [
    'onclick' => "modal.embed('" . $url . "');",
    'role' => 'button'
  ]);
}

function lnk($content, string $url, $confirm = null, $color = PRIMARY, $size = MD, $isBlank = false): string
{
  if (str_contains($url, '@')) {
    $url = 'mailto:' . $url;

  } else if (!str_starts_with($url, 'http')) {
    $url = 'tel:' . $url;
  }

  return a(
    href: $url,
    content: $content,
    class: ['d-inline-flex', getTextColor($color), getFontSize($size)],
    attr: [
      ...$confirm ? ['data-confirm' => $confirm] : [],
      ...$isBlank ? ['target' => '_blank'] : [],
    ],
  );
}

function btn(
  $content,
  $url = '#',
  $class = null,
  $confirm = null,
  $style = PRIMARY,
  $size = SM,
  $icon = null,
  $attr = [],
  $data = [],
  $type = null,
): string
{
  if ($confirm) {
    $data['confirm'] = $confirm;
  }

  $content = [
    $icon ? faIcon($icon, class: 'me-2') : null,
    $content,
  ];

  $class = ['btn', 'btn-' . $style, 'btn-' . $size, $class];

  if ($type === 'button' || $type === 'submit') {
    $attr['href'] = $url;
    return tag('button', $content, $class, $attr, $data);
  }

  return a(
    href: $url,
    content: $content,
    class: $class,
    data: $data,
    attr: count($attr) ? $attr : []
  );
}