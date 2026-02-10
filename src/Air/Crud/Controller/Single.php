<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Model\ModelAbstract;

abstract class Single extends Multiple
{
  public function index()
  {
    $this->getRequest()->setGetParam('quick-save', true, true);
    $this->getView()->assign('isSingleControl', true);

    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    return parent::manage($modelClassName::fetchObject()->id);
  }

  public function localizeCopy(\Air\Crud\Model\Language $language): string
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    $expectedCopy = $modelClassName::fetchOne([
      'language' => $language
    ]);

    if ($expectedCopy) {
      return $expectedCopy->id;
    }

    $defaultLanguage = \Air\Crud\Model\Language::one([
      'isDefault' => true
    ]);

    $modelDefaultLanguage = $modelClassName::fetchObject([
      'language' => $defaultLanguage
    ]);

    $this->localizedCopy($modelDefaultLanguage->id, $language);

    $copy = $modelClassName::fetchOne([
      'language' => $language
    ]);

    return $copy->id;
  }
}
