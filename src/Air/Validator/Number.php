<?php

declare(strict_types=1);

namespace Air\Validator;

class Number extends ValidatorAbstract
{
  public ?int $min = null;
  public ?int $max = null;

  public function getMin(): int
  {
    return $this->min;
  }

  public function setMin(int $min): void
  {
    $this->min = $min;
  }

  public function getMax(): int
  {
    return $this->max;
  }

  public function setMax(int $max): void
  {
    $this->max = $max;
  }

  public function isValid($value): bool
  {
    if (empty($value) && $this->allowNull) {
      return true;
    }

    if ($this->min && $this->min > (int)$value) {
      return false;
    }

    if ($this->max && $this->max < (int)$value) {
      return false;
    }

    return true;
  }

  public static function valid(
    string $errorMessage = '',
    bool   $allowNull = false,
    ?int   $exact = null,
    ?int   $min = null,
    ?int   $max = null,
  ): static
  {
    return new static(array_filter([
      'errorMessage' => $errorMessage,
      'min' => $min,
      'max' => $max,
      'exact' => $exact,
      'allowNull' => $allowNull,
    ]));
  }
}