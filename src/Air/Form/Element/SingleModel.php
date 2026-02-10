<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Model\ModelAbstract;

class SingleModel extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/single-model';
  public string $model = '';
  public mixed $field = 'title';
  public ?string $parent = null;

  public function getField(): string|callable
  {
    return $this->field;
  }

  public function setField(string|callable $field): void
  {
    $this->field = $field;
  }

  public function getModel(): ModelAbstract
  {
    return new $this->model;
  }

  public function setModel(string $model): void
  {
    $this->model = $model;
  }

  public function getParent(): ?string
  {
    return $this->parent;
  }

  public function setParent(?string $parent): void
  {
    $this->parent = $parent;
  }

  public function getValue(): ?ModelAbstract
  {
    $value = parent::getValue();

    if (!$value) {
      return null;
    }

    if ($value instanceof ModelAbstract) {
      return $value;
    }

    /** @var ModelAbstract $model */
    $model = $this->model;

    return $model::fetchObject(['id' => $value]);
  }

  public function getCleanValue(): mixed
  {
    $value = parent::getCleanValue();

    if (!$value) {
      return null;
    }

    if ($value instanceof ModelAbstract) {
      return $value->id;
    }

    return $value;
  }
}
