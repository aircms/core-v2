<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

class KeyValue extends KeyValueAbstract
{
  public ?string $elementTemplate = 'form/element/key-value';

  public function isValid($value): bool
  {
    $isValid = parent::isValid($value);

    if (!$isValid) {
      return false;
    }

    if (!$this->isAllowNull()) {
      $value = $this->getValue();

      if (!strlen($value[$this->getKeyPropertyName()]) || !strlen($value[$this->getValuePropertyName()])) {
        $this->errorMessages[] = Locale::t('Could not be empty');
        return false;
      }
    }

    return true;
  }

  public function getValue(): ?array
  {
    $value = (array)parent::getValue();

    if (!strlen(($value[$this->getKeyPropertyName()]) ?? '') ||
      !strlen(($value[$this->getValuePropertyName()] ?? ''))) {
      return null;
    }

    return $value;
  }
}
