<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection SmsQueue
 *
 * @property string $id
 * @property integer $when
 * @property string $toAddress
 * @property string $message
 * @property string $status
 * @property string $debugOutput
 */
class SmsQueue extends ModelAbstract
{
  const string STATUS_NEW = 'new';
  const string STATUS_SUCCESS = 'success';
  const string STATUS_FAIL = 'fail';
}