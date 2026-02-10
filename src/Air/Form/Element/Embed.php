<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Embed extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/embed';

  public function getValue(): ?string
  {
    $value = parent::getValue();

    if (!$value || !strlen($value)) {
      return null;
    }

    return $value;
  }
}