<?php

declare(strict_types=1);

namespace Air\Filter;

class Email extends FilterAbstract
{
  public function filter($value)
  {
    return trim(strtolower($value));
  }
}
