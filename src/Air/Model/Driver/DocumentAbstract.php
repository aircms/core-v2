<?php

declare(strict_types=1);

namespace Air\Model\Driver;

use Air\Cache;
use Air\Model\Meta\Property;
use Air\Model\ModelAbstract;
use ArrayAccess;

abstract class DocumentAbstract implements ArrayAccess
{
  private ?ModelAbstract $model;
  private array $data = [];

  public function __construct(ModelAbstract $model)
  {
    $this->model = $model;
  }

  public function populate(array $data, bool $fromSet = true): void
  {
    foreach ($this->getModel()->getMeta()->getProperties() as $property) {
      if (isset($data[$property->getName()])) {
        $this->setProperty($property, $data[$property->getName()], $fromSet);
      }
    }
  }

  public function getModel(): ?ModelAbstract
  {
    return $this->model;
  }

  public function setModel(ModelAbstract $model): void
  {
    $this->model = $model;
  }

  public function setProperty(Property $property, mixed $value, bool $fromSet = false): void
  {
    $this->data[$property->getName()] = $this->castDataType($property, $value, $fromSet);
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function toArray(): array
  {
    $arrayData = [];
    foreach ($this->getModel()->getMeta()->getProperties() as $property) {
      $arrayData[$property->getName()] = $this->getProperty($property->getName(), true);
    }
    return $arrayData;
  }

  public function getProperty(string $name, bool $toArray = false): mixed
  {
    $key = [__FUNCTION__, $this->getModel()->getModelClassName(), $this->getData()[$name] ?? '0', $name, $toArray];

    return Cache::single($key, function () use ($name, $toArray, $key) {
      $data = $this->getData();
      $property = $this->getModel()->getMeta()->getPropertyWithName($name);
      $value = $data[$name] ?? null;
      return $this->castDataType($property, $value, false, $toArray);
    });
  }

  public function getData(): array
  {
    return $this->data;
  }

  public function setData(array $data): void
  {
    $this->data = $data;
  }

  public function offsetExists(mixed $offset): bool
  {
    foreach ($this->getModel()->getMeta()->getProperties() as $property) {
      if ($property->getName() == $offset) {
        return true;
      }
    }
    return false;
  }

  public function offsetGet(mixed $offset): mixed
  {
    return $this->__get($offset);
  }

  public function __get(string $name)
  {
    return $this->getProperty($name);
  }

  public function __set(string $name, mixed $value)
  {
    $property = $this->getModel()->getMeta()->getPropertyWithName($name);
    $this->setProperty($property, $value, true);
  }

  public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->__set($offset, $value);
  }

  public function offsetUnset(mixed $offset): void
  {
    $this->__set($offset, null);
  }
}
