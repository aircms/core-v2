<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection SmsTemplate
 *
 * @property string $id
 *
 * @property string $url
 * @property string $message
 *
 * @property Language $language
 * @property boolean $enabled
 */
class SmsTemplate extends ModelAbstract
{
  public static function tpl(string $templateUrl): self
  {
    return self::one(['url' => $templateUrl]);
  }

  public static function template(string $templateUrl): self
  {
    return self::fetchOne(['url' => $templateUrl]);
  }
}