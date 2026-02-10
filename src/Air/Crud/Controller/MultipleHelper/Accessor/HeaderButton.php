<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper\Accessor;

class HeaderButton
{
  public static function item(
    array|string $url = [],
    array        $params = [],
    ?string      $icon = null,
    ?string      $style = null,
    ?string      $title = null,
    string|false $confirm = false,
    bool         $force = false
  ): array
  {
    return [
      'url' => $url,
      'params' => $params,
      'title' => $title,
      'confirm' => $confirm,
      'force' => $force,
      'style' => [
        'icon' => $icon,
        'color' => $style
      ],
    ];
  }
}