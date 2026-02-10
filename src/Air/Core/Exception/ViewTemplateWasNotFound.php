<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class ViewTemplateWasNotFound extends Exception
{
  public function __construct(string $template)
  {
    parent::__construct($template, 500);
  }
}