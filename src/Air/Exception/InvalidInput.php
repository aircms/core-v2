<?php

declare(strict_types=1);

namespace Air\Exception;

use Exception;

class InvalidInput extends Exception
{
  public function __construct(?string $message = null)
  {
    parent::__construct($message ?? '', 400);
  }
}