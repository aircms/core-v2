<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Crud\Locale;
use Air\Model\ModelAbstract;

trait View
{
  public function view(ModelAbstract $model): void
  {
    $form = $this->getForm($model);

    $form->setReturnUrl($this->getRouter()->assemble([
      'controller' => $this->getRouter()->getController(),
    ]));

    $this->getView()->setVars([
      'icon' => $this->getAdminMenuItem()['icon'] ?? null,
      'title' => Locale::t($this->getTitle()),
      'form' => $form,
      'mode' => 'manage',
      'isQuickManage' => true,
      'isSelectControl' => true,
    ]);

    $this->getView()->setScript('form/index');
  }

  public function data(string $id): array
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    $model = $modelClassName::fetchObject(['id' => $id]);

    $form = $this->getForm($model);
    $form->isValid($this->getParams());

    $formData = $form->getCleanValues();
    $model->populate($formData);

    $modelData = $model->getData();

    $data = [];
    foreach ($model->getMeta()->getProperties() as $property) {
      if ($property->isModel()) {
        $data[$property->getName()] = [];

        if ($property->isMultiple()) {

          /** @var ModelAbstract $item */
          foreach ($model->{$property->getName()} as $item) {
            $data[$property->getName()][] = $item->getData();
          }
        } else {
          /** @var ModelAbstract $item */
          $item = $model->{$property->getName()};
          $data[$property->getName()] = $item->getData();
        }

      } else {
        $data[$property->getName()] = $modelData[$property->getName()] ?? null;
      }
    }

    return $data;
  }
}