<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirLogs
 *
 * @property string $id
 *
 * @property string $title
 * @property array $data
 * @property string $level
 * @property integer $createdAt
 */
class Log extends ModelAbstract
{
  const string INFO = 'info';
  const string ERROR = 'error';
}