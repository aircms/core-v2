<?php

declare(strict_types=1);

namespace Air\Model;

use Air\Model\Driver\CursorAbstract;
use Air\Model\Driver\DocumentAbstract;

interface ModelInterface
{
  public static function fetchAll(
    array|string|int $cond = [],
    array            $sort = [],
    ?int             $count = null,
    ?int             $offset = null
  ): CursorAbstract|array;

  public static function all(
    array|string|int $cond = [],
    array            $sort = [],
    ?int             $count = null,
    ?int             $offset = null
  ): CursorAbstract|array;

  public static function singleAll(
    array|string|int $cond = [],
    array            $sort = [],
    ?int             $count = null,
    ?int             $offset = null
  ): CursorAbstract|array;

  public static function fetchOne(
    array|string|int $cond = [],
    array            $sort = []
  ): DocumentAbstract|static|null;

  public static function one(
    array|string|int $cond = [],
    array            $sort = []
  ): DocumentAbstract|static|null;

  public static function singleOne(
    array|string|int $cond = [],
    array            $sort = []
  ): DocumentAbstract|static|null;

  public static function fetchObject(
    array|string|int $cond = [],
    array            $sort = []
  ): DocumentAbstract|static|null;

  public static function count(array|string|int $cond = []): int;

  public static function batchInsert(array $data = []): int;

  public static function batchRemove(array|string|int $cond, ?int $limit = null): int;

  public static function insert(array $data = []): int;

  public static function update(array|string|int $cond = [], array $data = []): int;

  public static function iterate(callable $callback, int $batchSize = 1, array $cond = []): void;

  public function getData(): array;

  public function populate(array $data): void;

  public function save(): int;

  public function remove(): void;

  public function toArray(): array;
}