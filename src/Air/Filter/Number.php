<?php

declare(strict_types=1);

namespace Air\Filter;

class Number extends FilterAbstract
{
  public function filter($value)
  {
    return intval($value ?? 0);
  }
}
