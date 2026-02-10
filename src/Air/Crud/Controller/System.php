<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Throwable;

class System extends Multiple
{
  public function index(): void
  {
    try {
      $this->getView()->setVars([
        'disk' => \Air\System\System::disk(),
        'memory' => \Air\System\System::memory(),
        'uptime' => \Air\System\System::uptime(true),
        'version' => \Air\System\System::version(),
        'cpuLoadAverage' => \Air\System\System::cpuLoadAverage(),
        'cpuCoreCount' => \Air\System\System::cpuCoreCount(),
        'cpuName' => \Air\System\System::cpuName(),
      ]);
    } catch (Throwable) {
    }

    $this->getView()->setScript('system');
  }
}
