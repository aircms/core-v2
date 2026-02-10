<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Checkbox extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/checkbox';
  public array $deactivate = [];
  public bool $deactivateWhen = false;

  public function init(): void
  {
    parent::init();
    $this->setAllowNull(true);
  }

  public function getValue(): bool
  {
    return (bool)parent::getValue();
  }

  public function getCleanValue(): bool
  {
    return (bool)parent::getCleanValue();
  }

  public function getDeactivate(): array
  {
    return $this->deactivate;
  }

  public function setDeactivate(array $deactivate): void
  {
    $this->deactivate = $deactivate;
  }

  public function getDeactivateWhen(): bool
  {
    return $this->deactivateWhen;
  }

  public function setDeactivateWhen(bool $deactivateWhen): void
  {
    $this->deactivateWhen = $deactivateWhen;
  }
}
