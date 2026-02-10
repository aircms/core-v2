<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

class MultiplePage extends PageAbstract
{
  public ?string $elementTemplate = 'form/element/multiple-page';
  public ?Page $pageElement = null;
  public ?array $pageElements = [];

  public function getPageElement(): ?Page
  {
    return $this->pageElement;
  }

  public function setPageElement(?Page $pageElement): void
  {
    $this->pageElement = $pageElement;
  }

  /**
   * @return Page[]|null
   */
  public function getPageElements(): ?array
  {
    return $this->pageElements;
  }

  public function setPageElements(?array $pageElements): void
  {
    $this->pageElements = $pageElements;
  }

  public function init(): void
  {
    parent::init();

    $this->pageElement = new Page($this->getName() . '[{{pageId}}]', [
      'allowNull' => $this->isAllowNull(),
      'containerTemplate' => 'form/element/multiple-page/page-template',
    ]);

    $this->initPages($this->value);
  }

  public function initPages(?array $value): void
  {
    $this->pageElements = [];

    foreach (($value ?? []) as $page) {
      $element = new Page($this->getName() . '[' . uniqid() . ']', [
        'allowNull' => $this->isAllowNull(),
        'containerTemplate' => 'form/element/multiple-page/page-template',
        'value' => $page,
      ]);

      $element->init();
      $this->pageElements[] = $element;
    }
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

    if ((!$value || !count($value)) && !$this->isAllowNull()) {
      $this->errorMessages = [Locale::t('Could not be empty')];
      return false;
    }

    $value = array_values(($value ?? []));

    $this->initPages($value);

    foreach ($this->getPageElements() as $index => $pageElement) {
      if (!$pageElement->isValid(($value[$index] ?? []))) {
        $isValid = false;
      }
    }

    return $isValid;
  }

  /**
   * @return \Air\Type\Page[]
   */
  public function getValue(): array
  {
    $pages = parent::getValue();

    if (!$pages) {
      return [];
    }

    $formattedValue = [];

    if (is_string($pages)) {
      $pages = json_decode($pages, true);
    }

    foreach ($pages as $page) {
      if (!($page instanceof \Air\Type\Page)) {
        if (is_string($page)) {
          $page = json_decode($page, true);
        }
        $formattedValue[] = new \Air\Type\Page($page);

      } else {
        $formattedValue[] = $page;
      }
    }

    return $formattedValue;
  }
}