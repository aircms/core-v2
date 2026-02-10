<?php

declare(strict_types=1);

namespace Air\ThirdParty\Gatewayapi\Exception;

use Exception;

class InvalidResponse extends Exception
{
  public function __construct(array $response)
  {
    parent::__construct(var_export($response, true), 500);
  }
}