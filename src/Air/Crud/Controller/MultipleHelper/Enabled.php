<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Model\ModelAbstract;

trait Enabled
{
  public function setEnabled(string $id, bool $enabled): void
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    $record = $modelClassName::fetchOne(['id' => $id]);

    if ($record->getMeta()->hasProperty('enabled')) {
      $record->enabled = $enabled;
      $record->save();

      if ($record->enabled) {
        $this->didEnabled($record);
      } else {
        $this->didDisable($record);
      }
    }
  }
}