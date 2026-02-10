<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Filter;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Model\ModelAbstract;

/**
 * @mod-quick-manage
 * @mod-items-per-page 100
 */
class Phrase extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_PHRASES;
  }

  protected function getHeader(): array
  {
    return [
      Header::text(by: 'key'),
      Header::source('Value', function (\Air\Crud\Model\Phrase $phrase) {
        return div(
          class: 'form-outline bg-body-tertiary w-100',
          content: text(
            value: $phrase->value,
            class: 'form-control form-control-lg',
            attributes: ['style' => 'width: 600px;'],
            data: [
              'phrase-key' => $phrase->key,
              'phrase-language' => $phrase->language->id,
              'phrase-value' => $phrase->value
            ],
          ));
      }),
      Header::bool(by: 'isEdited'),
      Header::language()
    ];
  }

  protected function getFilter(): array
  {
    return [
      Filter::search(['key', 'value']),
      Filter::bool('Edited', 'isEdited', 'Edited', 'Not edited'),
      Filter::language()
    ];
  }

  protected function getControls(): array
  {
    return [];
  }

  protected function didSaved(ModelAbstract $model, array $formData, ModelAbstract $oldModel): void
  {
    /** @var \Air\Crud\Model\Phrase $model */

    $model->isEdited = true;
    $model->save();
  }

  protected function getHeaderButtons(): array
  {
    return [
      ...parent::getHeaderButtons(),
      ...[[
        'title' => 'Save all',
        'url' => '" data-save-phrases-url="/' . $this->getEntity() . '/save" data-save-phrases="true',
      ]]
    ];
  }

  public function save(): void
  {
    $this->getView()->setLayoutEnabled(false);
    $this->getView()->setAutoRender(false);

    foreach ($this->getParam('phrases') ?? [] as $phrase) {
      $_phrase = \Air\Crud\Model\Phrase::fetchOne([
        'language' => $phrase['language'],
        'key' => $phrase['key']
      ]);

      $_phrase->value = $phrase['value'];
      $_phrase->isEdited = true;
      $_phrase->save();
    }
  }
}
