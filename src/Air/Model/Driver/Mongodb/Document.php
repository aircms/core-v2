<?php

declare(strict_types=1);

namespace Air\Model\Driver\Mongodb;

use Air\Model\Driver\DocumentAbstract;
use Air\Model\Driver\Exception\PropertyHasDifferentType;
use Air\Model\Meta\Property;
use Air\Model\ModelAbstract;
use Air\Type\TypeAbstract;
use MongoDB\BSON\ObjectId;

class Document extends DocumentAbstract
{
  protected function castDataType(Property $property, $value, bool $isSet = true, bool $toArray = false): mixed
  {
    if (in_array($property->getType(), ['integer', 'float', 'double', 'array', 'string', 'boolean', 'NULL'])) {
      if (is_null($value)) {
        return $value;
      }
      settype($value, $property->getType());
      return $value;
    }

    if ($property->isModel() && $property->isMultiple()) {

      if ($isSet) {
        if (is_string($value)) {
          return $value;
        }
        if (is_array($value) && !count($value)) {
          return [];
        }
        if (is_array($value) && count($value) && is_string($value[0])) {
          return $value;
        }
        if (is_array($value) && count($value) && $value[0] instanceof ModelAbstract) {
          $ids = [];
          foreach ($value as $item) {
            $ids[] = (string)$item->{$item::meta()->getPrimary()};
          }
          return $ids;
        }
      }

      if (is_null($value)) {
        return [];
      }

      /** @var ModelAbstract $modelClassName */
      $modelClassName = $property->getRawType();
      $items = [];
      foreach ($value as $id) {
        $items[] = $modelClassName::fetchOne([$modelClassName::meta()->getPrimary() => $id]);
      }

      $items = array_values(array_filter($items));

      if ($toArray) {
        foreach ($items as $index => $item) {
          $items[$index] = $item->toArray();
        }
      }

      return $items;
    }

    if ($property->isModel() && !$property->isMultiple()) {

      if ($isSet) {
        if (is_string($value)) {
          return $value;
        }
        if ($value instanceof ModelAbstract) {
          return $value->{$value::meta()->getPrimary()};
        }
      }

      if (is_null($value)) {
        return null;
      }

      if (is_string($value)) {

        /** @var ModelAbstract $modelClassName */
        $modelClassName = $property->getType();
        $modelClassObject = new $modelClassName;

        $object = $modelClassName::fetchOne([
          $modelClassObject->getMeta()->getPrimary() => $value
        ]);

        if ($toArray) {
          return $object->toArray();
        }
        return $object;
      }
    }

    if ($property->isTypeAbstract() && $property->isMultiple()) {

      if ($isSet) {
        if (is_array($value) && !count($value)) {
          return [];
        }

        if (is_array($value) && count($value) && is_array($value[0])) {
          return $value;

        } else if (is_array($value) && count($value) && is_object($value[0])) {
          $json = [];
          foreach ($value as $item) {
            $json[] = $item->toRaw();
          }
          return $json;
        }
      }

      $result = [];

      if (is_array($value) && count($value)) {

        $typeClassName = $property->getRawType();
        $objects = [];

        foreach ($value as $item) {
          if (is_object($item) && $typeClassName === $item::class) {
            $objects[] = $item;

          } else if (is_array($value)) {
            $objects[] = new $typeClassName($item, $this->getModel());
          }
        }
        $result = $objects;
      }

      if ($toArray) {
        $items = [];
        foreach ($result as $item) {
          $items[] = $item->toRaw();
        }
        return $items;
      }
      return $result;
    }

    if ($property->isTypeAbstract() && !$property->isMultiple()) {

      if ($isSet) {
        if (is_array($value)) {
          return $value;
        }
        if (is_object($value)) {
          return $value->toRaw();
        }
      }

      if (is_null($value)) {
        return null;
      }

      /** @var TypeAbstract $typeClassName */
      $typeClassName = $property->getType();
      $instance = null;

      if (is_array($value) && count($value)) {
        $instance = new $typeClassName($value, $this->getModel());

      } else if (is_object($value)) {
        $instance = $value;
      }

      return $toArray ? $instance?->toRaw() : $instance;
    }

    throw new PropertyHasDifferentType(
      $this->getModel()->getMeta()->getCollection(),
      $property->getName(),
      $property->getType(),
      gettype($value)
    );
  }
}