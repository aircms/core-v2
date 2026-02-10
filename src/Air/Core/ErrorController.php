<?php

declare(strict_types=1);

namespace Air\Core;

use Error;
use Exception;
use Throwable;

class ErrorController extends Controller
{
  private Error|Exception|null $exception = null;
  private bool $exceptionEnabled = true;

  public function isExceptionEnabled(): bool
  {
    return $this->exceptionEnabled;
  }

  public function setExceptionEnabled(bool $exceptionEnabled): void
  {
    $this->exceptionEnabled = $exceptionEnabled;
  }

  public function init(): void
  {
    parent::init();

    try {
      $this->getResponse()->setStatusCode(
        $this->getException()->getCode()
      );

      $this->getResponse()->setStatusMessage(
        $this->getException()->getMessage()
      );
    } catch (Throwable) {}
  }

  public function getException(): Exception|Error|null
  {
    return $this->exception;
  }

  public function setException(Error|Exception $exception): void
  {
    $this->exception = $exception;
  }
}
