<?php

declare(strict_types=1);

namespace Air\Filter;

abstract class FilterAbstract
{
  public function __construct(array $options = [])
  {
    foreach ($options as $name => $value) {

      if (is_callable([$this, 'set' . ucfirst($name)])) {
        call_user_func_array([$this, 'set' . ucfirst($name)], [$value]);
      }
    }
  }

  public static function clean(mixed $value, array $options = []): mixed
  {
    $filter = new static($options);
    return $filter->filter($value);
  }

  public abstract function filter($value);
}