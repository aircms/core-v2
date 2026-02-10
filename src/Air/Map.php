<?php

declare(strict_types=1);

namespace Air;

use Air\Model\Driver\CursorAbstract;
use Air\Model\Driver\Mongodb\Cursor;
use Air\Model\ModelAbstract;
use Air\Model\ModelInterface;
use Air\Type\TypeAbstract;
use Closure;
use Throwable;

class Map
{
  public static function adjust(mixed $data, array $mapper, array $userData = []): mixed
  {
    $props = [];

    if (is_array($data) && isset($data[0]) && is_array($data[0])) {
      $props = array_map(fn(mixed $key) => (string)$key, array_keys($data[0]));

    } else if (is_array($data) && !isset($data[0])) {
      $props = array_keys($data);

    } else if ($data instanceof ModelAbstract) {
      $props = array_keys($data->getMeta()->getAssocProperties());

    } else if ($data instanceof CursorAbstract) {
      $props = array_keys($data->getModel()->getMeta()->getAssocProperties());
    }

    $mapper = [
      ...array_combine($props, $props),
      ...$mapper
    ];

    return self::execute($data, $mapper, $userData);
  }

  public static function execute(mixed $data, mixed $mapper, array $userData = []): mixed
  {
    if (!$data) {
      return null;
    }
    if (is_array($mapper)) {
      return self::executeAssoc($data, $mapper, $userData);
    }
    if (is_string($mapper)) {
      return self::executeLine($data, $mapper, $userData);
    }
    if ($mapper instanceof Closure) {
      return self::executeLineClosure($data, $mapper, $userData);
    }
    return null;
  }

  public static function single(mixed $data, mixed $mapper, array $userData = []): mixed
  {
    return self::execute($data, $mapper, $userData);
  }

  public static function multiple(mixed $data, mixed $mapper, array $userData = []): array
  {
    $result = self::execute($data, $mapper, $userData);
    return array_values(array_filter($result ?: []));
  }

  public static function isSingle(mixed $data): bool
  {
    if ($data instanceof ModelInterface || $data instanceof TypeAbstract) {
      return true;
    }

    if ($data instanceof Cursor) {
      return false;
    }

    if (is_array($data) && isset($data[0])) {
      return false;
    }

    return true;
  }

  public static function executeAssoc(mixed $data, array $mapper = [], array $userData = []): ?array
  {
    if (self::isSingle($data)) {
      return self::executeSingle($data, $mapper, $userData);
    }
    $mapped = [];
    foreach ($data as $row) {
      $mapped[] = self::executeSingle($row, $mapper, $userData);
    }
    return $mapped;
  }

  public static function executeLine(mixed $data, string $mapper, array $userData = []): mixed
  {
    if (self::isSingle($data)) {
      return self::executeSingle($data, [$mapper], $userData)[$mapper];
    }
    $mapped = [];
    foreach ($data as $row) {
      $mapped[] = self::executeSingle($row, [$mapper], $userData)[$mapper];
    }
    return $mapped;
  }

  public static function executeLineClosure(mixed $data, Closure $mapper, array $userData = []): mixed
  {
    if (self::isSingle($data)) {
      return self::transform($data, null, $mapper, $userData);
    }
    $mapped = [];
    foreach ($data as $index => $row) {
      $mapped[] = self::transform($row, null, $mapper, $userData, $index);
    }
    return $mapped;
  }

  public static function executeSingle($data, array $mapper, array $userData = []): array
  {
    $mapped = [];
    foreach ($mapper as $dest => $value) {
      if (is_string($dest)) {
        $mapped[$dest] = self::transform($data, $dest, $value, $userData ?? []);
      } else {
        $mapped[$value] = self::transform($data, $dest, $value, $userData ?? []);
      }
    }
    return $mapped;
  }

  public static function transform($data, $dest, $value, array $userData = [], int $index = 0): mixed
  {
    if ($value instanceof Closure) {
      return $value($data, $userData ?: [], $index);
    }
    if ($data instanceof ModelAbstract) {
      try {
        return $data->getMeta()->hasProperty($value) ? $data->{$value} : $data->{$dest};
      } catch (Throwable) {
        return null;
      }
    }
    if ($data instanceof TypeAbstract) {
      try {
        return $data->{$value} ?? $data->{$data};
      } catch (Throwable) {
        return null;
      }
    }
    if (is_array($data)) {
      return $data[$value] ?? $data[$dest] ?? null;
    }
    return null;
  }
}
