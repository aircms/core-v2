<?php

declare(strict_types=1);

namespace Air\Filter;

class StripTags extends FilterAbstract
{
  public function filter($value)
  {
    return strip_tags($value);
  }
}