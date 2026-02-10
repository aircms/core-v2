<?php

declare(strict_types=1);

namespace Air\System;

use Exception;

class SystemProcNotReadable extends Exception
{
  public function __construct(string $proc)
  {
    parent::__construct($proc, 500);
  }
}