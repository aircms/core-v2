<?php

declare(strict_types=1);

namespace Air\View\Helper;

class Partial extends HelperAbstract
{
  public function call(string $template, array $vars = []): string
  {
    return $this->getView()->render($template, $vars);
  }
}