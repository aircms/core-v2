<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class LoaderClassDoesNotExists extends Exception
{
  public function __construct(string $className)
  {
    parent::__construct($className, 500);
  }
}