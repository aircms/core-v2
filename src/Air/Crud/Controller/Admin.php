<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Locale;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Filter\Lowercase;
use Air\Filter\Trim;
use Air\Form\Form;
use Air\Form\Input;
use MongoDB\BSON\ObjectId;

class Admin extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_ADMINISTRATORS;
  }

  protected function getHeader(): array
  {
    return [
      Header::text(by: 'login', size: Header::XL),
      Header::bool(by: 'isRoot'),
      Header::enabled(),
    ];
  }

  /**
   * @param \Air\Crud\Model\Admin $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Form::inputs($model, [
      'General' => [
        'Settings' => [
          Input::checkbox('enabled'),
          Input::checkbox('isRoot'),
        ],
        'Auth' => [
          Input::text(
            'login',
            filters: [Lowercase::class, Trim::class],
            validators: [function (string $login) use ($model): true|string {
              if ($model->id) {
                $exists = \Air\Crud\Model\Admin::count([
                  'login' => $login,
                  '_id' => ['$ne' => new ObjectId($model->id)]
                ]);
              } else {
                $exists = \Air\Crud\Model\Admin::count(['login' => $login]);
              }
              return $exists ? Locale::t('User with this login is already exists') : true;
            }]
          ),
          Input::password('password'),
        ],
        'Permissions' => [
          Input::permissions('permissions')
        ],
      ]
    ]);
  }
}
