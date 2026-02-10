<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Time extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/time';
  public string $format = 'HH:mm';

  public function getFormat(): string
  {
    return $this->format;
  }

  public function setFormat(string $format): void
  {
    $this->format = $format;
  }
}
