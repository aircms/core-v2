<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Crud\Model\Language;
use Air\Model\ModelAbstract;
use Throwable;

trait Copy
{
  public function copy(string $id): void
  {
    $this->getView()->setLayoutEnabled(false);
    $this->getView()->setAutoRender(false);

    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();
    $record = $modelClassName::fetchOne(['id' => $id]);

    $data = $record->getData();
    unset($data['id']);

    if ($record->getMeta()->hasProperty('enabled')) {
      $data['enabled'] = false;
    }

    $newRecord = new $modelClassName();
    $newRecord->populate($data);
    $newRecord->save();

    $this->didCopied($record, $newRecord);
  }

  public function localizedCopy(string $id, Language $language): void
  {
    try {
      $this->getView()->setLayoutEnabled(false);
      $this->getView()->setAutoRender(false);

      /** @var ModelAbstract $modelClassName */
      $modelClassName = $this->getModelClassName();
      $record = $modelClassName::fetchOne(['id' => $id]);

      $data = $record->getData();
      unset($data['id']);

      $copy = new $modelClassName();
      $copy->populate($data);

      if ($record->getMeta()->hasProperty('language')) {
        $copy->language = $language;
      }

      foreach ($copy->getMeta()->getProperties() as $property) {
        if ($property->getName() !== 'language') {

          if ($property->isModel()) {

            /** @var ModelAbstract $propertyClassName */
            $propertyClassName = $property->getRawType();

            if ((new $propertyClassName())->getMeta()->hasProperty('language')) {

              if (!empty($copy->{$property->getName()})) {

                if ($property->isMultiple()) {
                  $ids = [];
                  foreach ($copy->{$property->getName()} as $propertyModel) {
                    $propertyModel = $propertyClassName::fetchOne([
                      'url' => $propertyModel->url,
                      'language' => $language
                    ]);
                    if ($propertyModel) {
                      $ids[] = $propertyModel->id;
                    }
                  }
                  $copy->populate([$property->getName() => $ids]);
                } else {
                  $propertyModel = $propertyClassName::fetchOne([
                    'url' => $copy->{$property->getName()}->url,
                    'language' => $language
                  ]);
                  $copy->{$property->getName()} = $propertyModel->id;
                }
              }
            }
          }
        }
      }
      $copy->save();
    } catch (Throwable) {
    }
  }
}