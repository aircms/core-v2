<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

class Page extends PageAbstract
{
  public static bool $templatesRendered = false;
  public ?string $elementTemplate = 'form/element/page';

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

    if (!count($value) && !$this->isAllowNull()) {
      $this->errorMessages = [Locale::t('Could not be empty')];
      return false;
    }

    return true;
  }

  public function getValue(): ?\Air\Type\Page
  {
    $page = parent::getValue();

    if (is_string($page)) {
      $page = json_decode($page, true);
    }

    if (!$page) {
      return null;
    }

    if (!($page instanceof \Air\Type\Page)) {
      $page = new \Air\Type\Page($page);
    }

    if (!$page->getBackgroundColor()
      && !$page->getBackgroundImage()
      && !count($page->getItems())
      && $page->getWidth() === \Air\Type\Page::WIDTH
      && $page->getHeight() === \Air\Type\Page::HEIGHT
      && $page->getGutter() === \Air\Type\Page::GUTTER
    ) {
      return null;
    }
    return $page;
  }
}