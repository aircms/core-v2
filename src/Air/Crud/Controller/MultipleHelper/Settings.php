<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

trait Settings
{
  protected function getQuickManage(): bool
  {
    return (bool)$this->getMods('quick-manage');
  }

  protected function getPrintable(): bool
  {
    return (bool)$this->getMods('printable');
  }

  protected function getHeaderButtons(): array
  {
    return $this->getMods('header-button');
  }

  protected function setAdminNavVisibility(bool $layoutVisibility): void
  {
    $this->getView()->assign('isSelectControl', !$layoutVisibility);
  }
}