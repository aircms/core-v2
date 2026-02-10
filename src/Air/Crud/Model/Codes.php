<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirCodes
 *
 * @property string $id
 *
 * @property string $title
 * @property string $description
 *
 * @property integer $position
 * @property boolean $enabled
 */
class Codes extends ModelAbstract
{
}