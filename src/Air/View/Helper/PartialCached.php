<?php

declare(strict_types=1);

namespace Air\View\Helper;

use Air\Cache;

class PartialCached extends HelperAbstract
{
  public function call(string $template): string
  {
    return Cache::single([$template], function () use ($template) {
      return $this->getView()->render($template);
    });
  }
}