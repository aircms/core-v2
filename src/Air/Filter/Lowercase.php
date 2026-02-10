<?php

declare(strict_types=1);

namespace Air\Filter;

class Lowercase extends FilterAbstract
{
  public function filter($value)
  {
    return strtolower($value);
  }
}