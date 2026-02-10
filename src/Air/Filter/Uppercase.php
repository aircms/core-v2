<?php

declare(strict_types=1);

namespace Air\Filter;

class Uppercase extends FilterAbstract
{
  public function filter($value)
  {
    return strtoupper($value);
  }
}