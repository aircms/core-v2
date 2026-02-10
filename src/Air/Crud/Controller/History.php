<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Filter;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Locale;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Throwable;

/**
 * @mod-sorting {"createdAt": -1}
 */
class History extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_ADMINISTRATORS_HISTORY;
  }

  public function getFilter(): array
  {
    return [
      Filter::search(),
      Filter::select('Action', 'type', options: [
        \Air\Crud\Model\History::TYPE_READ_TABLE,
        \Air\Crud\Model\History::TYPE_READ_ENTITY,
        \Air\Crud\Model\History::TYPE_CREATE_ENTITY,
        \Air\Crud\Model\History::TYPE_WRITE_ENTITY,
      ])
    ];
  }

  public function getHeader(): array
  {
    return [
      Header::source(Locale::t('User'), fn(\Air\Crud\Model\History $adminHistory) => $adminHistory->admin['login'] ?? $adminHistory->admin[0]),
      Header::createdAt(),
      Header::source(Locale::t('Action'), fn(\Air\Crud\Model\History $adminHistory) => badge(
        match ($adminHistory->type) {
          \Air\Crud\Model\History::TYPE_READ_TABLE => Locale::t('Table view'),
          \Air\Crud\Model\History::TYPE_READ_ENTITY => Locale::t('Record details'),
          \Air\Crud\Model\History::TYPE_WRITE_ENTITY => Locale::t('Edit record'),
          \Air\Crud\Model\History::TYPE_CREATE_ENTITY => Locale::t('Creating record'),
          default => Locale::t('Unknown'),
        },
        match ($adminHistory->type) {
          \Air\Crud\Model\History::TYPE_READ_TABLE, \Air\Crud\Model\History::TYPE_READ_ENTITY => INFO,
          \Air\Crud\Model\History::TYPE_WRITE_ENTITY, \Air\Crud\Model\History::TYPE_CREATE_ENTITY => WARNING,
          default => DANGER,
        }
      )),
      Header::source(Locale::t('Section'), function (\Air\Crud\Model\History $adminHistory) {
        $content = [label($adminHistory->section)];
        try {
          $fields = [];
          foreach ($adminHistory->entity as $values) {
            if (is_string($values)) {
              $fields[] = $values;
            }
          }
          $content[] .= implode(', ', $fields);
        } catch (Throwable) {
        }
        return vertical($content);
      })
    ];
  }
}
