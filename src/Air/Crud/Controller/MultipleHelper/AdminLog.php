<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Core\Front;
use Air\Crud\Auth;
use Air\Crud\Model\History;
use Air\Crud\Nav\Nav;

trait AdminLog
{
  protected function adminLog(
    string  $type,
    ?array  $entity = [],
    ?string $section = null,
    ?array  $was = [],
    ?array  $became = []
  ): void
  {
    if (Nav::getSettingsItem(Nav::SETTINGS_ADMINISTRATORS_HISTORY) ?? false) {
      if ($this instanceof \Air\Crud\Controller\History) {
        return;
      }
      $section = $section ?? $this->getTitle() ?? null;
      if (!$section) {
        $section = strtolower($this->getRouter()->getController());
        foreach (Front::getInstance()->getConfig()['air']['admin']['menu'] ?? [] as $menu) {
          foreach ($menu['items'] as $subMenu) {
            if (isset($subMenu['url'])) {
              if (strtolower($subMenu['url']['controller']) == $section) {
                $section = $subMenu['title'];
              }
            }
          }
        }
      }

      $history = new History();
      $data = [
        'admin' => ['login' => Auth::getInstance()->getName()],
        'type' => $type,
        'section' => $section,
        'entity' => $entity,
        'was' => $was,
        'became' => $became
      ];
      $data['search'] = serialize($data);
      $history->populate($data);
      $history->save();
    }
  }
}