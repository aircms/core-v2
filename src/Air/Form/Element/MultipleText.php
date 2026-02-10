<?php

declare(strict_types=1);

namespace Air\Form\Element;

class MultipleText extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/multiple-text';

  public function getValue(): array
  {
    $value = parent::getValue();

    if ($value === null) {
      return [];
    }

    return $value;
  }
}