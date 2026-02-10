<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;

class RobotsTxt extends Single
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_ROBOTSTXT;
  }
}
