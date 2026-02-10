<?php

declare(strict_types=1);

namespace Air\Form\Element;

class MultipleKeyValue extends KeyValueAbstract
{
  public ?string $elementTemplate = 'form/element/multiple-key-value';

  public function getValue(): array
  {
    $value = parent::getValue();

    if ($value === null) {
      return [];
    }

    return array_values($value);
  }
}