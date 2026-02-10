<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Model\ModelAbstract;

class TreeModel extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/tree-model';
  public string $model = '';
  public string $field = 'title';

  public function getField(): string
  {
    return $this->field;
  }

  public function setField(string $field): void
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

  public static function getModelChain(ModelAbstract $model, array $ids = []): array
  {
    $ids[] = (string)$model->id;

    if ($model->getMeta()->hasProperty('parent') && $model->parent && $model->parent instanceof ModelAbstract) {
      return self::getModelChain($model->parent, $ids);
    }

    return $ids;
  }

  public function getValue(): array
  {
    $value = parent::getValue();

    if (!$value) {
      return [];
    }

    if (is_string($value)) {
      $value = self::getModelChain($this->getModel()::fetchOne(['id' => $value]));
    }

    return $value;
  }
}
