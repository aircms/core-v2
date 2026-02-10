<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Control;
use Air\Crud\Controller\MultipleHelper\Accessor\Filter;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Locale;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Type\FaIcon;

class DeepSeekLog extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_DEEPSEEK_LOG;
  }

  protected function getFilter(): array
  {
    return [
      Filter::search(by: ['model', 'key']),
      Filter::createdAt()
    ];
  }

  protected function getBlock(): ?string
  {
    $totalTokens = 0;
    $inputTokens = 0;
    $outputTokens = 0;

    foreach (\Air\Crud\Model\DeepSeekLog::fetchAll($this->getConditions()) as $deepSeekLog) {
      $totalTokens += $deepSeekLog->totalTokens;
      $inputTokens += $deepSeekLog->inputTokens;
      $outputTokens += $deepSeekLog->outputTokens;
    }

    return div(content: horizontal([
      badge($inputTokens, LIGHT),
      ' + ',
      badge($outputTokens, DARK),
      ' = ',
      badge($totalTokens),
    ]));
  }

  protected function getHeader(): array
  {
    return [
      Header::source('Model', function (\Air\Crud\Model\DeepSeekLog $deepSeekLog) {
        return vertical([
          badge($deepSeekLog->model),
          badge($deepSeekLog->key, DARK)
        ]);
      }),
      Header::source('Tokens', function (\Air\Crud\Model\DeepSeekLog $deepSeekLog) {
        return horizontal([
          badge($deepSeekLog->inputTokens, LIGHT),
          ' + ',
          badge($deepSeekLog->outputTokens, DARK),
          ' = ',
          badge($deepSeekLog->totalTokens),
        ]);
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

  public function details(\Air\Crud\Model\DeepSeekLog $id): string
  {
    return card(content: [
      div(class: 'd-flex mb-2 gap-2 align-items-start justify-content-between', content: [
        div(class: 'd-flex gap-2 align-items-start', content: [
          badge($id->model),
          badge($id->key, DARK),
          horizontal([
            badge($id->inputTokens, LIGHT),
            ' + ',
            badge($id->outputTokens, DARK),
            ' = ',
            badge($id->totalTokens),
          ])
        ]),
        badge(date('r', $id->createdAt), LIGHT)
      ]),
      div(class: 'mt-3', content: div(class: 'row', content: [
        div(class: 'col-6', content: div(class: 'card bg-body p-3', content: json($id->input))),
        div(class: 'col-6', content: div(class: 'card bg-body p-3', content: json($id->output))),
      ]))
    ]);
  }
}
