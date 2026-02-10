<?php

declare(strict_types=1);

namespace Air\Type;

use Air\Model\Meta\Exception\PropertyWasNotFound;
use Air\Model\ModelAbstract;

class Meta extends TypeAbstract
{
  public string $title = '';
  public string $description = '';
  public string $ogTitle = '';
  public string $ogDescription = '';
  public ?File $ogImage = null;
  public bool $useModelData = false;
  public string $modelClassName = '';
  public mixed $modelObjectId = '';

  public static function dummy(array $data = []): static
  {
    if (!count($data)) {
      $data['useModelData'] = true;
    }

    return new static($data);
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function getOgTitle(): string
  {
    return $this->ogTitle;
  }

  public function getOgDescription(): string
  {
    return $this->ogDescription;
  }

  public function getOgImage(): ?File
  {
    return $this->ogImage;
  }

  public function isUseModelData(): bool
  {
    return $this->useModelData;
  }

  public function getModelClassName(): string
  {
    return $this->modelClassName;
  }

  public function getModelObjectId(): mixed
  {
    return $this->modelObjectId;
  }

  public function __construct(?array $meta = [], ?ModelAbstract $model = null)
  {
    parent::__construct($meta);

    if ($model) {
      $this->modelClassName = $model::class;
      $this->modelObjectId = $model->{$model->getMeta()->getPrimary()};
    }
  }

  public function getComputedData(): array
  {
    if ($this->isUseModelData()) {
      $objectData = $this->getObjectData();
      return [
        'title' => $objectData['title'],
        'description' => $objectData['description'],
        'ogImage' => $objectData['image'],
        'ogTitle' => $objectData['title'],
        'ogDescription' => $objectData['description'],
      ];
    }

    return [
      'title' => $this->getTitle(),
      'description' => $this->getDescription(),
      'ogImage' => $this->getOgImage(),
      'ogTitle' => $this->getOgTitle(),
      'ogDescription' => $this->getOgDescription(),
    ];
  }

  public function __toString(): string
  {
    $data = $this->getComputedData();

    $title = $data['title'];
    $description = $data['description'];
    $ogTitle = $data['ogTitle'];
    $ogDescription = $data['ogDescription'];
    $ogImage = $data['ogImage'];

    $siteUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'];
    $canonical = $siteUrl . $_SERVER['REQUEST_URI'];
    $ogUrl = $siteUrl . explode('?', $_SERVER['REQUEST_URI'])[0];

    $ogType = 'website';

    $tags = [
      // Default Meta tags
      "<title>{$title}</title>",
      "<meta name=\"description\" content=\"{$description}\">",
      "<link rel=\"canonical\" href=\"$canonical\">",

      // OG Meta tags
      "<meta name=\"og:type\" content=\"$ogType\">",
      "<meta name=\"og:url\" content=\"$ogUrl\">",
      "<meta name=\"og:title\" content=\"{$ogTitle}\" itemprop=\"title name\">",
      "<meta name=\"og:description\" content=\"{$ogDescription}\" itemprop=\"description\">",
    ];

    if ($ogImage) {
      $tags[] = "<meta property=\"og:image\" content=\"{$ogImage->getSrc()}\" itemprop=\"image primaryImageOfPage\">";
    }

    return implode("\n", $tags);
  }

  /**
   * @return null[]
   * @throws PropertyWasNotFound
   */
  public function getObjectData(): array
  {
    /** @var ModelAbstract $model */
    $model = $this->modelClassName;
    $object = null;

    if ($model) {
      $object = $model::fetchOne([
        (new $model)->getMeta()->getPrimary() => $this->modelObjectId
      ]);
    }

    $defaults = [
      'title' => null,
      'description' => null,
      'image' => null
    ];

    if ($object) {
      if ($object->getMeta()->hasProperty('title')
        && $object->getMeta()->getPropertyWithName('title')->getType() === 'string') {
        $defaults['title'] = mb_substr($object->title ?? '', 0, 60);

      } elseif ($object->getMeta()->hasProperty('subTitle')
        && $object->getMeta()->getPropertyWithName('subTitle')->getType() === 'string') {
        $defaults['title'] = mb_substr($object->subTitle ?? '', 0, 60);
      }

      if ($object->getMeta()->hasProperty('description')
        && $object->getMeta()->getPropertyWithName('description')->getType() === 'string') {
        $defaults['description'] = mb_substr($object->description, 0, 60);

      } elseif ($object->getMeta()->hasProperty('content')
        && $object->getMeta()->getPropertyWithName('content')->getType() === 'string') {
        $defaults['description'] = mb_substr(strip_tags($object->content ?? ''), 0, 60);

      } elseif ($object->getMeta()->hasProperty('richContent')) {
        /** @var RichContent $richContent */
        foreach (($object->richContent ?? []) as $richContent) {
          if ($richContent->getType() === RichContent::TYPE_HTML) {
            $defaults['description'] = strip_tags($richContent->getType());

          } elseif ($richContent->getType() === RichContent::TYPE_TEXT) {
            $defaults['description'] = $richContent->getValue();
          }
        }
        $defaults['description'] = mb_substr($defaults['description'], 0, 60);
      }

      $defaults['image'] = null;

      if ($object->getMeta()->hasProperty('image')
        && $object->getMeta()->getPropertyWithName('image')->getType() === File::class) {
        $defaults['image'] = $object->image;

      } elseif ($object->getMeta()->hasProperty('images')
        && $object->getMeta()->getPropertyWithName('images')->getType() === File::class . '[]') {
        $defaults['image'] = $object->images[0] ?? null;
      }
    }
    return $defaults;
  }

  public function toArray(): array
  {
    return [
      'title' => $this->title,
      'description' => $this->description,
      'ogTitle' => $this->ogTitle,
      'ogDescription' => $this->ogDescription,
      'ogImage' => $this->ogImage,
      'useModelData' => $this->useModelData,
      'modelClassName' => $this->modelClassName,
      'modelObjectId' => $this->modelObjectId
    ];
  }
}
