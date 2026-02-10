<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Form\Input;

class Social extends MultipleGroup
{
  public function __construct(string $name, array $userOptions = [])
  {
    $userOptions['elements'] = [
      [
        Input::select('type', options: array_values(\Air\Type\Social::getTypes())),
        Input::text('link'),
      ]
    ];

    parent::__construct($name, $userOptions);
  }
}