<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper\Accessor;

use Air\Crud\Model\Language;
use Air\Model\ModelAbstract;
use Closure;
use ReflectionClass;
use Throwable;

class Header
{
  const string SM = 'sm';
  const string MD = 'md';
  const string LG = 'lg';
  const string XL = 'xl';

  const string SOURCE = 'source';
  const string MODEL = 'model';
  const string IMAGE = 'image';
  const string IMAGES = 'images';
  const string LONGTEXT = 'longtext';
  const string TEXT = 'text';
  const string BOOL = 'bool';
  const string DATETIME = 'dateTime';
  const string DATE = 'date';
  const string SELECT = 'select';

  public static string|null $entity = null;

  private static function getModelClass(): ModelAbstract
  {
    return new self::$entity;
  }

  public static function getTitleBasedOnModelOrPropertyName(
    ?string $by = null,
    ?string $model = null,
  ): ?string
  {
    if (!$model && !$by) {
      return null;
    }

    $title = null;

    if ($by) {
      $title = $by;
    } else if ($model) {
      $reflection = new ReflectionClass($model);
      $title = $reflection->getShortName();
    }

    $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $title);
    return ucfirst(strtolower($title));
  }

  public static function col(
    string  $type,
    ?string $title = null,
    ?string $by = null,
    ?string $size = self::MD,
    ?string $model = null,
    ?string $field = null,
    ?array  $options = [],
  ): array
  {
    $field = $field ?? 'title';

    if (!$title) {
      $title = self::getTitleBasedOnModelOrPropertyName($by, $model);
    }

    if (!$by && $model) {
      $className = new ReflectionClass($model);
      $by = lcfirst($className->getShortName());
    }

    if (!$model && $by && $type === self::MODEL) {
      try {
        $model = self::getModelClass()->getMeta()->getPropertyWithName($by)->getType();
      } catch (Throwable) {
      }
    }

    return [
      'type' => $type,
      'title' => $title,
      'by' => $by,
      'size' => $size,
      'model' => $model,
      'field' => $field,
      'options' => $options
    ];
  }

  public static function image(): array
  {
    return self::col(self::IMAGE, 'Img.', 'image', self::SM);
  }

  public static function images(): array
  {
    return self::col(self::IMAGES, 'Img.', 'images', self::SM);
  }

  public static function source(string $title, Closure $source): array
  {
    return ['type' => self::SOURCE, 'title' => $title, 'source' => $source];
  }

  public static function color(?string $by = 'color', ?string $title = 'Color'): array
  {
    return self::source($title, function (ModelAbstract $modelAbstract) use ($by) {
      return div(class: 'color-preview', attr: [
        'style' => 'background-color: ' . $modelAbstract->{$by}
      ]);
    });
  }

  public static function badge(string $by, ?string $style = PRIMARY): array
  {
    return self::source(self::getTitleBasedOnModelOrPropertyName($by), function (ModelAbstract $model) use ($by, $style) {
      return badge((string)$model->{$by}, $style);
    });
  }

  public static function text(?string $title = null, ?string $by = null, ?string $size = null): array
  {
    return self::col(self::TEXT, $title, $by, $size);
  }

  public static function title(?string $size = self::XL): array
  {
    return self::text(by: 'title', size: $size);
  }

  public static function longtext(?string $title = null, ?string $by = null, ?string $size = self::LG): array
  {
    return self::col(self::LONGTEXT, $title, $by, $size);
  }

  public static function bool(?string $title = null, ?string $by = null): array
  {
    return self::col(self::BOOL, $title, $by, self::SM);
  }

  public static function enabled(): array
  {
    return self::bool(by: 'enabled');
  }

  public static function dateTime(?string $title = null, ?string $by = null): array
  {
    return self::col(self::DATETIME, $title, $by);
  }

  public static function date(?string $title = null, ?string $by = null): array
  {
    return self::col(self::DATE, $title ?? 'Date', $by);
  }

  public static function createdAt(): array
  {
    return self::source('Created', function (ModelAbstract $model) {
      return badge(format($model->createdAt), DARK);
    });
  }

  public static function updatedAt(): array
  {
    return self::source('Updated', function (ModelAbstract $model) {
      return badge(format($model->updatedAt), DARK);
    });
  }

  public static function model(
    ?string $model = null,
    ?string $title = null,
    ?string $by = null,
    ?string $field = null,
    ?string $size = self::SM
  ): array
  {
    return self::col(self::MODEL, $title, $by, $size, $model, $field);
  }

  public static function language(): array
  {
    return self::model(Language::class);
  }

  public static function createdAndUpdated(): array
  {
    return self::source('Updated', fn(ModelAbstract $model) => vertical(content: [
      badge(format($model->updatedAt), LIGHT),
      badge(format($model->createdAt), DARK),
    ]));
  }
}