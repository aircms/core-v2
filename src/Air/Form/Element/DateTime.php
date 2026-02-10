<?php

declare(strict_types=1);

namespace Air\Form\Element;

class DateTime extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/date-time';
  public string $format = 'yyyy-MM-dd HH:mm';
  public string $phpFormat = 'Y-m-d H:i';

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

  public function getValue(): ?int
  {
    $value = parent::getValue();

    if (empty($value)) {
      return 0;
    }

    if (is_string($value)) {
      return intval(strtotime($value));
    }

    return intval($value);
  }
}
