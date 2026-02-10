<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

class Cache extends AuthCrud
{
  public function index(): void
  {
    $this->getView()->setLayoutEnabled(false);
    $this->getView()->setAutoRender(false);

    \Air\Cache::remove();
  }
}
