<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Date extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/date';
  public string $format = 'YYYY-MM-DD';
  public string $phpFormat = 'Y-m-d';

  public function getFormat(): string
  {
    return $this->format;
  }

  public function setFormat(string $format): void
  {
    $this->format = $format;
  }

  public function getPhpFormat(): string
  {
    return $this->phpFormat;
  }

  public function setPhpFormat(string $phpFormat): void
  {
    $this->phpFormat = $phpFormat;
  }

  public function getValue(): int
  {
    $value = parent::getValue();

    if (is_string($value) && strlen($value)) {
      return strtotime($value);
    }

    return intval($value) ?? 0;
  }
}
