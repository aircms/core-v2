<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class ControllerClassWasNotFound extends Exception
{
  public function __construct(string $controllerClassName)
  {
    parent::__construct($controllerClassName, 404);
  }
}