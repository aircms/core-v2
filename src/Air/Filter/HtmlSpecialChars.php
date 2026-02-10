<?php

declare(strict_types=1);

namespace Air\Filter;

class HtmlSpecialChars extends FilterAbstract
{
  public function filter($value)
  {
    return htmlspecialchars($value ?? '');
  }
}