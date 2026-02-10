<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirAdmin
 *
 * @property string $id
 *
 * @property string $login
 * @property string $password
 *
 * @property boolean $isRoot
 * @property array $permissions
 *
 * @property boolean $enabled
 */
class Admin extends ModelAbstract
{
}