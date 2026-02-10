<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Crud\Locale;

trait Select
{
  public function select(): void
  {
    $this->getView()->setVars([
      'icon' => $this->getIcon(),
      'title' => Locale::t($this->getTitle()),
      'filter' => $this->getFilterWithValues(),
      'header' => $this->getHeader(),
      'paginator' => $this->getPaginator(),
      'controller' => $this->getRouter()->getController(),
      'controls' => $this->getControls(),
      'isSelectControl' => true,
    ]);
    $this->getView()->setScript('table/index');
  }
}