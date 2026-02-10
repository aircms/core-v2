<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection EmailSettings
 *
 * @property string $id
 *
 * @property string $smtpServer
 * @property string $smtpPort
 * @property string $smtpProtocol
 * @property string $smtpAddress
 * @property string $smtpPassword
 * @property string $smtpFromName
 * @property string $smtpFromAddress
 *
 * @property string $resendApiKey
 * @property string $resendFromEmail
 * @property string $resendFromName
 *
 * @property boolean $emailQueueEnabled
 * @property string $gateway
 */
class EmailSettings extends ModelAbstract
{
  const string TSL = 'tls';
  const string SSL = 'ssl';

  const string GATEWAY_SMTP = 'smtp';
  const string GATEWAY_RESEND = 'resend';
}