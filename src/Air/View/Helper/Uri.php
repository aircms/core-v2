<?php

declare(strict_types=1);

namespace Air\View\Helper;

use Air\Core\Front;

class Uri extends HelperAbstract
{
  public function call(array $route = [], array $params = [], bool $reset = true): string
  {
    return Front::getInstance()->getRouter()->assemble($route, $params, $reset);
  }
}
