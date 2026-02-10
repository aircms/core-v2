<?php

declare(strict_types=1);

namespace Air\Core;

abstract class BootstrapAbstract
{
  private array $config = [];

  final public function getConfig(): array
  {
    return $this->config;
  }

  final public function setConfig(array $config): void
  {
    $this->config = $config;
  }
}