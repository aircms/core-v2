<?php

declare(strict_types=1);

namespace Air\Model\Meta\Exception;

use Exception;

class PropertyWasNotFound extends Exception
{
  public function __construct(string $collection, string $property)
  {
    parent::__construct("Collection: '" . $collection . "', property: " . $property);
  }
}