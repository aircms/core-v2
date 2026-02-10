<?php

declare(strict_types=1);

namespace Air\Model\Driver\Mysql;

use Air\Model\Driver\DocumentAbstract;
use Air\Model\Driver\Exception\PropertyHasDifferentType;
use Air\Model\Meta\Property;
use Air\Model\ModelAbstract;
use Air\Type\TypeAbstract;

class Document extends DocumentAbstract
{
  protected function castDataType(Property $property, $value, bool $isSet = true, bool $toArray = false): mixed
  {
    if (in_array($property->getType(), ['integer', 'float', 'double', 'string', 'boolean', 'NULL'])) {
      settype($value, $property->getType());
      return $value;
    }

    if ($property->getType() === 'array') {
      if ($isSet) {
        if (is_string($value)) {
          return $value;
        }
        if (is_array($value) && !count($value)) {
          return json_encode([]);
        }
        if (is_array($value) && count($value)) {
          return json_encode($value);
        }
      }

      if (is_null($value)) {
        return [];
      }

      if (is_string($value)) {
        return json_decode($value, true);
      }
    }

    if ($property->isModel() && $property->isMultiple()) {

      if ($isSet) {
        if (is_string($value)) {
          return $value;
        }
        if (is_array($value) && !count($value)) {
          return json_encode([]);
        }
        if (is_array($value) && count($value) && is_array($value[0])) {
          return json_encode($value);
        }
        if (is_array($value) && count($value) && $value[0] instanceof ModelAbstract) {
          $ids = [];
          foreach ($value as $item) {
            $ids[] = (string)$item->{$item::meta()->getPrimary()};
          }
          return json_encode($ids);
        }
      }

      if (is_null($value)) {
        return [];
      }

      /** @var ModelAbstract $modelClassName */
      $modelClassName = $property->getRawType();
      $items = [];
      foreach (json_decode($value, true) as $id) {
        $items[] = $modelClassName::fetchOne([$modelClassName::meta()->getPrimary() => $id]);
      }

      if ($toArray) {
        foreach ($items as $index => $item) {
          $items[$index] = $item->toArray();
        }
      }

      return $items;
    }

    if ($property->isModel() && !$property->isMultiple()) {
      if ($isSet) {
        if (is_numeric($value)) {
          return (int)$value;
        }
        if ($value instanceof ModelAbstract) {
          return $value->{$value::meta()->getPrimary()};
        }
      }

      if (is_null($value)) {
        return null;
      }

      if (is_numeric($value)) {

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
        if (is_string($value)) {
          return $value;
        }

        if (is_array($value) && !count($value)) {
          return json_encode([]);
        }

        if (is_array($value) && count($value) && is_string($value[0])) {
          $json = [];
          foreach ($value as $item) {
            $json[] = json_decode($item, true);
          }
          return json_encode($json);

        } else if (is_array($value) && count($value) && is_array($value[0])) {
          return json_encode($value);

        } else if (is_array($value) && count($value) && is_object($value[0])) {
          $json = [];
          foreach ($value as $item) {
            $json[] = $item->toRaw();
          }
          return json_encode($json);
        }
      }

      $typeClassName = $property->getRawType();
      $result = [];

      if (is_string($value)) {
        $items = [];
        foreach (json_decode($value, true) as $item) {
          $items[] = new $typeClassName($item, $this->getModel());
        }
        $result = $items;

      } else if (is_array($value) && count($value)) {

        $typeClassName = $property->getRawType();
        $objects = [];

        foreach ($value as $item) {
          if (is_object($item) && $typeClassName === $item::class) {
            $objects[] = $item;

          } else if (is_array($value)) {
            $objects[] = new $typeClassName($item, $this->getModel());

          } else if (is_string($value)) {
            $objects[] = new $typeClassName(json_decode($item, true), $this->getModel());
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
        if (is_string($value)) {
          return $value;
        }
        if (is_object($value)) {
          return json_encode($value->toRaw());
        }
        if (is_array($value)) {
          return json_encode($value);
        }
      }

      if (is_null($value)) {
        return null;
      }

      /** @var TypeAbstract $typeClassName */
      $typeClassName = $property->getType();
      $instance = null;

      if (is_string($value)) {
        $instance = new $typeClassName(json_decode($value, true), $this->getModel());

      } else if (is_object($value)) {
        $instance = $value;

      } else if (is_array($value) && count($value)) {
        $instance = new $typeClassName($value, $this->getModel());
      }

      return $toArray ? $instance->toRaw() : $instance;
    }

    throw new PropertyHasDifferentType(
      $this->getModel()->getMeta()->getCollection(),
      $property->getName(),
      $property->getType(),
      gettype($value)
    );
  }
}