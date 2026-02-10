<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Http\Request;
use Exception;

class LiqPay extends Payment
{
  private string $publicKey;
  private string $privateKey;

  private bool $sandboxEnabled;
  private string $sandboxPublicKey;
  private string $sandboxPrivateKey;

  public function __construct(array $settings)
  {
    parent::__construct($settings);

    $this->publicKey = $this->settings['publicKey'];
    $this->privateKey = $this->settings['privateKey'];

    $this->sandboxEnabled = $this->settings['sandboxEnabled'];
    $this->sandboxPublicKey = $this->settings['sandboxPublicKey'];
    $this->sandboxPrivateKey = $this->settings['sandboxPrivateKey'];
  }

  public function isSandboxEnabled(): bool
  {
    return $this->sandboxEnabled;
  }

  public function setSandboxEnabled(bool $sandboxEnabled): void
  {
    $this->sandboxEnabled = $sandboxEnabled;
  }

  public function getPublicKey(): string
  {
    return $this->isSandboxEnabled() ? $this->sandboxPublicKey : $this->publicKey;
  }

  public function getPrivateKey(): string
  {
    return $this->isSandboxEnabled() ? $this->sandboxPrivateKey : $this->privateKey;
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
      'version' => 3,
      'action' => 'pay',
      'currency' => 'UAH',
      'public_key' => $this->getPublicKey(),
      'amount' => $amount,
      'description' => $description,
      'result_url' => $redirect,
      'server_url' => $callback,
      'order_id' => $orderId,
    ];

    return new Invoice([
      'orderId' => $orderId,
      'invoiceId' => $orderId,
      'url' => "https://www.liqpay.ua/api/3/checkout?" . http_build_query($this->sign($request)),
      'isSandbox' => $this->isSandboxEnabled(),
    ]);
  }

  public function validate(array $response): false|Status
  {
    if (!isset($response['signature']) || !isset($response['data'])) {
      return false;
    }

    $signature = $response['signature'];
    $response = $response['data'];

    $requestedSignature = base64_encode(sha1($this->getPrivateKey() . $response . $this->getPrivateKey(), true));

    if ($signature !== $requestedSignature) {
      return false;
    }

    $response = json_decode(base64_decode($response), true);

    return $this->dataToStatus($response);
  }

  public function status(Invoice|string $invoice): Status
  {
    $orderId = is_string($invoice) ? $invoice : $invoice->getInvoiceId();

    $request = [
      'version' => 3,
      'action' => 'status',
      'public_key' => $this->getPublicKey(),
      'order_id' => $orderId
    ];

    $response = Request::post(
      url: "https://www.liqpay.ua/api/request",
      body: $this->sign($request)
    );

    if ($response->body['result'] === 'ok') {
      return $this->dataToStatus($response->body);
    }

    throw new Exception('LiqPay status error: [order: ' . $orderId . ', err_code: ' . $response->body['err_code']);
  }

  protected function sign(array $request): array
  {
    $request = base64_encode(json_encode($request));
    $signature = base64_encode(sha1($this->getPrivateKey() . $request . $this->getPrivateKey(), true));

    return [
      'signature' => $signature,
      'data' => $request,
    ];
  }

  protected function dataToStatus(array $data): Status
  {
    return new Status([
      'invoiceId' => $data['order_id'],
      'orderId' => $data['order_id'],
      'status' => $data['status'],
      'isPaid' => $data['status'] === 'success',
      'isSandbox' => $this->isSandboxEnabled(),
      'raw' => $data,
    ]);
  }
}