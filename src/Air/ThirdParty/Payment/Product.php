<?php

declare(strict_types=1);

namespace Air\ThirdParty\Payment;

use Air\Type\File;
use Air\Type\TypeAbstract;

class Product extends TypeAbstract
{
  public ?string $name = null;
  public ?int $qty = null;
  public ?int $sum = null;
  public ?string $code = null;
  public ?File $image = null;
}