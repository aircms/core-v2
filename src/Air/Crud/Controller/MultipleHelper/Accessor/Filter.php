<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper\Accessor;

use Air\Crud\Model\Language;
use Air\Model\ModelAbstract;
use Closure;
use ReflectionClass;

class Filter
{
  const string MODEL = 'model';
  const string SEARCH = 'search';
  const string BOOL = 'bool';
  const string DATETIME = 'dateTime';
  const string DATE = 'date';
  const string SELECT = 'select';
  const string ENUM = 'enum';

  public static string|null $entity = null;

  private static function getModelClass(): ModelAbstract
  {
    return new self::$entity;
  }

  public static function filter(
    string            $type,
    string|array|null $by = null,
    mixed             $value = null,
    ?string           $title = null,
    ?string           $true = null,
    ?string           $false = null,
    ?string           $field = null,
    ?string           $model = null,
    ?string           $parent = null,
    ?Closure          $source = null,
    ?array            $options = null,
  ): array
  {
    if (!$title && is_string($by)) {
      $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $by);
      $title = ucfirst(strtolower($title));
    }

    return [
      'type' => $type,
      'title' => $title,
      'by' => $by,
      'value' => $value,
      'true' => $true,
      'false' => $false,
      'field' => $field,
      'model' => $model,
      'source' => $source,
      'options' => $options,
      'parent' => $parent,
    ];
  }

  public static function dateTime(string $by, ?string $value = null): array
  {
    return self::filter(self::DATETIME, $by, $value);
  }

  public static function date(string $by, ?string $value = null): array
  {
    return self::filter(self::DATE, $by, $value);
  }

  public static function enabled(): array
  {
    return self::bool('Enabled', 'enabled', 'Enabled', 'Disabled', 'true');
  }

  public static function createdAt(?string $value = null): array
  {
    return self::dateTime('createdAt', $value);
  }

  public static function updatedAt(?string $value = null): array
  {
    return self::dateTime('updatedAt', $value);
  }

  public static function search(
    array|string $by = ['search', 'id', 'title', 'subTitle', 'description', 'url'],
    ?string      $value = null
  ): array
  {
    return self::filter(self::SEARCH, $by, $value);
  }

  public static function bool(?string $title = null, ?string $by = null, string $true = 'Yes', string $false = 'No', ?string $value = null): array
  {
    return self::filter(self::BOOL, $by, $value, $title, $true, $false);
  }

  public static function model(
    ?string  $model = null,
    ?string  $title = null,
    ?string  $by = null,
    string   $field = 'title',
    ?Closure $source = null,
    ?string  $value = null,
    ?string  $parent = null
  ): array
  {
    if ($model && !$by) {
      $reflection = new ReflectionClass($model);
      $by = lcfirst($reflection->getShortName());
    } else if (!$model && $by) {
      $model = self::getModelClass()->getMeta()->getPropertyWithName($by)->getRawType();
    }
    if (!$title) {
      $title = ucfirst($by);
    }
    return self::filter(self::MODEL, $by, $value, $title, field: $field, model: $model, source: $source, parent: $parent);
  }

  public static function language(?string $value = null): array
  {
    return self::model(Language::class, 'Language', 'language', value: $value);
  }

  public static function enum(
    string  $by,
    ?string $title = null,
    mixed   $value = null,
  ): array
  {
    $reflectionClass = new ReflectionClass(self::getModelClass());
    $constants = $reflectionClass->getConstants();

    $options = [];
    $upperProp = strtoupper($by);
    foreach ($constants as $constantName => $constantValue) {
      if (str_starts_with(strtoupper($constantName), $upperProp . '_')) {
        $title = ucfirst(strtolower(str_replace(['_', '-'], ' ', $constantValue)));
        $options[$title] = $constantValue;
      }
    }

    if (!$title) {
      $title = Header::getTitleBasedOnModelOrPropertyName($by);
    }

    return Filter::select(
      title: $title,
      by: $by,
      options: $options,
      value: $value
    );
  }

  public static function select(
    ?string $title = null,
    ?string $by = null,
    mixed   $value = null,
    ?array  $options = []
  ): array
  {
    $_options = [];
    if (count($options)) {
      if (isset($options[0]['title'])) {
        $_options = $options;
      } else if (isset($options[0])) {
        foreach ($options as $option) {
          $_options[] = ['title' => ucfirst($option), 'value' => $option];
        }
      } else {
        foreach ($options as $t => $v) {
          $_options[] = ['title' => ucfirst($t), 'value' => $v];
        }
      }
    }
    return self::filter(self::SELECT, $by, $value, $title, options: $_options);
  }
}