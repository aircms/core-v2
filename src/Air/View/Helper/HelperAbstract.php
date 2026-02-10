<?php

declare(strict_types=1);

namespace Air\View\Helper;

use Air\View\View;

abstract class HelperAbstract
{
  protected ?View $_view = null;

  public function getView(): View
  {
    return $this->_view;
  }

  public function setView(View $view): void
  {
    $this->_view = $view;
  }
}