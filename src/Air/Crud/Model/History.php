<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirAdminHistory
 *
 * @property string $id
 * @property array $admin
 * @property string $type
 * @property string $section
 * @property array $entity
 * @property array $was
 * @property array $became
 *
 * @property string $search
 * @property integer $createdAt
 */
class History extends ModelAbstract
{
  const string TYPE_READ_TABLE = 'read-table';
  const string TYPE_READ_ENTITY = 'read-entity';
  const string TYPE_CREATE_ENTITY = 'create-entity';
  const string TYPE_WRITE_ENTITY = 'write-entity';
}