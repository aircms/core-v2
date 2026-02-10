<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

class Meta extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/meta';

  public function isValid($value): bool
  {
    $isValid = parent::isValid($value);

    if (!$this->isAllowNull()) {
      if (strlen(trim(implode('', array_filter($value)))) === 0) {
        $this->errorMessages = [Locale::t('Could not be empty')];
        return false;
      }
    }

    return $isValid;
  }

  public function getValue(): ?\Air\Type\Meta
  {
    $value = (array)parent::getValue();
    if (isset($value['ogImage']) && is_string($value['ogImage'])) {
      $value['ogImage'] = json_decode($value['ogImage'], true);
    }
    $value['useModelData'] = (bool)($value['useModelData'] ?? false);
    return new \Air\Type\Meta($value);
  }
}