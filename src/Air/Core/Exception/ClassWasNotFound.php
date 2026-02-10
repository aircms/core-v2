<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class ClassWasNotFound extends Exception
{
  public function __construct(?string $className = null)
  {
    parent::__construct($className, 404);
  }
}