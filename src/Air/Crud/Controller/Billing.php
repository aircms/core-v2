<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Nav\NavController;
use Air\Crud\Nav\Nav;
use Air\Form\Form;
use Air\Form\Input;

class Billing extends Single
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_BILLING;
  }

  /**
   * @param \Air\Crud\Model\Billing $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return new Form(['data' => $model], [
      'LiqPay' => [
        Input::checkbox('liqPayEnabled'),
        Input::text('liqPayPublicKey', allowNull: true),
        Input::text('liqPayPrivateKey', allowNull: true),

        Input::checkbox('liqPaySandboxEnabled'),
        Input::text('liqPaySandboxPublicKey', allowNull: true),
        Input::text('liqPaySandboxPrivateKey', allowNull: true),
      ],
      'MonoPay' => [
        Input::checkbox('monoPayEnabled'),
        Input::text('monoPayKey', allowNull: true),

        Input::checkbox('monoPaySandboxEnabled'),
        Input::text('monoPaySandboxKey', allowNull: true),
      ]
    ]);
  }
}
