<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\AdminLog;
use Air\Crud\Controller\MultipleHelper\Copy;
use Air\Crud\Controller\MultipleHelper\Enabled;
use Air\Crud\Controller\MultipleHelper\Export;
use Air\Crud\Controller\MultipleHelper\Header;
use Air\Crud\Controller\MultipleHelper\Magic;
use Air\Crud\Controller\MultipleHelper\Manage;
use Air\Crud\Controller\MultipleHelper\ModelMeta;
use Air\Crud\Controller\MultipleHelper\Mods;
use Air\Crud\Controller\MultipleHelper\Position;
use Air\Crud\Controller\MultipleHelper\Printable;
use Air\Crud\Controller\MultipleHelper\Select;
use Air\Crud\Controller\MultipleHelper\Settings;
use Air\Crud\Controller\Many\Table;
use Air\Crud\Controller\MultipleHelper\View;
use Air\Model\ModelAbstract;

abstract class Many extends AuthCrud
{
  use Mods;
  use ModelMeta;
  use Table;
  use Position;
  use Header;
  use Settings;
  use AdminLog;

  use Export;
  use Copy;
  use Enabled;
  use Manage;
  use Select;
  use View;
  use Printable;
  use Magic;

  public function init(): void
  {
    parent::init();

    MultipleHelper\Accessor\Header::$entity = $this->getModelClassName();
    MultipleHelper\Accessor\Filter::$entity = $this->getModelClassName();

    $this->getView()->setLayoutEnabled(
      !$this->getRequest()->isAjax()
    );

    $this->getView()->setLayoutTemplate('index');
    $this->getView()->setAutoRender(true);

    $this->getView()->setPath(realpath(__DIR__ . '/../View'));
  }

  protected function didCreated(ModelAbstract $model, array $formData): void
  {
  }

  protected function didChanged(ModelAbstract $model, array $formData, ModelAbstract $oldModel): void
  {
  }

  protected function didSaved(ModelAbstract $model, array $formData, ModelAbstract $oldModel): void
  {
  }

  protected function didEnabled(ModelAbstract $model): void
  {
  }

  protected function didDisable(ModelAbstract $model): void
  {
  }

  protected function didCopied(ModelAbstract $oldRecord, ModelAbstract $newRecord): void
  {
  }
}
