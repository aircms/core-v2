<?php

namespace Air\Core\Exception;

use Exception;

class Stop extends Exception
{
  public function __construct()
  {
    parent::__construct();
  }
}