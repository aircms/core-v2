<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;

class EmailTemplate extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_EMAIL_TEMPLATES;
  }

  protected function getHeader(): array
  {
    $headers = [
      Header::text(by: 'url'),
      Header::text(by: 'subject'),
      Header::longtext(by: 'body'),
      Header::enabled(),
    ];
    if (Nav::getSettingsItem(Nav::SETTINGS_LANGUAGES)) {
      $headers[] = Header::language();
    }
    return $headers;
  }

  /**
   * @param \Air\Crud\Model\EmailTemplate $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      Input::text('url'),
      Input::text('subject'),
      Input::tiny('body'),
    ]);
  }
}