<?php

declare(strict_types=1);

namespace Air\Model\Meta\Exception;

use Exception;
use Air\Model\ModelAbstract;

class CollectionCantBeWithoutPrimary extends Exception
{
  public function __construct(ModelAbstract $model)
  {
    parent::__construct(var_export($model, true));
  }
}