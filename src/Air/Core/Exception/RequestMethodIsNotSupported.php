<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class RequestMethodIsNotSupported extends Exception
{
  public function __construct(string $method)
  {
    parent::__construct($method, 500);
  }
}