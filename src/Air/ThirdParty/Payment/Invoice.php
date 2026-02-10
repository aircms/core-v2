<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Type\TypeAbstract;

class Invoice extends TypeAbstract
{
  public ?string $orderId = null;
  public ?string $invoiceId = null;
  public ?string $url = null;
  public ?bool $isSandbox = false;

  public function getOrderId(): ?string
  {
    return $this->orderId;
  }

  public function getInvoiceId(): ?string
  {
    return $this->invoiceId;
  }

  public function getUrl(): ?string
  {
    return $this->url;
  }

  public function isSandbox(): ?bool
  {
    return $this->isSandbox;
  }
}