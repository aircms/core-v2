<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

class MultipleGroup extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/multiple-group';
  public array $elements = [];
  public array $groups = [];
  public Group $group;
  public ?array $defaultValue = [];
  public bool $allowNullGroup = true;
  public bool $isFixed = false;

  /** @var ElementAbstract[][] */
  public ?array $elementsMap = null;

  public function getDefaultValue(): ?array
  {
    return $this->defaultValue;
  }

  public function setDefaultValue(?array $defaultValue): void
  {
    $this->defaultValue = $defaultValue;
  }

  public function isAllowNullGroup(): bool
  {
    return $this->allowNullGroup;
  }

  public function setAllowNullGroup(bool $allowNullGroup): void
  {
    $this->allowNullGroup = $allowNullGroup;
  }

  /**
   * @return ElementAbstract[]
   */
  public function getElements(): array
  {
    $cleanElements = [];
    foreach ($this->elements as $element) {
      $cleanElements[] = clone $element;
    }
    return $cleanElements;
  }

  public function getElementsMap(): ?array
  {
    if (!$this->elementsMap) {
      return null;
    }

    $elementsMap = [];

    foreach ($this->elementsMap as $rowIndex => $row) {
      foreach ($row as $name) {
        foreach ($this->getElements() as $element) {
          if ($name === $element->getName()) {
            $elementsMap[$rowIndex][] = $element;
          }
        }
      }
    }
    return $elementsMap;
  }

  public function setElementsMap(?array $elementsMap): void
  {
    $this->elementsMap = $elementsMap;
  }

  public function setElements(array $elements): void
  {
    if (is_array($elements[0])) {
      $plainElements = [];
      $this->elementsMap = [];
      foreach ($elements as $rowIndex => $map) {
        foreach ($map as $element) {
          $this->elementsMap[$rowIndex][] = $element->getName();
          $plainElements[] = $element;
        }
      }
      $this->elements = $plainElements;

    } else {
      $this->elements = $elements;
    }
  }

  public function getGroups(): array
  {
    return $this->groups;
  }

  public function setGroups(array $groups): void
  {
    $this->groups = $groups;
  }

  public function getGroup(): Group
  {
    return $this->group;
  }

  public function setGroup(Group $group): void
  {
    $this->group = $group;
  }

  public function isFixed(): bool
  {
    return $this->isFixed;
  }

  public function setIsFixed(bool $isFixed): void
  {
    $this->isFixed = $isFixed;
  }

  public function init(): void
  {
    parent::init();

    $this->initGroups($this->value);

    $name = $this->getName() . '[{{groupId}}]';

    $this->group = new Group($name, [
      'elements' => $this->getElementsMap() ?? $this->getElements(),
      'allowNull' => $this->isAllowNullGroup(),
      'containerTemplate' => 'form/element/multiple-group/group-template',
      'value' => $this->defaultValue,
    ]);

    $this->group->init();
  }

  public function isValid($value): bool
  {
    $isValid = true;

    if ((!$value || !count($value)) && $this->isAllowNull()) {
      $this->value = [];
      return true;
    }

    $this->value = array_values(($value ?? []));

    $this->initGroups($this->value);
    $groups = $this->getGroups();

    if (!count($groups) && !$this->isAllowNull()) {
      $this->errorMessages = [Locale::t('Could not be empty')];
      return false;
    }

    foreach ($groups as $index => $group) {
      if (!$group->isValid($this->value[$index])) {
        $isValid = false;
      }
    }

    if (!$isValid) {
      $this->errorMessages = [Locale::t('Could not be empty')];
    }

    return $isValid;
  }

  public function initGroups(?array $value = []): void
  {
    $this->groups = [];

    foreach (($value ?? []) as $groupValue) {
      $group = new Group($this->getName() . '[' . uniqid() . ']', [
        'label' => $this->getLabel(),
        'elements' => $this->getElementsMap() ?? $this->getElements(),
        'allowNull' => $this->isAllowNullGroup(),
        'containerTemplate' => 'form/element/multiple-group/group-template',
        'value' => $groupValue,
      ]);
      $group->init();
      $this->groups[] = $group;
    }
  }

  public function getValue(): array
  {
    $value = parent::getValue();

    if (!$value) {
      return [];
    }

    $this->initGroups($value);

    $value = [];

    foreach ($this->getGroups() as $group) {
      $value[] = $group->getValue();
    }

    return $value;
  }

  public function getCleanValue(): mixed
  {
    $value = parent::getCleanValue();

    if (!$value) {
      return [];
    }

    $this->initGroups($value);

    $value = [];

    foreach ($this->getGroups() as $group) {
      $value[] = $group->getCleanValue();
    }

    return $value;
  }
}