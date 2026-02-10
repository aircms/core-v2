<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Core\Front;
use Air\Crud\Locale;
use Air\Crud\Nav\Nav;
use Throwable;

class Permissions extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/permissions';

  public function __construct(string $name, array $userOptions = [])
  {
    $userOptions['allowNull'] = true;
    parent::__construct($name, $userOptions);
  }

  public function getPermissions(): array
  {
    $permissions = Front::getInstance()->getConfig()['air']['admin']['menu'];

    $sections = [];
    foreach (Nav::getSettingItems() as $group) {
      foreach ($group as $setting) {
        $sections[$setting['title']] = $setting['alias'];
      }
    }

    $systemPermissions = [];
    foreach ($sections as $title => $controller) {
      $systemPermissions[] = [
        'title' => $title,
        'url' => ['controller' => $controller],
      ];
    }

    if (count($systemPermissions)) {
      $permissions[] = [
        'title' => Locale::t('System settings'),
        'items' => $systemPermissions
      ];
    }

    $_permissions = [];
    foreach ($permissions as $sectionIndex => $section) {
      foreach ($section['items'] as $page) {
        if ($page !== 'divider') {
          $_permissions[$sectionIndex] = $_permissions[$sectionIndex] ?? [
            'title' => $section['title'],
            'items' => []
          ];
          $_permissions[$sectionIndex]['items'][] = $page;
        }
      }
    }

    return $_permissions;
  }

  public function getValue(): array
  {
    $selectedPermissions = [];
    $permissions = [];

    foreach ($this->getPermissions() as $groups) {
      foreach ($groups['items'] as $permission) {
        try {
          $permissions[md5(serialize($permission['url']))] = $permission['url'];
        } catch (Throwable) {
          die();
        }
      }
    }

    $value = (array)parent::getValue();

    if (count($value)) {
      if (!isset($value[0])) {
        foreach (array_keys($value) as $permission) {
          $selectedPermissions[] = $permissions[$permission];
        }
      } else {
        foreach ($value as $permission) {
          $selectedPermissions[] = $permission;
        }
      }
    }

    return $selectedPermissions;
  }

  public function isPermitted(array $route): bool
  {
    foreach ($this->getValue() as $permittedRoute) {
      if (md5(serialize($permittedRoute)) == md5(serialize($route['url']))) {
        return true;
      }
    }
    return false;
  }
}