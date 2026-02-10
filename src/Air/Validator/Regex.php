<?php

declare(strict_types=1);

namespace Air\Validator;

class Regex extends ValidatorAbstract
{
  public string $pattern = '';

  public function getPattern(): string
  {
    return $this->pattern;
  }

  public function setPattern(string $pattern): void
  {
    $this->pattern = $pattern;
  }

  public function isValid($value): bool
  {
    $value = $value ?? '';
    if (empty($value) && $this->allowNull) {
      return true;
    }
    return !!preg_match($this->pattern, $value);
  }

  public static function valid(string $errorMessage = '', bool $allowNull = false, string $pattern = ''): static
  {
    return new static([
      'errorMessage' => $errorMessage,
      'pattern' => $pattern,
      'allowNull' => $allowNull
    ]);
  }
}