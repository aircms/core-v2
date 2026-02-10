<?php

declare(strict_types=1);

namespace Air\Filter;

class Phone extends FilterAbstract
{
  public function filter($value): string
  {
    return trim(str_replace(['+', '-', ' ', '-', '(', ')', '.', ','], '', $value ?? ''));
  }
}
