<?php

declare(strict_types=1);

namespace Air\Filter;

class Trim extends FilterAbstract
{
  public function filter($value)
  {
    return trim($value ?? '');
  }
}
