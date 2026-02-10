<?php

declare(strict_types=1);

namespace Air\Validator;

class Contain extends ValidatorAbstract
{
  public array $list = [];

  public function getList(): array
  {
    return $this->list;
  }

  public function setList(array $list): void
  {
    $this->list = $list;
  }

  public function isValid($value): bool
  {
    $value = $value ?? '';

    if (empty($value) && $this->allowNull) {
      return true;
    }

    return !!in_array($value, $this->list);
  }

  public static function valid(string $errorMessage = '', bool $allowNull = false, array $list = []): static
  {
    return new static([
      'errorMessage' => $errorMessage,
      'allowNull' => $allowNull,
      'list' => $list,
    ]);
  }
}