<?php

namespace Air;

use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(...$args): void
{
  echo "<pre>";
  var_dump(...$args);
  echo "</pre>";
  die();
}