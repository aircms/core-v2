<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Crud\Model\Billing;
use InvalidArgumentException;

class PaymentFactory
{
  const string LiqPay = 'liqPay';
  const string MonoPay = 'monoPay';

  public static function payment(string $gateway): Payment
  {
    return match ($gateway) {
      self::LiqPay => self::liqPay(),
      self::MonoPay => self::monoPay(),
      default => throw new InvalidArgumentException("Unknown gateway: " . $gateway),
    };
  }

  public static function monoPay(): MonoPay
  {
    $settings = Billing::one();
    return new MonoPay([
      'key' => $settings->monoPayKey,
      'sandboxEnabled' => $settings->monoPaySandboxEnabled,
      'sandboxKey' => $settings->monoPaySandboxKey
    ]);
  }

  public static function liqPay(): LiqPay
  {
    $billing = Billing::one();

    return new LiqPay([
      'publicKey' => $billing->liqPayPublicKey,
      'privateKey' => $billing->liqPayPrivateKey,
      'sandboxEnabled' => $billing->liqPaySandboxEnabled,
      'sandboxPublicKey' => $billing->liqPaySandboxPublicKey,
      'sandboxPrivateKey' => $billing->liqPaySandboxPrivateKey,
    ]);
  }
}