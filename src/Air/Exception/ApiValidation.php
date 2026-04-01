<?php

declare(strict_types=1);

namespace Air\Exception;

use Exception;

class ApiValidation extends Exception
{
  private array $data = [];

  public function __construct(int $code = 400, ?array $data = [])
  {
    parent::__construct('', $code);
    $this->data = $data;
  }

  public function getData(): array
  {
    return $this->data;
  }
}