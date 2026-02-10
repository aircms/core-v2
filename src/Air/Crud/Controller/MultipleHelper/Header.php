<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Core\Front;
use Throwable;

trait Header
{
  protected function getIcon(): string|null
  {
    $icon = $this->getMods('icon');
    if (!$icon) {
      $icon = $this->getAdminMenuItem()['icon'] ?? null;
    }
    return $icon;
  }

  protected function getTitle(): string
  {
    $title = $this->getMods('title');

    if (!$title) {
      try {
        $menuItem = $this->getAdminMenuItem();
        $title = implode('&nbsp;&nbsp;&#8250;&nbsp;&nbsp;', [$menuItem['parent']['title'], $menuItem['title']]);
      } catch (Throwable) {
        $title = ucfirst($this->getRouter()->getController());
      }
    }

    return $title;
  }

  protected function getAdminMenuItem(): array|null
  {
    $controllerClassPars = explode('\\', get_class($this));
    $section = strtolower(end($controllerClassPars));

    foreach (Front::getInstance()->getConfig()['air']['admin']['menu'] ?? [] as $menu) {
      foreach ($menu['items'] as $subMenu) {
        if (strtolower($subMenu['url']['controller'] ?? '') == $section) {
          return [...$subMenu, ...['parent' => [
            'title' => $menu['title'],
            'icon' => $menu['icon']
          ]]];
        }
      }
    }
    return null;
  }
}