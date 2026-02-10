<?php

declare(strict_types=1);

namespace Air\Crud\Nav;

use ReflectionClass;

trait NavController
{
  abstract protected function getNav(): string;

  protected function getTitle(): string
  {
    return Nav::getSettingsItem($this->getNav())['title'];
  }

  protected function getIcon(): string
  {
    return Nav::getSettingsItem($this->getNav())['icon'];
  }

  protected function getModelClassName(): string
  {
    $reflection = new ReflectionClass(
      Nav::getSettingsItem($this->getNav())['controller']
    );

    if (str_starts_with($reflection->getName(), "Air")) {
      return '\\Air\\Crud\\Model\\' . $reflection->getShortName();
    }

    return $reflection->getShortName();
  }
}