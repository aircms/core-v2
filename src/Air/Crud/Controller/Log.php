<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Core\Route;
use Air\Crud\Controller\MultipleHelper\Accessor\Control;
use Air\Crud\Controller\MultipleHelper\Accessor\Filter;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Controller\MultipleHelper\Accessor\HeaderButton;
use Air\Crud\Locale;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Type\FaIcon;

class Log extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_LOGS;
  }

  protected function getSorting(): array
  {
    return [
      'createdAt' => -1
    ];
  }

  protected function getHeaderButtons(): array
  {
    return [
      HeaderButton::item(Route::assembleRoute(controller: $this->getEntity(), action: 'clear'), title: 'Clear')
    ];
  }

  protected function getFilter(): array
  {
    return [
      Filter::search(by: ['title', 'search']),
      Filter::createdAt(),
      Filter::select(by: 'level', options: [
        \Air\Crud\Model\Log::INFO,
        \Air\Crud\Model\Log::ERROR,
      ])
    ];
  }

  protected function getHeader(): array
  {
    return [
      Header::source('Level', function (\Air\Crud\Model\Log $log) {
        return badge($log->level, match ($log->level) {
          \Air\Crud\Model\Log::INFO => PRIMARY,
          \Air\Crud\Model\Log::ERROR => DANGER,
          default => DARK
        });
      }),
      Header::source('Message', function (\Air\Crud\Model\Log $log) {
        return badge($log->title, DARK);
      }),
      Header::createdAt(),
    ];
  }

  protected function getControls(): array
  {
    return [
      Control::html(['action' => 'details'], FaIcon::ICON_INFO_CIRCLE, Locale::t('Details'))
    ];
  }

  public function clear(): void
  {
    \Air\Crud\Model\Log::batchRemove();

    if ($nav = Nav::getSettingsItem(Nav::SETTINGS_LOGS)) {
      $this->redirect(Route::assembleRoute(controller: $nav['alias']));
    }
  }

  public function details(\Air\Crud\Model\Log $id): string
  {
    return card(content: [
      div(class: 'd-flex mb-2 gap-2 justify-content-between', content: [
        div(class: 'd-flex gap-2', content: [
          badge($id->level, match ($id->level) {
            \Air\Crud\Model\Log::INFO => PRIMARY,
            \Air\Crud\Model\Log::ERROR => DANGER,
            default => DARK
          }),
          badge($id->title, DARK)
        ]),
        badge(date('r', $id->createdAt), LIGHT)
      ]),
      div(class: 'card bg-body p-3 mt-3', content: json($id->data))
    ]);
  }
}
