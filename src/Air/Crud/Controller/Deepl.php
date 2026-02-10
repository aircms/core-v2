<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;

class Deepl extends Single
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_DEEPL;
  }

  /**
   * @param \Air\Crud\Model\Deepl $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      Input::text('key', allowNull: true),
      Input::checkbox('isFree')
    ]);
  }

  public function phrase(): array
  {
    $language = \Air\Crud\Model\Language::fetchOne([
      'id' => $this->getParam('language')
    ]);

    return [
      'translation' => \Air\ThirdParty\Deepl::instance()
        ->translate(
          [$this->getParam('phrase')],
          $language
        )[0]
    ];
  }
}
