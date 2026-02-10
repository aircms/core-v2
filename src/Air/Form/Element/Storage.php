<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Core\Front;
use Air\Crud\Locale;

class Storage extends ElementAbstract
{
  public bool $multiple = false;
  public ?string $elementTemplate = 'form/element/storage';
  public array $storageSettings;

  public function isMultiple(): bool
  {
    return $this->multiple;
  }

  public function setMultiple(bool $multiple): void
  {
    $this->multiple = $multiple;
  }

  public function getStorageSettings(): array
  {
    return $this->storageSettings;
  }

  public function setStorageSettings(array $storageSettings): void
  {
    $this->storageSettings = $storageSettings;
  }

  public function init(): void
  {
    parent::init();
    $this->storageSettings = Front::getInstance()->getConfig()['air']['storage'];
  }

  public function isValid($value): bool
  {
    $isValid = parent::isValid($value);

    if (!$isValid) {
      $this->errorMessages = [Locale::t('Could not be empty')];
      return false;
    }

    if (is_string($value)) {
      $value = json_decode($value, true);
    }

    if ((!is_array($value) || !count($value)) && !$this->isAllowNull()) {
      $this->errorMessages = [Locale::t('Could not be empty')];
      return false;
    }

    return true;
  }

  public function getValue(): mixed
  {
    $value = parent::getValue();

    if (is_string($value)) {
      $value = json_decode($value, true);
    }

    if (!$value) {
      return $this->isMultiple() ? [] : null;
    }

    return $value;
  }
}