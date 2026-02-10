<?php

declare(strict_types=1);

namespace Air\Model\Driver\Exception;

use Exception;

class PropertyWasNotFound extends Exception
{
  public function __construct(string $className, string $propertyName)
  {
    parent::__construct($className . '::' . $propertyName);
  }
}