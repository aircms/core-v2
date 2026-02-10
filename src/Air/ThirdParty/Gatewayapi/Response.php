<?php

declare(strict_types=1);

namespace Air\ThirdParty\Gatewayapi;

use Air\Type\TypeAbstract;

class Response extends TypeAbstract
{
  public array $ids = [];
  public array $usage = [];
}