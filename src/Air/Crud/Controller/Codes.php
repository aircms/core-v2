<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;
use Air\Map;

class Codes extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_CODES;
  }

  public static function render(): string
  {
    return implode("\n", Map::multiple(\Air\Crud\Model\Codes::all(), 'description'));
  }

  /**
   * @param \Air\Crud\Model\Codes $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      Input::textarea('description', description: 'Code will be used in HEAD section')
    ]);
  }
}
