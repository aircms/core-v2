<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;

class SmsTemplate extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_SMS_TEMPLATES;
  }

  protected function getHeader(): array
  {
    $headers = [
      Header::text(by: 'url'),
      Header::longtext(by: 'message'),
      Header::enabled(),
    ];

    if (!!Nav::getSettingsItem(Nav::SETTINGS_LANGUAGES)) {
      $headers[] = Header::language();
    }
    return $headers;
  }

  /**
   * @param \Air\Crud\Model\SmsTemplate $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      Input::textarea('message'),
    ]);
  }
}