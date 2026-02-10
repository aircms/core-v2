<?php

declare(strict_types=1);

namespace Air\ThirdParty\GoogleOAuth\Exception;

use Exception;

class UnableToGetUserByAccessToken extends Exception
{
  public function __construct(string $accessToken)
  {
    parent::__construct($accessToken, 400);
  }
}