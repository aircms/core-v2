<?php

declare(strict_types=1);

namespace Air\Type;

use Exception;

class PageItem extends TypeAbstract
{
  const string TYPE_FILE = 'file';
  const string TYPE_HTML = 'html';
  const string TYPE_EMBED = 'embed';

  public string $type = '';
  public int $width = 0;
  public int $height = 0;
  public int $x = 0;
  public int $y = 0;
  public int $deep = 0;

  /**
   * @var File|array{html: string, color: string}|string
   */
  public mixed $value = null;
  public int $transparent = 100;

  public function getType(): string
  {
    return $this->type;
  }

  public function getWidth(): int
  {
    return $this->width;
  }

  public function getHeight(): int
  {
    return $this->height;
  }

  public function getX(): int
  {
    return $this->x;
  }

  public function getY(): int
  {
    return $this->y;
  }

  /**
   * @return File|array{html: string, color: string}|string
   */
  public function getValue(): mixed
  {
    return $this->value;
  }

  public function getDeep(): int
  {
    return $this->deep;
  }

  public function getTransparent(): int
  {
    return $this->transparent;
  }

  public function __construct(?array $data = [])
  {
    if (!in_array(($data['type'] ?? false), [self::TYPE_FILE, self::TYPE_HTML, self::TYPE_EMBED])) {
      throw new Exception('Unknown page item type: ' . $data['type']);
    }

    $this->value = match ($data['type'] ?? false) {
      self::TYPE_FILE => new File($data['value'] ?? []),
      self::TYPE_EMBED => $data['value'] ?? null,
      self::TYPE_HTML => $data['value'] ?? [],
      default => throw new Exception('Unknown page item type: ' . $data['type']),
    };

    $this->type = $data['type'];

    if ($data['width'] ?? false) {
      $this->width = $data['width'];
    }

    if ($data['height'] ?? false) {
      $this->height = $data['height'];
    }

    if ($data['x'] ?? false) {
      $this->x = $data['x'];
    }

    if ($data['y'] ?? false) {
      $this->y = $data['y'];
    }

    if ($data['deep'] ?? false) {
      $this->deep = $data['deep'];
    }

    if ($data['transparent'] ?? false) {
      $this->transparent = $data['transparent'];
    }
  }

  public function asCss(): string
  {
    $css =
      'left: ' . $this->getX() . 'px; top: ' . $this->getY() . 'px;' .
      'width: ' . $this->getWidth() . 'px; height: ' . $this->getHeight() . 'px;' .
      'z-index: ' . $this->getDeep() . ';' .
      'opacity: ' . ($this->getTransparent() / 100) . ';';

    if ($this->value instanceof File) {
      if ($this->value->isImage()) {
        $css .= "background-image: url('" . $this->getValue()->getSrc() . "');";
      } else {
        $css .= "background-image: url('" . $this->getValue()->getThumbnail() . "');";
      }
    }

    return $css;
  }
}