<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

abstract class KeyValueAbstract extends ElementAbstract
{
  public string $keyLabel = 'Key';
  public string $valueLabel = 'Value';

  public string $keyPropertyName = 'key';
  public string $valuePropertyName = 'value';

  public function __construct(string $name, array $userOptions = [])
  {
    $this->keyLabel = Locale::t('Key');
    $this->valueLabel = Locale::t('Label');

    parent::__construct($name, $userOptions);
  }

  public function getKeyLabel(): string
  {
    return $this->keyLabel;
  }

  public function setKeyLabel(string $keyLabel): void
  {
    $this->keyLabel = $keyLabel;
  }

  public function getValueLabel(): string
  {
    return $this->valueLabel;
  }

  public function setValueLabel(string $valueLabel): void
  {
    $this->valueLabel = $valueLabel;
  }

  public function getKeyPropertyName(): string
  {
    return $this->keyPropertyName;
  }

  public function setKeyPropertyName(string $keyPropertyName): void
  {
    $this->keyPropertyName = $keyPropertyName;
  }

  public function getValuePropertyName(): string
  {
    return $this->valuePropertyName;
  }

  public function setValuePropertyName(string $valuePropertyName): void
  {
    $this->valuePropertyName = $valuePropertyName;
  }
}