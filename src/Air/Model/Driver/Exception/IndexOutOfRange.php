<?php

declare(strict_types=1);

namespace Air\Model\Driver\Exception;

use Exception;

class IndexOutOfRange extends Exception
{
  public function __construct(int $index, int $maxValue)
  {
    parent::__construct($index . ', max value is ' . $maxValue);
  }
}