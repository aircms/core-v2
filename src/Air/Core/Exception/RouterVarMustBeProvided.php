<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class RouterVarMustBeProvided extends Exception
{
  public function __construct(string $var)
  {
    parent::__construct($var, 500);
  }
}