<?php

declare(strict_types=1);

namespace Air\Validator;

class Email extends ValidatorAbstract
{
  public function isValid($value): bool
  {
    $value = $value ?? '';

    if (empty($value) && $this->allowNull) {
      return true;
    }

    $value = trim($value);

    return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
  }
}
