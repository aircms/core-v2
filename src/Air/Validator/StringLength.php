<?php

declare(strict_types=1);

namespace Air\Validator;

class StringLength extends Number
{
  public string $encoding = 'UTF-8';

  public ?int $exact = null;

  public function getEncoding(): string
  {
    return $this->encoding;
  }

  public function setEncoding(string $encoding): void
  {
    $this->encoding = $encoding;
  }

  public function isValid($value): bool
  {
    $value = $value ?? '';

    if (empty($value) && $this->allowNull) {
      return true;
    }

    if ($this->exact) {
      return $this->exact === mb_strlen($value, $this->encoding);
    }

    if ($this->min && $this->min > mb_strlen($value, $this->encoding)) {
      return false;
    }

    if ($this->max && $this->max < mb_strlen($value, $this->encoding)) {
      return false;
    }

    return true;
  }
}
