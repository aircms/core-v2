<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class RouterWasNotFound extends Exception
{
  public function __construct(string $router)
  {
    parent::__construct($router, 404);
  }
}