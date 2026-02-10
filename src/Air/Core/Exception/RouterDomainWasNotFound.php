<?php

declare(strict_types=1);

namespace Air\Core\Exception;

use Exception;

class RouterDomainWasNotFound extends Exception
{
  public function __construct(string $domain)
  {
    parent::__construct($domain, 404);
  }
}