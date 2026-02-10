<?php

declare(strict_types=1);

namespace Air\Model\Exception;

use Exception;

class DriverClassDoesNotExists extends Exception
{
  public function __construct(string $className)
  {
    parent::__construct($className);
  }
}