<?php

declare(strict_types=1);

namespace Air\Validator;

abstract class ValidatorAbstract
{
  public bool $allowNull = false;
  public string $errorMessage = '';

  public function __construct(array $options = [])
  {
    foreach ($options as $name => $value) {

      if (is_callable([$this, 'set' . ucfirst($name)])) {
        call_user_func_array([$this, 'set' . ucfirst($name)], [$value]);
      }
    }
  }

  public function isAllowNull(): bool
  {
    return $this->allowNull;
  }

  public function setAllowNull(bool $allowNull): void
  {
    $this->allowNull = $allowNull;
  }

  public function getErrorMessage(): string
  {
    return $this->errorMessage;
  }

  public function setErrorMessage(string $errorMessage): void
  {
    $this->errorMessage = $errorMessage;
  }

  public static function valid(
    string $errorMessage = '',
    bool   $allowNull = false
  ): static
  {
    return new static([
      'errorMessage' => $errorMessage,
      'allowNull' => $allowNull,
    ]);
  }

  public abstract function isValid($value): bool;
}
