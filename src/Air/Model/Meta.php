<?php

declare(strict_types=1);

namespace Air\Model;

use Air\Core\Loader;
use Air\Model\Meta\Exception\CollectionCantBeWithoutPrimary;
use Air\Model\Meta\Exception\CollectionCantBeWithoutProperties;
use Air\Model\Meta\Exception\CollectionNameDoesNotExists;
use Air\Model\Meta\Exception\PropertyIsSetIncorrectly;
use Air\Model\Meta\Exception\PropertyWasNotFound;
use Air\Model\Meta\Property;
use Air\Type\TypeAbstract;
use ReflectionClass;
use Throwable;

final class Meta
{
  const string ID_COLLECTION = '@collection';
  const string ID_PROPERTY = '@property';
  const string ID_PRIMARY = '@primary';

  private static array $cache = [];
  private static array $namespaceCache = [];
  private ?string $collection = null;
  private string $primary = 'id';

  /**
   * @var Property[]
   */
  private array $properties = [];

  /**
   * @var Property[]
   */
  private array $assocProperties = [];
  private ?string $modelClassName;

  public function __construct(ModelAbstract $model)
  {
    $this->modelClassName = get_class($model);
    $this->parse($model);
  }

  private function parse(ModelAbstract $model): void
  {
    if (!empty(self::$cache[$this->modelClassName])) {

      $cache = self::$cache[$this->modelClassName];

      $this->collection = $cache['collection'];
      $this->primary = $cache['primary'];
      $this->properties = $cache['properties'];
      $this->assocProperties = $cache['assocProperties'];

      return;
    }

    $reflection = new ReflectionClass($model);

    $docblock = $reflection->getDocComment();

    $docblock = str_replace('*', '', $docblock);

    $docblock = array_filter(array_map(function ($line) {

      $line = trim($line);

      if (strlen($line) > 0) {
        return $line;
      }
    }, explode("\n", $docblock)));

    $this->properties = [];
    $this->collection = null;

    $reflectionClass = new ReflectionClass($model);
    $constants = $reflectionClass->getConstants();

    foreach ($docblock as $line) {

      if (str_starts_with($line, self::ID_COLLECTION)) {
        $this->collection = ucfirst(strtolower(trim(str_replace(self::ID_COLLECTION, '', $line))));
        continue;
      }

      if (str_starts_with($line, self::ID_PRIMARY)) {
        $this->primary = trim(str_replace(self::ID_PRIMARY, '', $line));
        continue;
      }

      if (str_starts_with($line, self::ID_PROPERTY)) {

        $propertyLine = array_values(array_filter(explode(' ', $line)));

        $property = new Property();

        if (count($propertyLine) < 3) {
          throw new PropertyIsSetIncorrectly($model, $line);
        }

        $propertyName = str_replace('$', '', $propertyLine[2]);
        $propertyType = $propertyLine[1];

        $isArray = false;
        $propType = $propertyType;

        if (str_ends_with($propType, '[]')) {
          $propType = substr($propType, 0, -2);
          $isArray = true;
        }

        $namespaces = Loader::getUsedNamespaces($model::class);

        if (class_exists($namespaces['namespace'] . '\\' . $propType)) {
          $propType = $namespaces['namespace'] . '\\' . $propType;

        } else {
          foreach ($namespaces['uses'] as $namespace) {
            if (str_contains($namespace, $propType)) {
              $propType = $namespace;
            }
          }
        }

        if ($isArray) {
          $propType .= '[]';
        }

        $propertyType = $propType;

        $property->setType($propertyType);
        $property->setName($propertyName);
        $property->setIsMultiple($isArray);
        $property->setRawType(str_replace('[]', '', $propType));

        $options = [];
        $upperProp = strtoupper($property->getName());
        foreach ($constants as $constantName => $constantValue) {
          if (str_starts_with(strtoupper($constantName), $upperProp . '_')) {
            $title = ucfirst(strtolower(str_replace(['_', '-'], ' ', (string)$constantValue)));
            $options[$title] = $constantValue;
          }
        }

        if (count($options)) {
          $property->setIsEnum(true);
          $property->setEnum($options);
        }

        $modelClass = str_replace('[]', '', $propType);
        if (class_exists($modelClass) && is_subclass_of($modelClass, ModelAbstract::class)) {
          $property->setIsModel(true);
        } else {
          $property->setIsModel(false);
        }

        if (class_exists($modelClass) && is_subclass_of($modelClass, TypeAbstract::class)) {
          $property->setIsTypeAbstract(true);
        } else {
          $property->setIsTypeAbstract(false);
        }

        $this->assocProperties[$property->getName()] = $property;
        $this->properties[] = $property;
      }
    }

    if (!strlen($this->collection)) {
      throw new CollectionNameDoesNotExists($model);
    }

    if (!strlen($this->primary)) {
      throw new CollectionCantBeWithoutPrimary($model);
    }

    if (!count($this->properties)) {
      throw new CollectionCantBeWithoutProperties($model);
    }

    self::$cache[$this->modelClassName] = [
      'collection' => $this->collection,
      'properties' => $this->properties,
      'primary' => $this->primary,
      'assocProperties' => $this->assocProperties
    ];
  }

  public function getPrimary(): string
  {
    return $this->primary;
  }

  public function setPrimary(string $primary): void
  {
    $this->primary = $primary;
  }

  /**
   * @return Property[]
   */
  public function getProperties(): array
  {
    return $this->properties;
  }

  /**
   * @return Property[]
   */
  public function getAssocProperties(): array
  {
    return $this->assocProperties;
  }

  public function setProperties(array $fields): void
  {
    $this->properties = $fields;
  }

  public function getPropertyWithName(string $name): Property
  {
    if (isset($this->assocProperties[$name])) {
      return $this->assocProperties[$name];
    }
    throw new PropertyWasNotFound($this->getCollection(), $name);
  }

  public function getCollection(): string
  {
    return $this->collection;
  }

  public function setCollection(string $collection): void
  {
    $this->collection = $collection;
  }

  public function hasProperty(string $name): bool
  {
    return isset($this->assocProperties[$name]);
  }
}
