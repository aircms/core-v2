<?php

declare(strict_types=1);

namespace Air\Validator;

class Phone extends ValidatorAbstract
{
  public function isValid($value): bool
  {
    $value = $value ?? '';

    if (empty($value) && $this->allowNull) {
      return true;
    }

    return strlen(trim($value)) >= 10;
  }
}
