<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;
use Air\Model\Exception\CallUndefinedMethod;
use Air\Model\Exception\ConfigWasNotProvided;
use Air\Model\ModelAbstract;

/**
 * @mod-manageable
 * @mod-sortable title
 */
class Language extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_LANGUAGES;
  }

  public static function isAvailable(): bool
  {
    return !!Nav::getSettingsItem(Nav::SETTINGS_LANGUAGES);
  }

  protected function getHeader(): array
  {
    return [
      Header::image(),
      Header::title(),
      Header::enabled(),
      Header::bool(by: 'isDefault')
    ];
  }

  /**
   * @param \Air\Crud\Model\Language $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      Input::checkbox('isDefault'),
      Input::text('title'),
      Input::storage('image', allowNull: true),
      Input::text('key', description: '2 symbols, lowercase'),
    ]);
  }

  /**
   * @param \Air\Crud\Model\Language $model
   * @param array $formData
   * @return void
   * @throws CallUndefinedMethod
   * @throws ConfigWasNotProvided
   */
  protected function didCreated(ModelAbstract $model, array $formData): void
  {
    parent::didCreated($model, $formData);

    $defaultLanguage = \Air\Crud\Model\Language::fetchOne([
      'isDefault' => true
    ]);

    foreach (\Air\Crud\Model\Phrase::fetchAll(['language' => $defaultLanguage]) as $phrase) {
      $copy = new \Air\Crud\Model\Phrase([
        'key' => $phrase->key,
        'value' => $phrase->value,
        'isEdited' => false,
        'language' => $model->id
      ]);
      $copy->save();
    }
  }
}
