<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Group extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/group';
  public array $elements = [];
  public array $originalElementNames = [];

  /** @var string[][] */
  public ?array $elementsMap = null;

  public function __construct(string $name, array $userOptions = [])
  {
    $this->elementsMap = [];
    $elements = [];
    foreach ($userOptions['elements'] as $index => $userElements) {
      $this->elementsMap[$index] = [];

      if (is_array($userElements)) {
        /** @var ElementAbstract $element */
        foreach ($userElements as $element) {
          $this->elementsMap[$index][] = $element->getName();
          $elements[] = $element;
        }
      } else {
        $this->elementsMap[$index][] = $userElements->getName();
        $elements[] = $userElements;
      }
    }
    $userOptions['elements'] = $elements;

    parent::__construct($name, $userOptions);
  }

  public function init(): void
  {
    parent::init();

    foreach ($this->getElements() as $index => $element) {
      $element->setValue(((array)$this->value)[$this->originalElementNames[$index]] ?? null);

      $element->setContainerTemplate('form/element/group/container');
      $element->setAllowNull($this->isAllowNull());
    }

    $this->updateNamesWith($this->getName());

    if ($this->elementsMap) {
      $elementIndex = 0;
      foreach ($this->elementsMap as $rowIndex => $map) {
        foreach ($map as $colIndex => $element) {
          $name = $this->getName() . '[' . $this->originalElementNames[$elementIndex] . ']';
          $this->elementsMap[$rowIndex][$colIndex] = $name;
          $elementIndex++;
        }
      }
    }
  }

  public function updateNamesWith(string $parentName): void
  {
    foreach ($this->getElements() as $index => $element) {
      $name = $parentName . '[' . $this->originalElementNames[$index] . ']';

      if ($element instanceof $this) {
        $element->updateNamesWith($name);

      } else {
        $element->setName($name);
      }

      $element->init();
    }
  }

  public function getElementsMap(): ?array
  {
    return $this->elementsMap;
  }

  public function setElementsMap(?array $elementsMap): void
  {
    $this->elementsMap = $elementsMap;
  }

  /**
   * @return ElementAbstract[]
   */
  public function getElements(): array
  {
    return $this->elements;
  }

  public function setElements(array $elements): void
  {
    foreach ($elements as $element) {
      $this->originalElementNames[] = $element->getName();
    }
    $this->elements = $elements;
  }

  public function isValid($value): bool
  {
    /** @var ElementAbstract $element */

    $isValid = true;

    foreach ($this->getElements() as $index => $element) {
      $_value = $value[$this->originalElementNames[$index]] ?? null;
      if (!$element->isValid($_value)) {
        $this->errorMessages[$this->originalElementNames[$index]] = $element->getErrorMessages();
        $isValid = false;
      }
    }

    return $isValid;
  }

  public function hasError(): bool
  {
    $hasError = false;

    foreach ($this->getElements() as $element) {
      if ($element->hasError()) {
        $hasError = true;
      }
    }

    return $hasError;
  }

  public function getValue(): array
  {
    $value = [];

    foreach ($this->getElements() as $index => $element) {
      $value[$this->originalElementNames[$index]] = $element->getValue();
    }

    if (!count($value)) {
      return [];
    }

    return $value;
  }

  public function getCleanValue(): mixed
  {
    $value = [];

    foreach ($this->getElements() as $index => $element) {
      $value[$this->originalElementNames[$index]] = $element->getCleanValue();
    }

    if (!count($value)) {
      return [];
    }

    return $value;
  }
}