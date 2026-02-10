<?php

declare(strict_types=1);

namespace Air\Model\Driver;

use Air\Model\ModelAbstract;

abstract class DriverAbstract
{
  private ?ModelAbstract $model = null;
  private array $config;

  public function __construct(array $config = [])
  {
    $this->config = $config;
  }

  public function getConfig(): array
  {
    return $this->config;
  }

  public function fetchObject(array|string|int $cond = [], array $sort = [], array $map = []): ModelAbstract|static
  {
    $model = static::fetchOne($cond, $sort, $map);
    if ($model) {
      return $model;
    }
    return $this->getModel();
  }

  abstract public function fetchOne(array|string|int $cond = [], array $sort = [], array $map = []): mixed;

  public function getModel(): ModelAbstract
  {
    return $this->model;
  }

  public function setModel(ModelAbstract $model): void
  {
    $this->model = $model;
  }

  abstract public function save(): int;

  abstract public function remove(array|string|int $cond = [], ?int $limit = null): int;

  abstract public function fetchAll(
    array $cond = [],
    array $sort = [],
    ?int  $count = null,
    ?int  $offset = null,
    array $map = []
  ): array|CursorAbstract;

  abstract public function count(array $cond = []): int;

  abstract public function batchInsert(array $data = []): int;

  abstract public function insert(array $data = []): int;

  abstract public function update(array|string|int $cond = [], array $data = []): int;

  abstract public function getManager(): mixed;

  abstract public function reflectSchema(): void;
}