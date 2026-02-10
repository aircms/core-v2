<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;

class OpenAi extends Single
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_OPENAI;
  }

  /**
   * @param \Air\Crud\Model\OpenAi $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      Input::text('key', allowNull: true),
      Input::text('model', allowNull: true),
    ]);
  }
}
