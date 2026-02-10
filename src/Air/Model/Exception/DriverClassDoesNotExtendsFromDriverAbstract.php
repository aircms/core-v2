<?php

declare(strict_types=1);

namespace Air\Model\Exception;

use Exception;

class DriverClassDoesNotExtendsFromDriverAbstract extends Exception
{
  public function __construct(string $className)
  {
    parent::__construct($className);
  }
}