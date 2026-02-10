<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper\Accessor;

use Air\Core\Front;
use Closure;

class Control
{
  public static function separator(): array
  {
    return ['type' => 'separator'];
  }

  public static function enabled(): array
  {
    return ['type' => 'enabled'];
  }

  public static function copy(): array
  {
    return ['type' => 'copy'];
  }

  public static function localizedCopy(): array
  {
    return ['type' => 'localizedCopy'];
  }

  public static function view(): array
  {
    return ['type' => 'view'];
  }

  public static function manage(): array
  {
    return ['type' => 'edit'];
  }

  public static function print(): array
  {
    return ['type' => 'print'];
  }

  public static function runAndReload(array|string $url, string $icon, string $title, string|false $confirm = false): array
  {
    if (is_array($url)) {
      $url['controller'] = $url['controller'] ?? Front::getInstance()->getRouter()->getController();
    }

    return [
      'type' => 'run-and-reload',
      'url' => $url,
      'icon' => $icon,
      'title' => $title,
      'confirm' => $confirm,
    ];
  }

  public static function source(Closure $source): array
  {
    return ['type' => 'custom', 'source' => $source];
  }

  public static function item(array|string $url, string $icon, string $title, string|false $confirm = false): array
  {
    return [
      'url' => $url,
      'icon' => $icon,
      'title' => $title,
      'confirm' => $confirm,
    ];
  }

  public static function modal(array|string $url, string $icon, string $title, string|false $confirm = false): array
  {
    return [
      'url' => $url,
      'icon' => $icon,
      'title' => $title,
      'confirm' => $confirm,
      'modal' => 'iframe',
    ];
  }

  public static function html(array|string $url, string $icon, string $title, string|false $confirm = false): array
  {
    $url['controller'] = $url['controller'] ?? Front::getInstance()->getRouter()->getController();
    return [
      'url' => $url,
      'icon' => $icon,
      'title' => $title,
      'confirm' => $confirm,
      'modal' => 'htmlAjax',
    ];
  }
}