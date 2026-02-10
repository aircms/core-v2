<?php

declare(strict_types=1);

namespace Air\Type;

class Page extends TypeAbstract
{
  const int WIDTH = 360;
  const int HEIGHT = 560;
  const int GUTTER = 10;

  public ?string $backgroundColor = null;
  public ?File $backgroundImage = null;
  public int $width = self::WIDTH;
  public int $height = self::HEIGHT;

  /**
   * @var PageItem[]|null
   */
  public ?array $items = [];
  public int $gutter = self::GUTTER;

  public function getBackgroundColor(): ?string
  {
    return $this->backgroundColor;
  }

  public function getBackgroundImage(): ?File
  {
    return $this->backgroundImage;
  }

  /**
   * @return PageItem[]|null
   */
  public function getItems(): ?array
  {
    return $this->items;
  }

  public function getWidth(): int
  {
    return $this->width;
  }

  public function getHeight(): int
  {
    return $this->height;
  }

  public function getGutter(): int
  {
    return $this->gutter;
  }

  public function __construct(?array $page = [])
  {
    if ($page['backgroundColor'] ?? false) {
      $this->backgroundColor = $page['backgroundColor'];
    }

    if ($page['backgroundImage'] ?? false) {
      $this->backgroundImage = new File($page['backgroundImage']);
    }

    if ($page['width'] ?? false) {
      $this->width = $page['width'];
    }

    if ($page['height'] ?? false) {
      $this->height = $page['height'];
    }

    if ($page['gutter'] ?? false) {
      $this->gutter = $page['gutter'];
    }

    foreach (($page['items'] ?? []) as $item) {
      $this->items[] = new PageItem($item);
    }
  }

  public function asCss(): string
  {
    $imageSrc = null;
    if ($file = $this->getBackgroundImage()) {
      if (str_contains($file->getMime(), 'video')) {
        $imageSrc = $file->getThumbnail();
      } else {
        $imageSrc = $file->getSrc();
      }
    }

    return
      ($imageSrc ? "background-image: url('" . $imageSrc . "');" : '') .
      ($this->getBackgroundColor() ? "background-color: " . $this->getBackgroundColor() . ";" : '') .
      ('width: ' . $this->getWidth() . 'px; height: ' . $this->getHeight() . 'px');
  }
}