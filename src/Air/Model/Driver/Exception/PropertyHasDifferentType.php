<?php

declare(strict_types=1);

namespace Air\Model\Driver\Exception;

use Exception;

class PropertyHasDifferentType extends Exception
{
  public function __construct(
    string $className,
    string $propertyName,
    string $validPropertyType,
    string $invalidPropertyType
  )
  {
    parent::__construct($className . '::' . $propertyName . ' trying to set value with type ' . $invalidPropertyType . ' instead of valid type ' . $validPropertyType);
  }
}