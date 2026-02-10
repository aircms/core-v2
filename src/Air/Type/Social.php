<?php

declare(strict_types=1);

namespace Air\Type;

use ReflectionClass;
use ReflectionClassConstant;

class Social extends TypeAbstract
{
  const string TYPE_FACEBOOK = 'facebook';
  const string TYPE_INSTAGRAM = 'instagram';
  const string TYPE_TIKTOK = 'tiktok';
  const string TYPE_YOUTUBE = 'youtube';
  const string TYPE_TWITTER = 'twitter';
  const string TYPE_LINKEDIN = 'linkedin';
  const string TYPE_PINTEREST = 'pinterest';
  const string TYPE_TELEGRAM = 'telegram';
  const string TYPE_WHATSAPP = 'whatsapp';

  public static function getTypes(): array
  {
    return (new ReflectionClass(self::class))
      ->getConstants(ReflectionClassConstant::IS_PUBLIC);
  }

  public ?string $type = null;
  public ?string $link = null;

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(?string $type): void
  {
    $this->type = $type;
  }

  public function getLink(): ?string
  {
    return $this->link;
  }

  public function setLink(?string $link): void
  {
    $this->link = $link;
  }
}