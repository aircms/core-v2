<?php

declare(strict_types=1);

function title($title, $icon = null, $buttons = null): string
{
  return div(
    class: 'p-3 pb-0 position-sticky top-0 z-i-1001 header',
    content: card(
      containerClass: 'position-sticky w-100',
      contentClass: 'd-flex justify-content-between align-items-center w-100',
      content: [
        div([faIcon($icon, class: 'me-2'), $title], 'fw-semibold'),
        div($buttons, 'd-flex align-items-center gap-2'),
      ]
    )
  );
}

function contents($content): string
{
  return div($content, 'p-3 overflow-hidden overflow-y-auto z-1 content');
}

function page($title = null, $content = null, $icon = null, $buttons = null): string
{
  return content([
    when($title, title($title, $icon, $buttons)),
    when($content, contents($content)),
  ]);
}