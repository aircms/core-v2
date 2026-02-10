<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirBilling
 *
 * @property string $id
 *
 * @property boolean $liqPayEnabled
 * @property string $liqPayPublicKey
 * @property string $liqPayPrivateKey
 *
 * @property boolean $liqPaySandboxEnabled
 * @property string $liqPaySandboxPublicKey
 * @property string $liqPaySandboxPrivateKey
 *
 * @property boolean $monoPayEnabled
 * @property string $monoPayKey
 *
 * @property boolean $monoPaySandboxEnabled
 * @property string $monoPaySandboxKey
 */
class Billing extends ModelAbstract
{
}