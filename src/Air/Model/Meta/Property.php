<?php

declare(strict_types=1);

namespace Air\Model\Meta;

class Property
{
  private ?string $type = null;
  private ?string $rawType = null;

  private ?string $name = null;

  private bool $isMultiple = false;

  private bool $isModel = false;
  private bool $isTypeAbstract = false;

  private bool $isEnum = false;
  private array $enum = [];

  public function getType(): string
  {
    return $this->type;
  }

  public function setType(string $type): void
  {
    $this->type = $type;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function isMultiple(): ?bool
  {
    return $this->isMultiple;
  }

  public function setIsMultiple(?bool $isMultiple): void
  {
    $this->isMultiple = $isMultiple;
  }

  public function isModel(): ?bool
  {
    return $this->isModel;
  }

  public function setIsModel(?bool $isModel): void
  {
    $this->isModel = $isModel;
  }

  public function getRawType(): ?string
  {
    return $this->rawType;
  }

  public function setRawType(?string $rawType): void
  {
    $this->rawType = $rawType;
  }

  public function isEnum(): ?bool
  {
    return $this->isEnum;
  }

  public function setIsEnum(?bool $isEnum): void
  {
    $this->isEnum = $isEnum;
  }

  public function getEnum(): ?array
  {
    return $this->enum;
  }

  public function setEnum(?array $enum): void
  {
    $this->enum = $enum;
  }

  public function isTypeAbstract(): bool
  {
    return $this->isTypeAbstract;
  }

  public function setIsTypeAbstract(bool $isTypeAbstract): void
  {
    $this->isTypeAbstract = $isTypeAbstract;
  }
}