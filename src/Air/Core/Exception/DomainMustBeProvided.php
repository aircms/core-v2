<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class DomainMustBeProvided extends Exception
{
  public function __construct()
  {
    parent::__construct(code: 500);
  }
}