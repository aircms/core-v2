<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Type\TypeAbstract;

class Product extends TypeAbstract
{
  const string DISCOUNT_TYPE_DISCOUNT = 'DISCOUNT';
  const string DISCOUNT_TYPE_EXTRA_CHARGE = 'EXTRA_CHARGE';

  const string DISCOUNT_MODE_PERCENT = 'PERCENT';
  const string DISCOUNT_MODE_VALUE = 'VALUE';

  public ?string $name = null;
  public ?int $qty = null;
  public ?int $sum = null;
  public ?string $code = null;
  public ?string $image = null;
  public array $discounts = [];

  public function addDiscount(
    string $type,
    string $mode,
    float  $value
  ): void
  {
    $this->discounts = $this->discounts ?? [];
    $this->discounts[] = [
      'type' => $type,
      'mode' => $mode,
      'value' => $value
    ];
  }
}