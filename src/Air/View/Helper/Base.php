<?php

declare(strict_types=1);

namespace Air\View\Helper;

use Air\Core\Front;

class Base extends HelperAbstract
{
  public function call(): string
  {
    return base(Front::getInstance()->getConfig()['air']['asset']['prefix'] . '/');
  }
}
