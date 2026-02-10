<?php

declare(strict_types=1);

namespace Air\ThirdParty\Smsto;

use Air\Type\TypeAbstract;

class Response extends TypeAbstract
{
  public string $message = '';
  public bool $succes = false;
  public string $messageId = '';
}