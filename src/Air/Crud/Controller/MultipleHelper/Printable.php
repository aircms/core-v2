<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

trait Printable
{
  protected function getPrintableTitle(): string
  {
    return $this->getTitle();
  }

  protected function getPrintableHeader(): ?array
  {
    return $this->getHeader();
  }

  public function print(): void
  {
    $this->getView()->setVars([
      'title' => $this->getPrintableTitle(),
      'header' => $this->getPrintableHeader(),
      'paginator' => $this->getPaginator(),
      'isSelectControl' => true,
    ]);
    $this->getView()->setScript('table/printable');
  }
}