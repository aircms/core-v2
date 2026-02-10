<?php

namespace Air\Model;

use Air\Cache;
use Air\Crud\Model\Language;
use Air\Model\Driver\CursorAbstract;
use Air\Model\Driver\DocumentAbstract;
use Air\Model\Driver\DriverAbstract;
use Air\Model\Driver\Mongodb\Document;
use Air\Model\Exception\CallUndefinedMethod;
use Air\Model\Exception\ConfigWasNotProvided;
use Air\Model\Exception\DriverClassDoesNotExists;
use Air\Model\Exception\DriverClassDoesNotExtendsFromDriverAbstract;
use ArrayAccess;

class ModelAbstract implements ModelInterface, ArrayAccess
{
  private static DocumentAbstract|string $driverClassName = '';
  private ?Meta $meta = null;
  private ?DocumentAbstract $document = null;

  public function __construct(?array $data = null)
  {
    if (!Config::getConfig()) {
      throw new ConfigWasNotProvided();
    }

    $this->meta = new Meta($this);

    if ($data) {
      $this->populate($data);
    }
  }

  public static function setDriver(DriverAbstract $class): void
  {
    self::$driverClassName = get_class($class);
  }

  public static function fetchObject(array|string|int $cond = [], array $sort = []): static
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function __callStatic(string $methodName, array $args): mixed
  {
    $driver = self::getDriver();

    $method = [$driver, $methodName];

    if (is_callable($method)) {
      $driver->setModel(new static());
      return call_user_func_array($method, $args);
    }

    throw new CallUndefinedMethod(static::class, $methodName);
  }

  public static function getDriver(): DriverAbstract
  {
    if (!Config::getConfig()) {
      throw new ConfigWasNotProvided();
    }

    $driverClassName = null;

    if (!empty(self::$driverClassName)) {
      $driverClassName = self::$driverClassName;

    } else {
      if (Config::getConfig()['driver'] == 'mongodb') {
        $driverClassName = Driver\Mongodb\Driver::class;

      } else if (Config::getConfig()['driver'] == 'mysql') {
        $driverClassName = Driver\Mysql\Driver::class;
      }
    }

    if ($driverClassName) {
      if (!class_exists($driverClassName)) {
        throw new DriverClassDoesNotExists($driverClassName);
      }

      if (!is_subclass_of($driverClassName, DriverAbstract::class)) {
        throw new DriverClassDoesNotExtendsFromDriverAbstract($driverClassName);
      }
    }

    return new $driverClassName(Config::getConfig());
  }

  public static function batchInsert(array $data = []): int
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function insert(array $data = []): int
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function update(array|string|int $cond = [], array $data = []): int
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function iterate(callable $callback, int $batchSize = 1, array $cond = []): void
  {
    self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function singleOne(array|string|int $cond = [], array $sort = [], array $map = []): static|null
  {
    $cond = static::addCond($cond);
    $sort = static::addPosition($sort);

    return Cache::single(
      ['one', static::class, $cond, $sort, $map],
      fn() => static::fetchOne($cond, $sort, $map)
    );
  }

  public static function object(array|string|int $cond = [], array $sort = [], array $map = []): static|null
  {
    return static::one($cond, $sort, $map) ?? new static();
  }

  public static function one(array|string|int $cond = [], array $sort = [], array $map = []): static|null
  {
    return static::fetchOne(static::addCond($cond), static::addPosition($sort), $map);
  }

  public static function fetchOne(array|string|int $cond = [], array $sort = [], array $map = []): static|null
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function addCond(array|string|int $cond = []): array
  {
    $model = new static();

    if (is_string($cond) || is_int($cond)) {
      $cond = [$model::meta()->getPrimary() => $cond];
    }

    if ($model->getMeta()->hasProperty('enabled') && !isset($cond['enabled'])) {
      $cond['enabled'] = true;
    }

    if ($model->getMeta()->hasProperty('language') && !isset($cond['language'])) {
      $cond['language'] = Language::getLanguage();
    }

    return $cond;
  }

  public static function addPosition(array $sort = []): array
  {
    $model = new static();
    if ($model->getMeta()->hasProperty('position') && !isset($sort['position'])) {
      $sort['position'] = 1;
    }
    return $sort;
  }

  /**
   * @param array|string|int $cond
   * @param array $sort
   * @param int|null $count
   * @param int|null $offset
   * @param array $map
   * @return CursorAbstract|array|static[]
   * @throws CallUndefinedMethod
   */
  public static function singleAll(
    array|string|int $cond = [],
    array            $sort = [],
    ?int             $count = null,
    ?int             $offset = null,
    array            $map = []
  ): CursorAbstract|array
  {
    $cond = static::addCond($cond);
    $sort = static::addPosition($sort);

    return Cache::single(
      ['all', static::class, $cond, $sort, $count, $offset, $map],
      fn() => static::fetchAll($cond, $sort, $count, $offset, $map)
    );
  }

  /**
   * @param array|string|int $cond
   * @param array $sort
   * @param int|null $count
   * @param int|null $offset
   * @param array $map
   * @return CursorAbstract|array|static[]
   * @throws CallUndefinedMethod
   */
  public static function all(
    array|string|int $cond = [],
    array            $sort = [],
    ?int             $count = null,
    ?int             $offset = null,
    array            $map = []
  ): CursorAbstract|array
  {
    return static::fetchAll(static::addCond($cond), static::addPosition($sort), $count, $offset, $map);
  }

  /**
   * @param array|string|int $cond
   * @param array $sort
   * @param int|null $count
   * @param int|null $offset
   * @param array $map
   * @return CursorAbstract|array|static[]
   * @throws CallUndefinedMethod
   */
  public static function fetchAll(
    array|string|int $cond = [],
    array            $sort = [],
    ?int             $count = null,
    ?int             $offset = null,
    array            $map = []
  ): CursorAbstract|array
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public static function quantity(array|string|int $cond = []): int
  {
    return static::count(static::addCond($cond));
  }

  public static function count(array|string|int $cond = []): int
  {
    return self::__callStatic(__FUNCTION__, func_get_args());
  }

  public function getModelClassName(): string
  {
    return get_class($this);
  }

  public function __get(string $name): mixed
  {
    return $this->getDocument()->__get($name);
  }

  public function __set(string $name, mixed $value): void
  {
    $this->getDocument()->__set($name, $value);
  }

  public function getDocument(): DocumentAbstract
  {
    if (!$this->document) {

      $driver = Config::getConfig()['driver'];
      $documentClassName = null;

      if ($driver === 'mongodb') {
        $documentClassName = Document::class;

      } else if ($driver === 'mysql') {
        $documentClassName = Driver\Mysql\Document::class;
      }

      if ($documentClassName) {
        $this->document = new $documentClassName($this);
      }
    }
    return $this->document;
  }

  public function __isset(string $name): bool
  {
    return $this->getDocument()->__isset($name);
  }

  public function offsetExists(mixed $offset): bool
  {
    return $this->getDocument()->offsetExists($offset);
  }

  public function offsetGet(mixed $offset): mixed
  {
    return $this->getDocument()->offsetGet($offset);
  }

  public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->getDocument()->offsetSet($offset, $value);
  }

  public function offsetUnset(mixed $offset): void
  {
    $this->getDocument()->offsetUnset($offset);
  }

  public function getData(): array
  {
    return self::__call(__FUNCTION__, func_get_args());
  }

  public function __call(string $methodName, array $args)
  {
    $document = $this->getDocument();
    $method = [$document, $methodName];

    if (is_callable($method)) {
      return call_user_func_array($method, $args);
    }

    $driver = self::getDriver();
    $method = [$driver, $methodName];

    if (is_callable($method)) {
      $driver->setModel($this);
      return call_user_func_array($method, $args);
    }

    throw new CallUndefinedMethod($this->getMeta()->getCollection(), $methodName);
  }

  public function getMeta(): Meta
  {
    return $this->meta;
  }

  public static function meta(): Meta
  {
    return new Meta(new static());
  }

  public function populate(array $data, bool $fromSet = true): void
  {
    self::__call(__FUNCTION__, func_get_args());
  }

  public function save(): int
  {
    return self::__call(__FUNCTION__, func_get_args());
  }

  public function toArray(): array
  {
    return $this->getDocument()->toArray();
  }

  public function remove(): void
  {
    self::__call(__FUNCTION__, []);
  }

  public static function reflectSchema(): void
  {
    self::__callStatic(__FUNCTION__, []);
  }

  public static function batchRemove(array|string|int $cond = [], ?int $limit = null): int
  {
    return self::__callStatic('remove', func_get_args());
  }
}
