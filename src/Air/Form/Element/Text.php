<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Text extends TextAbstract
{
  const string TYPE_TEXT = 'text';
  const string TYPE_NUMBER = 'number';
  const string TYPE_COLOR = 'color';
  const string TYPE_EMAIL = 'email';
  const string TYPE_TEL = 'tel';
  const string TYPE_PASSWORD = 'password';

  public string $type = self::TYPE_TEXT;
  public ?string $elementTemplate = 'form/element/text';

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): void
  {
    $this->type = $type;
  }

  public function getValue(): mixed
  {
    if ($this->type === self::TYPE_NUMBER) {
      $floatValue = (float)parent::getValue();
      if (floor($floatValue) == $floatValue) {
        return (int)$floatValue;
      }
      return $floatValue;
    }
    return parent::getValue();
  }
}