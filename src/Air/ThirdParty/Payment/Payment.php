<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

abstract class Payment
{
  public function __construct(
    protected array $settings
  )
  {
  }

  abstract public function setSandboxEnabled(
    bool $sandboxEnabled
  ): void;

  abstract public function isSandboxEnabled(): bool;

  abstract public function create(
    string $orderId,
    float  $amount,
    string $description,
    string $redirect,
    string $callback
  ): Invoice;

  abstract public function validate(
    array $response
  ): false|Status;

  abstract public function status(
    Invoice $invoice
  ): Status;
}