<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;
use Air\Model\ModelAbstract;

class MultipleModel extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/multiple-model';
  public string $model = '';
  public array $field = ['title'];

  public function getField(): array
  {
    return $this->field;
  }

  public function setField(array|string $field): void
  {
    if (is_string($field)) {
      $this->field = [$field];
    } else {
      $this->field = $field;
    }
  }

  public function getModel(): ModelAbstract
  {
    return new $this->model;
  }

  public function setModel(string $model): void
  {
    $this->model = $model;
  }

  public function isValid($value): bool
  {
    $isValid = parent::isValid($value);

    if (!$this->isAllowNull()) {
      $value = $this->getValue();
      $count = !!count($value);

      if (!$count) {
        $this->errorMessages[] = Locale::t('Could not be empty');
        return false;
      }
      return true;
    }

    return $isValid;
  }

  public function getValue(): array
  {
    $value = parent::getValue();

    if (!$value) {
      return [];
    }

    if (is_array($value)) {
      return $value;
    }

    if (is_string($value)) {
      $value = json_decode($value, true);
    }

    return $value;
  }

  public function getRawValue(): array
  {
    $ids = [];
    foreach ($this->getValue() as $item) {
      if ($item instanceof ModelAbstract) {
        $ids[] = (string)$item->id;

      } elseif (is_string($item)) {
        $ids[] = $item;
      }
    }
    return $ids;
  }

  public function getCleanValue(): array
  {
    return $this->getRawValue();
  }
}
