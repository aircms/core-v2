<?php

declare(strict_types=1);

namespace Air\ThirdParty\GoogleOAuth;

use Air\Type\TypeAbstract;

class Profile extends TypeAbstract
{
  public ?string $email = null;
  public ?string $firstName = null;
  public ?string $secondName = null;
  public ?string $image = null;

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(?string $email): void
  {
    $this->email = $email;
  }

  public function getFirstName(): ?string
  {
    return $this->firstName;
  }

  public function setFirstName(?string $firstName): void
  {
    $this->firstName = $firstName;
  }

  public function getSecondName(): ?string
  {
    return $this->secondName;
  }

  public function setSecondName(?string $secondName): void
  {
    $this->secondName = $secondName;
  }

  public function getImage(): ?string
  {
    return $this->image;
  }

  public function setImage(?string $image): void
  {
    $this->image = $image;
  }
}