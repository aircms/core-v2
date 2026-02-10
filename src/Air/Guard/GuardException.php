<?php

declare(strict_types=1);

namespace Air\Guard;

use Exception;

final class GuardException extends Exception
{
  public function __construct(int $conditionNumber)
  {
    parent::__construct("Guard throw rule: #" . $conditionNumber);
  }
}


