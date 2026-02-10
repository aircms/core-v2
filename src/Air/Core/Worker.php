<?php

declare(strict_types=1);

namespace Air\Core;

use Exception;

abstract class Worker extends Controller
{
  public function __call(string $name, array $arguments)
  {
    $arguments = $this->getParams();

    $this->didStarted($name, $arguments);

    if (!method_exists($this, $name)) {
      throw new Exception("Method {$name} was not found");
    }
    while (true) {
      $r = call_user_func_array([$this, $name], $arguments);
      sleep(Front::getInstance()->getConfig()['air']['worker']['sleep'] ?? 1);
      if ($r === false) {
        break;
      }
    }
    $this->didFinished($name, $arguments);
  }

  public function didStarted(string $method, array $params = [])
  {
  }

  public function didFinished(string $method, array $params = [])
  {
  }
}
