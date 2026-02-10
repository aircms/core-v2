<?php

declare(strict_types=1);

namespace Air\Model\Meta\Exception;

use Exception;
use Air\Model\ModelAbstract;

class PropertyIsSetIncorrectly extends Exception
{
  public function __construct(ModelAbstract $model, string $line)
  {
    parent::__construct("Line: '" . $line . "', model: " . var_export($model, true));
  }
}