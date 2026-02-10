<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Throwable;

class FaIcon extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/fa-icon';

  public function getValue(): ?\Air\Type\FaIcon
  {
    $value = parent::getValue();

    if (is_array($value)) {
      $value = new \Air\Type\FaIcon($value);

    } else if (is_string($value)) {
      try {
        return new \Air\Type\FaIcon(json_decode($value, true));
      } catch (Throwable) {
      }

    } else if ($value instanceof \Air\Type\FaIcon) {
      return $value;
    }

    return $value;
  }

  public function getCleanValue(): mixed
  {
    return $this->getValue()?->toArray();
  }
}