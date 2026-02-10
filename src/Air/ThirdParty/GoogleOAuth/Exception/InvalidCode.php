<?php

declare(strict_types=1);

namespace Air\ThirdParty\GoogleOAuth\Exception;

use Exception;

class InvalidCode extends Exception
{
  public function __construct(string $code)
  {
    parent::__construct($code, 400);
  }
}