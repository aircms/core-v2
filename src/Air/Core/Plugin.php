<?php

declare(strict_types=1);

namespace Air\Core;

class Plugin
{
  public function preRun(Request $request, Response $response, Router $router): void
  {
  }

  public function postRun(Request $request, Response $response, Router $router): void
  {
  }
}