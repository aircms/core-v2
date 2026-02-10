<?php

declare(strict_types=1);

namespace Air\Model\Driver\Mongodb;

use Air\Model\Driver\CursorAbstract;
use Air\Model\Driver\Exception\IndexOutOfRange;
use Air\Model\ModelAbstract;
use IteratorIterator;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use Throwable;

class Cursor extends CursorAbstract
{
  private ?IteratorIterator $iterator = null;
  private int $count = -1;
  private ?Query $query;

  public function __construct(ModelAbstract $model, Query $query, array $config = [])
  {
    parent::__construct($model, [], $config);
    $this->query = $query;
  }

  public function offsetExists($offset): bool
  {
    if (is_numeric($offset)) {

      $this->rewind();

      for ($i = 0; $i < $offset; $i++) {
        $this->next();
      }

      return $this->valid();
    }

    return false;
  }

  /*************** \ArrayIterator implementation ***********/

  public function rewind(): void
  {
    $cursor = $this->executeQuery($this->query);
    $this->iterator = new IteratorIterator($cursor);

    $this->iterator->rewind();
  }

  private function executeQuery(Query $query): \MongoDB\Driver\Cursor
  {
    /** @var Manager $manager */
    $manager = $this->getModel()->getManager();

    return $manager->executeQuery(
      $this->getCollectionNamespace(),
      $query
    );
  }

  /*************** \Iterator implementation ***********/

  public function getCollectionNamespace(): string
  {
    return implode('.', [
      $this->getConfig()['db'],
      $this->getModel()->getMeta()->getCollection()
    ]);
  }

  public function next(): void
  {
    $this->iterator->next();
  }

  public function valid(): bool
  {
    return $this->iterator->valid();
  }

  public function offsetGet(mixed $offset): mixed
  {
    if (isset($this->documents[$offset])) {
      return $this->documents[$offset];
    }

    $this->rewind();

    for ($i = 0; $i < $offset; $i++) {

      try {
        $this->iterator->next();
      } catch (Throwable) {
        throw new IndexOutOfRange($offset, $i + 1);
      }
    }

    $this->documents[$offset] = $this->getDataModel();
    return $this->documents[$offset];
  }

  private function getDataModel(): ModelAbstract
  {
    $currentItem = $this->iterator->current();

    if ($currentItem->_id instanceof ObjectID) {
      $currentItem->_id = (string)$currentItem->_id;
    }

    $data = json_decode(json_encode($currentItem), true);
    $modelClassName = $this->getModel()->getModelClassName();

    /** @var ModelAbstract $model */
    $model = new $modelClassName();
    $model->populate($this->processDataRow($data));

    return $model;
  }


  /*************** \Countable implementation ***********/

  public function processDataRow(array $data): array
  {
    if (isset($data['_id'])) {
      $data['id'] = $data['_id'];
      unset($data['_id']);
    }

    return $data;
  }

  public function current(): ModelAbstract|static
  {
    $offset = $this->iterator->key();

    if (isset($this->documents[$offset])) {
      return $this->documents[$offset];
    }

    $this->documents[$offset] = $this->getDataModel();
    return $this->documents[$offset];
  }

  public function key(): mixed
  {
    return $this->iterator->key();
  }

  public function count(): int
  {
    if ($this->count == -1) {

      $queryResult = $this->executeQuery($this->query);
      $this->count = count($queryResult->toArray());
    }

    return $this->count;
  }
}
