<?php

declare(strict_types=1);

namespace Air\Model\Driver\Mysql\Exception;

use Exception;
use Throwable;

class FailedStatement extends Exception
{
  public function __construct(string $sql, array $params = [], ?Throwable $exception = null)
  {
    $message = $exception?->getMessage();
    $params = $params ? var_export($params, true) : null;

    $message = implode('. ', array_filter([$message, $sql, $params]));
    parent::__construct($message, 500);
  }
}