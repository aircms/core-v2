<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;

class RobotsTxt extends Single
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_ROBOTSTXT;
  }

  protected function getForm($model = null): ?Form
  {
    return Generator::full($model);
  }
}
