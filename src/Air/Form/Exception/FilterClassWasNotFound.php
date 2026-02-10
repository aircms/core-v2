<?php

declare(strict_types=1);

namespace Air\Form\Exception;

use Exception;

class FilterClassWasNotFound extends Exception
{
  public function __construct(?string $filterClassName = null)
  {
    parent::__construct($filterClassName);
  }
}
