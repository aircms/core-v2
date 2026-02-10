<?php

declare(strict_types=1);

namespace Air\Type;

use Air\Core\Loader;
use Air\Model\ModelAbstract;
use ReflectionProperty;
use Throwable;

abstract class TypeAbstract
{
  public function __construct(?array $data = [])
  {
    foreach (array_keys(get_class_vars(static::class)) as $var) {

      if (isset($data[$var])) {
        $value = $data[$var];

        list($type, $isArray) = $this->getPropertyType($var);

        if (class_exists($type)) {

          if ($isArray) {
            $this->{$var} = [];

            foreach ($value as $datum) {

              if (is_subclass_of($type, TypeAbstract::class)) {
                if (is_object($datum)) {
                  $this->{$var}[] = $datum;
                } else {
                  $this->{$var}[] = new $type($datum);
                }

              } elseif (is_subclass_of($type, ModelAbstract::class)) {
                if (is_object($datum)) {
                  $this->{$var}[] = $datum;
                } else {
                  $this->{$var}[] = $type::singleOne([(new $type)->getMeta()->getPrimary() => $datum]);
                }
              }
            }
          } else {
            if (is_subclass_of($type, TypeAbstract::class)) {
              if (is_object($value)) {
                $this->{$var} = $value;
              } else {
                $this->{$var} = new $type($value);
              }

            } elseif (is_subclass_of($type, ModelAbstract::class)) {
              if (is_object($value)) {
                $this->{$var} = $value;
              } else {
                $this->{$var} = $type::singleOne([(new $type)->getMeta()->getPrimary() => $value]);
              }
            }
          }
        } else {
          try {
            settype($value, $type);
          } catch (Throwable) {
          }

          $this->{$var} = $value;
        }
      }
    }
  }

  public function getPropertyType(string $property): ?array
  {
    $rp = new ReflectionProperty(static::class, $property);
    $type = $rp->getType()->getName();

    if (class_exists($type)) {
      return [$type, false];
    }

    try {
      $docBlocks = explode('/', str_replace('*', '', $rp->getDocComment()));
      foreach ($docBlocks as $docBlock) {
        if (str_contains($docBlock, '@var')) {
          $type = trim(str_replace('[]', '', explode('|', trim(str_replace('@var', '', $docBlock)))[0]));

          if (class_exists($type)) {
            return [$type, true];
          }

          $namespaces = Loader::getUsedNamespaces(static::class);

          if (class_exists($namespaces['namespace'] . '\\' . $type)) {
            $type = $namespaces['namespace'] . '\\' . $type;

          } else {
            foreach ($namespaces['uses'] as $namespace) {
              if (str_contains($namespace, $type)) {
                $type = $namespace;
                break;
              }
            }
          }
          if (class_exists($type)) {
            return [$type, true];
          }
        }
      }
    } catch (Throwable) {
    }

    $type = $rp->getType()->getName();

    return [$type, $type === 'array'];
  }

  public function toArray(): array
  {
    $array = [];
    foreach (get_class_vars(static::class) as $var => $value) {
      if (is_object($this->{$var})) {
        if (method_exists($this->{$var}, 'toArray')) {
          $value = $this->{$var}->toArray();
        } else {
          $value = (array)$this->{$var};
        }
      } else {
        $value = $this->{$var};
      }
      $array[$var] = $value;
    }
    return $array;
  }

  public function toRaw(): array
  {
    $array = [];

    foreach (get_class_vars(static::class) as $var => $value) {
      $value = $this->{$var};

      list($type, $isArray) = $this->getPropertyType($var);

      if (class_exists($type)) {

        if ($isArray && is_array($value)) {
          $array[$var] = [];
          foreach ($value as $datum) {
            if (is_subclass_of($type, TypeAbstract::class)) {
              if (is_object($datum)) {
                $array[$var][] = $datum->toRaw();
              } else {
                $array[$var][] = $datum;
              }

            } elseif (is_subclass_of($type, ModelAbstract::class)) {
              if (is_object($datum)) {
                $array[$var][] = $datum->{(new $type)->getMeta()->getPrimary()};
              } else {
                $array[$var][] = $datum;
              }
            }
          }
        } else {
          if (is_subclass_of($type, TypeAbstract::class)) {
            if (is_object($value)) {
              $array[$var] = $value->toRaw();
            } else {
              $array[$var] = $value;
            }

          } elseif (is_subclass_of($type, ModelAbstract::class)) {
            if (is_object($value)) {
              $array[$var] = $value->{(new $type)->getMeta()->getPrimary()};
            } else {
              $array[$var] = $value;
            }
          }
        }
      } else {
        $array[$var] = $value;
      }
    }
    return $array;
  }

  public function __toString(): string
  {
    return implode(', ', $this->toArray());
  }
}