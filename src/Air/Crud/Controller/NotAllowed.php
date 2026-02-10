<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

/**
 * @mod-manageable
 */
class NotAllowed extends AuthCrud
{
  public function index(): void
  {
    if ($this->getRequest()->isAjax()) {
      $this->getView()->setLayoutEnabled(false);
    } else {
      $this->getView()->setLayoutEnabled(true);
      $this->getView()->setLayoutTemplate('index');
    }

    $this->getView()->setAutoRender(true);
    $this->getView()->setPath(realpath(__DIR__ . '/../View'));
    $this->getView()->setScript('not-allowed');
  }
}
