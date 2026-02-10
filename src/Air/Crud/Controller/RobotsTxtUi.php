<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Core\Controller;

class RobotsTxtUi extends Controller
{
  public function index(): string
  {
    $this->getResponse()->setHeader('Content-type', 'text/plain');
    return \Air\Crud\Model\RobotsTxt::one()->description;
  }
}
