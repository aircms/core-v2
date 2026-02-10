<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection SmsSettings
 *
 * @property string $id
 * @property string $gateway
 *
 * @property string $smstoApiKey
 * @property string $smstoSenderId
 *
 * @property string $gatewayapiApiKey
 * @property string $gatewayapiSender
 *
 * @property boolean $smsQueueEnabled
 */
class SmsSettings extends ModelAbstract
{
  const string GATEWAY_SMSTO = 'smsto';
  const string GATEWAY_GATEWAYAPI = 'gatewayapi';
}