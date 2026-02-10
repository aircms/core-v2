<?php

declare(strict_types=1);

namespace Air\Filter;

class Safe extends FilterAbstract
{
  public function filter($value)
  {
    return htmlspecialchars(trim(strip_tags($value ?? '')));
  }
}
