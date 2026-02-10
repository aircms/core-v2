<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class ActionMethodWasNotFound extends Exception
{
  public function __construct(string $actionClassName)
  {
    parent::__construct($actionClassName, 404);
  }
}