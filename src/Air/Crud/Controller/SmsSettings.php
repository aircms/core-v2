<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;

class SmsSettings extends Single
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_SMS_SETTINGS;
  }

  /**
   * @param \Air\Crud\Model\SmsSettings $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      'General' => [
        Input::checkbox('smsQueueEnabled'),
        Input::select('gateway', options: array_filter([
          \Air\Crud\Model\SmsSettings::GATEWAY_SMSTO,
          \Air\Crud\Model\SmsSettings::GATEWAY_GATEWAYAPI,
        ]))
      ],
      'SMSto' => [
        Input::text('smstoApiKey', allowNull: true),
        Input::text('smstoSenderId', allowNull: true),
      ],
      'Gateway API' => [
        Input::text('gatewayapiApiKey', allowNull: true),
        Input::text('gatewayapiSender', allowNull: true),
      ],
    ]);
  }
}