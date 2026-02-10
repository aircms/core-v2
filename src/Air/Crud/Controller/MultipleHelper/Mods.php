<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Exception;
use ReflectionClass;

trait Mods
{
  protected function getMods(string $type): mixed
  {
    $reflection = new ReflectionClass(static::class);

    $docComment = $reflection->getDocComment();

    if ($docComment === false) {
      return [];
    }

    $mods = array_values(
      array_map(function ($item) use ($type) {
        return trim(str_replace('@mod-' . $type . " ", '', $item));
      },
        array_filter(
          explode("\n", str_replace('*', ' ', $docComment)),
          function ($item) use ($type) {
            return strstr($item, '@mod-' . $type);
          }
        )
      )
    );

    if (
      $type === 'title' ||
      $type === 'icon' ||
      $type === 'items-per-page' ||
      $type === 'manageable' ||
      $type === 'sortable' ||
      $type === 'quick-manage' ||
      $type === 'manageable-multiple' ||
      $type === 'printable' ||
      $type === 'exportable'
    ) {
      return $mods[0] ?? false;
    }

    foreach ($mods as $index => $mod) {
      if ($mod = json_decode($mod, true)) {
        $mods[$index] = $mod;
        continue;
      }
      throw new Exception("Modification with type: {$type} have not a valid JSON: {$mod}");
    }

    return $mods;
  }
}