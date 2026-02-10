<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Type\TypeAbstract;

class Status extends TypeAbstract
{
  public ?string $invoiceId = null;
  public ?string $orderId = null;
  public ?string $status = null;
  public ?bool $isPaid = false;
  public ?bool $isSandbox = false;
  public ?array $raw = [];

  public function getInvoiceId(): ?string
  {
    return $this->invoiceId;
  }

  public function getOrderId(): ?string
  {
    return $this->orderId;
  }

  public function getStatus(): ?string
  {
    return $this->status;
  }

  public function isPaid(): ?bool
  {
    return $this->isPaid;
  }

  public function isSandbox(): ?bool
  {
    return $this->isSandbox;
  }

  public function getRaw(): ?array
  {
    return $this->raw;
  }
}