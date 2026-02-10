<?php

declare(strict_types=1);

namespace Air\Model\Driver\Mysql;

use Air\Model\Driver\CursorAbstract;
use Air\Model\Driver\DriverAbstract;
use Air\Model\Driver\Mysql\Exception\FailedStatement;
use Air\Model\Meta\Property;
use Air\Model\ModelAbstract;
use Air\Type\TypeAbstract;
use MongoDB\BSON\Regex;
use PDO;
use PDOStatement;
use Throwable;

class Driver extends DriverAbstract
{
  private ?array $manager = null;

  public function save(): int
  {
    $model = $this->getModel();
    $primary = $model->getMeta()->getPrimary();
    $data = $model->getData();

    if (!$model->{$primary}) {
      $model->{$primary} = $this->insert($data);
      return 1;
    }
    return $this->update([$primary => $model->{$primary}], $data);
  }

  private function normalizeDataTypes(array $data = []): array
  {
    foreach ($data as $name => $value) {

      try {
        $property = $this->getModel()->getMeta()->getPropertyWithName($name);
      } catch (Throwable) {
        continue;
      }

      if ($value instanceof ModelAbstract) {
        $data[$name] = $value->{$value->getMeta()->getPrimary()};

      } else if ($value instanceof Cursor) {
        $ids = [];
        foreach ($value as $record) {
          $ids[] = (string)$record->{$value->getModel()->getMeta()->getPrimary()};
        }
        $data[$name] = json_encode($ids);

      } else if ($property->isModel()) {
        if (is_string($value)) {
          $value = json_decode($value);
        }

        if ($property->isMultiple()) {
          $ids = [];
          foreach ((array)$value as $record) {
            if (is_object($record)) {
              $ids[] = (string)$record->{$record->getMeta()->getPrimary()};
            } else {
              $ids[] = (string)$record;
            }
          }
          $data[$name] = json_encode($ids);
        } else {
          $data[$name] = $value;
        }

      } else if ($value instanceof TypeAbstract) {
        $data[$name] = json_encode($value->toRaw());

      } else {
        try {
          if ($property->isMultiple() && $property->isTypeAbstract()) {
            $data[$name] = [];

            /** @var TypeAbstract[] $value */
            foreach ($value as $item) {
              if (is_object($item)) {
                $data[$name][] = $item->toRaw();
              } else {
                $data[$name][] = $item;
              }
            }
          }
        } catch (Throwable) {
        }
      }
    }

    return $data;
  }

  public function getManager(): PDO
  {
    $config = $this->getConfig();
    $managerConfigKey = md5(var_export($config, true));

    if (empty($this->manager[$managerConfigKey])) {

      $host = $config['host'] ?? 'localhost';

      $connection = 'mysql:host=' . $host . ';dbname=' . $config['db'] . ';charset=utf8mb4';

      if (isset($config['port'])) {
        $connection .= ';port=' . $config['port'];
      }

      $pdo = new PDO($connection, $config['user'] ?? null, $config['pass'] ?? null);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $this->manager[$managerConfigKey] = $pdo;
    }
    return $this->manager[$managerConfigKey];
  }

  public function getCollectionNamespace(): string
  {
    return implode('.', [
      $this->getConfig()['db'],
      $this->getModel()->getMeta()->getCollection()
    ]);
  }

  public function remove(array|string|int $cond = [], ?int $limit = null): int
  {
    if (is_string($cond) || is_int($cond)) {
      $cond = [$this->getModel()::meta()->getPrimary() => $cond];
      $limit = 1;
    }

    $model = $this->getModel();
    if ($model->{$model->getMeta()->getPrimary()}) {
      $cond = ['id' => $model->{$model->getMeta()->getPrimary()}];
      $limit = 1;
    } else {
      $cond = $this->normalizeDataTypes($cond);
    }

    $cond = $this->convertCond($cond);
    $table = $this->getModel()->getMeta()->getCollection();

    $sql = 'DELETE FROM `' . $table . '` ' . $cond;

    if ($limit) {
      $sql .= ' limit ' . $limit;
    }

    $statement = $this->exec($sql);
    return $statement->rowCount();
  }

  public function exec(string $sql, array $params = []): PDOStatement|false
  {
    try {
      $statement = $this->getManager()->prepare($sql);
      $statement->execute($params);

      return $statement;

    } catch (Throwable $e) {
      throw new FailedStatement($sql, $params, $e);
    }
  }

  private function sqlValue($val): string|int
  {
    if (is_numeric($val)) {
      return $val;
    }

    if (is_bool($val)) {
      return $val ? 'TRUE' : 'FALSE';
    }

    return "'" . addslashes((string)$val) . "'";
  }

  private function convertCond(array $cond = [], bool $fromRecursive = false): string
  {
    $sqlParts = [];

    foreach ($cond as $field => $value) {

      try {
        $prop = $this->getModel()->getMeta()->getPropertyWithName($field);
        if ($prop->isModel() && $prop->isMultiple()) {
          if (is_int($value)) {
            $value = new Regex('"' . $value . '"');
          }
        }
      } catch (Throwable) {
      }

      if ($field === '$or' || $field === '$and') {
        $logic = $field === '$or' ? 'OR' : 'AND';
        $subParts = [];
        foreach ($value as $subQuery) {
          $subParts[] = '(' . $this->convertCond($subQuery, true) . ')';
        }
        $sqlParts[] = implode(" $logic ", $subParts);
        continue;
      }

      // Regex
      if ($value instanceof Regex) {
        $pattern = $value->getPattern();
        $like = '%' . $pattern . '%';
        $operator = 'LIKE';
        $sqlParts[] = "`$field` $operator '$like'";
        continue;
      }

      // Операторы сравнения
      if (is_array($value)) {
        foreach ($value as $op => $val) {
          switch ($op) {
            case '$eq':
              $sqlParts[] = "`$field` = " . $this->sqlValue($val);
              break;
            case '$ne':
              $sqlParts[] = "`$field` != " . $this->sqlValue($val);
              break;
            case '$gt':
              $sqlParts[] = "`$field` > " . $this->sqlValue($val);
              break;
            case '$gte':
              $sqlParts[] = "`$field` >= " . $this->sqlValue($val);
              break;
            case '$lt':
              $sqlParts[] = "`$field` < " . $this->sqlValue($val);
              break;
            case '$lte':
              $sqlParts[] = "`$field` <= " . $this->sqlValue($val);
              break;
            case '$in':
              $vals = implode(", ", array_map([$this, 'sqlValue'], $val));
              $sqlParts[] = "`$field` IN ($vals)";
              break;
            case '$nin':
              $vals = implode(", ", array_map([$this, 'sqlValue'], $val));
              $sqlParts[] = "`$field` NOT IN ($vals)";
              break;
            case '$exists':
              $sqlParts[] = "`$field` IS " . ($val ? "NOT NULL" : "NULL");
              break;
            default:
              $sqlParts[] = "`$field` = " . $this->sqlValue($val); // fallback
          }
        }
      } else {
        $sqlParts[] = "`$field` = " . $this->sqlValue($value);
      }
    }

    if (!$fromRecursive && count($sqlParts)) {
      return 'where ' . implode(" and ", $sqlParts);
    }

    return implode(" and ", $sqlParts);
  }

  private function convertSort(array $sort = []): string
  {
    $orderClauses = [];
    foreach ($sort as $field => $direction) {
      $dir = (int)$direction === -1 ? 'DESC' : 'ASC';
      $orderClauses[] = "`$field` $dir";
    }
    if (count($orderClauses)) {
      return 'ORDER BY ' . implode(', ', $orderClauses);
    }
    return '';
  }

  private function getProjection(array $map = []): string
  {
    return count($map) ? '`' . implode('`, `', $map) . '`' : '*';
  }

  public function fetchOne(array|string|int $cond = [], array $sort = [], array $map = []): mixed
  {
    if (is_string($cond) || is_int($cond)) {
      $cond = [$this->getModel()::meta()->getPrimary() => $cond];
    }

    $cond = $this->convertCond(
      $this->normalizeDataTypes($cond)
    );

    $projection = $this->getProjection($map);
    $sort = $this->convertSort($sort);
    $table = $this->getModel()->getMeta()->getCollection();

    $sql = 'select ' . $projection . ' from ' . $table . ' ' . $cond . ' ' . $sort . ' limit 1';
    $statement = $this->exec($sql);

    $data = $statement->fetchAll(PDO::FETCH_ASSOC);
    $cursor = new Cursor($this->getModel(), $data, $this->getConfig());

    if ($cursor->offsetExists(0)) {
      return $cursor->offsetGet(0);
    }
    return null;
  }

  public function fetchAll(
    array $cond = [],
    array $sort = [],
    ?int  $count = null,
    ?int  $offset = null,
    array $map = []
  ): array|CursorAbstract
  {
    $cond = $this->convertCond(
      $this->normalizeDataTypes($cond)
    );

    $projection = $this->getProjection($map);
    $sort = $this->convertSort($sort);
    $table = $this->getModel()->getMeta()->getCollection();

    $sql = 'select ' . $projection . ' from `' . $table . '` ' . $cond . ' ' . $sort;

    if ($count) {
      $sql .= ' limit ' . $count;
    }

    if ($offset) {
      $sql .= ' offset ' . $offset;
    }

    $data = $this->exec($sql)->fetchAll(PDO::FETCH_ASSOC);
    return new Cursor($this->getModel(), $data, $this->getConfig());
  }

  public function count(array $cond = []): int
  {
    $table = $this->getModel()->getMeta()->getCollection();

    $cond = $this->convertCond(
      $this->normalizeDataTypes($cond)
    );

    $sql = 'select count(*) from `' . $table . '` ' . $cond;

    return $this->exec($sql)->fetchColumn();
  }

  public function batchInsert(?array $data = null): int
  {
    $rows = [];

    foreach ($data as $dataItem) {

      $modelClassName = $this->getModel()->getModelClassName();

      /** @var ModelAbstract $model */
      $model = new $modelClassName();

      if ($model->getMeta()->hasProperty('updatedAt')) {
        $updatedAtProperty = $model->getMeta()->getPropertyWithName('updatedAt');
        if ($updatedAtProperty->getType() === 'integer') {
          $dataItem['updatedAt'] = time();
        }
      }

      if ($model->getMeta()->hasProperty('createdAt')) {
        $updatedAtProperty = $model->getMeta()->getPropertyWithName('createdAt');
        if ($updatedAtProperty->getType() === 'integer') {
          $dataItem['createdAt'] = time();
        }
      }

      $model->populate($dataItem);
      $rows[] = $model->getData();
    }

    if (count($rows)) {
      $collection = $this->getModel()->getMeta()->getCollection();
      foreach ($rows as $row) {
        if (empty($row['id'])) {
          unset($row['id']);
        }
        $fields = '(`' . implode('`, `', array_keys($row)) . '`)';
        $valueHolders = '(:' . implode(', :', array_keys($row)) . ')';
        $sql = 'insert into `' . $collection . '` ' . $fields . ' values ' . $valueHolders;
        $this->exec($sql, $row);
      }
      return count($rows);
    }

    return 0;
  }

  public function insert(array $data = []): int
  {
    self::batchInsert([$data]);
    return (int)$this->getManager()->lastInsertId();
  }

  public function update(array|string|int $cond = [], array $data = []): int
  {
    if (is_string($cond) || is_int($cond)) {
      $cond = [$this->getModel()::meta()->getPrimary() => $cond];
    }

    $modelClassName = $this->getModel()->getModelClassName();

    /** @var ModelAbstract $model */
    $model = new $modelClassName();

    $cond = $this->convertCond(
      $this->normalizeDataTypes($cond)
    );

    $model->populate($data);
    $data = $this->normalizeDataTypes(
      $model->getData()
    );

    $table = $this->getModel()->getMeta()->getCollection();
    unset($data[$model->getMeta()->getPrimary()]);

    $fields = [];
    foreach ($data as $field => $value) {
      $fields[] = '`' . $field . '` = :' . $field;
    }

    return $this->exec('update `' . $table . '` set ' . implode(', ', $fields) . ' ' . $cond, $data)->rowCount();
  }

  public function reflectSchema(): void
  {
    $meta = $this->getModel()::meta();
    $manager = $this->getManager();

    $collection = $meta->getCollection();
    $primary = $meta->getPrimary();

    try {
      $this->exec('select 1 from `' . $collection . '`');
    } catch (Throwable) {
      $this->exec('create table `' . $collection . '` (`' . $primary . '` int not null auto_increment primary key)');
    }

    $fields = [];
    foreach ($this->exec('show columns from `' . $collection . '`')->fetchAll(PDO::FETCH_ASSOC) as $options) {
      $fields[$options['Field']] = $options;
    }

    foreach ($meta->getProperties() as $property) {
      $name = $property->getName();
      $type = $this->getSqlTypeBaseFromProperty($property);

      if ($name !== $meta->getPrimary()) {

        if (!isset($fields[$name])) {
          $this->exec('alter table `' . $collection . '` add `' . $name . '` ' . $type);

        } else if ($type !== $fields[$name]['Type']) {
          $this->exec('alter table `' . $collection . '` modify `' . $name . '` ' . $type);
        }
      }
    }
  }

  public function getSqlTypeBaseFromProperty(Property $property): string
  {
    $type = match ($property->getRawType()) {
      'integer', 'int' => 'int',
      'string', 'array' => 'text',
      'boolean', 'bool' => 'bool',
      'float' => 'float',
      'double' => 'double',
      default => null
    };

    if ($property->isModel()) {
      $type = $property->isMultiple() ? 'text' : 'int';
    }

    if ($property->isTypeAbstract()) {
      $type = 'text';
    }

    return $type;
  }
}
