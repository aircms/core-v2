<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection EmailQueue
 *
 * @property string $id
 * @property integer $when
 * @property string $toAddress
 * @property string toName
 * @property string $subject
 * @property string $body
 * @property array $attachments
 *
 * @property string $status
 * @property string $debugOutput
 */
class EmailQueue extends ModelAbstract
{
  const string STATUS_NEW = 'new';
  const string STATUS_SUCCESS = 'success';
  const string STATUS_FAIL = 'fail';
}