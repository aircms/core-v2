<?php

declare(strict_types=1);

namespace Air\Model\Driver;

use Air\Model\Driver\Exception\UnsupportedCursorOperation;
use Air\Model\ModelAbstract;
use ArrayAccess;
use Countable;
use Iterator;

class CursorAbstract implements Iterator, ArrayAccess, Countable
{
  protected array $documents = [];
  private string|ModelAbstract $model;
  private int $cursorIndex = 0;
  private array $cursorData;
  private ?array $config;

  public function __construct(ModelAbstract $model, array $data, array $config = [])
  {
    $this->model = $model;
    $this->cursorData = $data;
    $this->config = $config;
  }

  public function getConfig(): ?array
  {
    return $this->config;
  }

  public function toArray(): array
  {
    $arrayData = [];
    foreach ($this as $document) {
      $arrayData[] = $document->toArray();
    }
    return $arrayData;
  }

  public function getCursorIndex(): int
  {
    return $this->cursorIndex;
  }

  public function setCursorIndex(int $cursorIndex): void
  {
    $this->cursorIndex = $cursorIndex;
  }

  public function save(): int
  {
    $savedCount = 0;
    foreach ($this->documents as $document) {
      $savedCount += $document->save();
    }
    return $savedCount;
  }

  public function offsetExists($offset): bool
  {
    if (is_numeric($offset)) {
      $data = $this->getCursorData();
      return isset($data[$offset]);
    }
    return false;
  }

  public function getCursorData(): array
  {
    return $this->cursorData;
  }

  public function setCursorData(array $cursorData): void
  {
    $this->cursorData = $cursorData;
  }

  public function offsetGet(mixed $offset): mixed
  {
    return $this->getRowWithIndex($this->getCursorData(), $offset);
  }

  public function getRowWithIndex(array $data, int $index): mixed
  {
    if (isset($this->documents[$index])) {
      return $this->documents[$index];
    }

    if (isset($data[$index])) {

      $modelClassName = $this->getModel()->getModelClassName();

      /** @var ModelAbstract $model */
      $model = new $modelClassName();
      $model->populate(static::processDataRow($data[$index]));

      $this->documents[$index] = $model;

      return $model;
    }

    return null;
  }

  public function getModel(): ModelAbstract
  {
    return $this->model;
  }


  /*************** \ArrayIterator implementation ***********/

  public function setModel(ModelAbstract $model): void
  {
    $this->model = $model;
  }

  public function processDataRow(array $data): array
  {
    return $data;
  }

  public function offsetSet(mixed $offset = null, mixed $value = null): void
  {
    throw new UnsupportedCursorOperation("offsetSet - " . $offset);
  }

  public function offsetUnset(mixed $offset = null): void
  {
    throw new UnsupportedCursorOperation("offsetUnset - " . $offset);
  }


  /*************** \Iterator implementation ***********/

  public function current(): static|ModelAbstract
  {
    return $this->getRowWithIndex($this->getCursorData(), $this->cursorIndex);
  }

  public function next(): void
  {
    $this->cursorIndex++;
  }

  public function key(): mixed
  {
    if (isset($this->cursorData[$this->cursorIndex])) {
      return $this->cursorIndex;
    }
    return null;
  }

  public function valid(): bool
  {
    return isset($this->cursorData[$this->cursorIndex]);
  }

  public function rewind(): void
  {
    $this->cursorIndex = 0;
  }


  /*************** \Countable implementation ***********/

  public function count(): int
  {
    return count($this->cursorData);
  }
}
