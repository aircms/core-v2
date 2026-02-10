<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Http\Request;
use Air\Log;
use Exception;
use InvalidArgumentException;
use Throwable;

class MonoPay extends Payment
{
  private string $key;

  private bool $sandboxEnabled;
  private string $sandboxKey;

  public function __construct(array $settings)
  {
    parent::__construct($settings);

    if (!isset($this->settings['key'])) {
      throw new InvalidArgumentException("MonoPay settings are missing");
    }

    $this->key = $this->settings['key'];

    $this->sandboxEnabled = $this->settings['sandboxEnabled'];
    $this->sandboxKey = $this->settings['sandboxKey'];
  }

  public function isSandboxEnabled(): bool
  {
    return $this->sandboxEnabled;
  }

  public function setSandboxEnabled(bool $sandboxEnabled): void
  {
    $this->sandboxEnabled = $sandboxEnabled;
  }

  public function create(
    string $orderId,
    float  $amount,
    string $description,
    string $redirect,
    string $callback,
  ): Invoice
  {
    $request = [
      'amount' => ceil($amount * 100),
      'redirectUrl' => $redirect,
      'webHookUrl' => $callback,
      'merchantPaymInfo' => [
        'reference' => $orderId,
        'destination' => $description
      ]
    ];

    $response = Request::post(
      url: 'https://api.monobank.ua/api/merchant/invoice/create',
      headers: ['X-Token' => $this->getKey()],
      type: Request::CONTENT_TYPE_JSON,
      body: $request,
    );

    if (!$response->isOk()) {
      throw new InvalidArgumentException("MonoPay create invoice error:" . var_export($response->body, true));
    }

    return new Invoice([
      'orderId' => $orderId,
      'invoiceId' => $response->body['invoiceId'],
      'url' => $response->body['pageUrl'],
      'isSandbox' => $this->isSandboxEnabled(),
    ]);
  }

  public function validate(array $response): false|Status
  {
    try {
      return $this->dataToStatus($response);
    } catch (Throwable) {
      return false;
    }
  }

  public function status(Invoice|string $invoice): Status
  {
    $invoice = is_string($invoice) ? $invoice : $invoice->getInvoiceId();

    $response = Request::fetch(
      url: "https://api.monobank.ua/api/merchant/invoice/status",
      headers: ['X-Token' => $this->getKey()],
      get: ['invoiceId' => $invoice]
    );

    if ($response->isOk()) {
      return $this->dataToStatus($response->body);
    }

    throw new Exception('MonoPay status error: [order: ' . $invoice . ', errCode: ' . $response->body['errCode']);
  }

  protected function getKey(): string
  {
    return $this->isSandboxEnabled() ? $this->sandboxKey : $this->key;
  }

  protected function dataToStatus(array $data): Status
  {
    return new Status([
      'invoiceId' => $data['invoiceId'],
      'orderId' => $data['reference'],
      'status' => $data['status'],
      'isPaid' => $data['status'] === 'success',
      'isSandbox' => $this->isSandboxEnabled(),
      'raw' => $data
    ]);
  }
}