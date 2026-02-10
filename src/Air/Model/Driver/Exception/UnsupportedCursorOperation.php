<?php

declare(strict_types=1);

namespace Air\Model\Driver\Exception;

use Exception;

class UnsupportedCursorOperation extends Exception
{
  public function __construct(?string $operation = null)
  {
    parent::__construct($operation);
  }
}