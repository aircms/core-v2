<?php

declare(strict_types=1);

namespace Air\Core\Request;

class File
{
  public ?string $name = null;
  public ?string $type = null;
  public ?string $tmpName = null;
  public int $error = 0;
  public int $size = 0;

  public function __construct(array $options = [])
  {
    foreach ($options as $key => $value) {
      if (is_callable([$this, 'set' . ucfirst($key)])) {
        $this->{'set' . ucfirst($key)}($value);
      }
    }
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): void
  {
    $this->type = $type;
  }

  public function getTmpName(): ?string
  {
    return $this->tmpName;
  }

  public function setTmpName(?string $tmpName): void
  {
    $this->tmpName = $tmpName;
  }

  public function getError(): int
  {
    return $this->error;
  }

  public function setError(int $error): void
  {
    $this->error = $error;
  }

  public function getSize(): int
  {
    return $this->size;
  }

  public function setSize(int $size): void
  {
    $this->size = $size;
  }

  public function toArray(): array
  {
    return [
      'name' => $this->getName(),
      'type' => $this->getType(),
      'tmpName' => $this->getTmpName(),
      'error' => $this->getError(),
      'size' => $this->getSize()
    ];
  }
}
