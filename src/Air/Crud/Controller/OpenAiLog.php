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

class OpenAiLog extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_OPENAI_LOG;
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

    foreach (\Air\Crud\Model\OpenAiLog::fetchAll($this->getConditions()) as $openAiLog) {
      $totalTokens += $openAiLog->totalTokens;
      $inputTokens += $openAiLog->inputTokens;
      $outputTokens += $openAiLog->outputTokens;
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
      Header::source('Model', function (\Air\Crud\Model\OpenAiLog $openAiLog) {
        return vertical([
          badge($openAiLog->model),
          badge($openAiLog->key, DARK)
        ]);
      }),
      Header::source('Tokens', function (\Air\Crud\Model\OpenAiLog $openAiLog) {
        return horizontal([
          badge($openAiLog->inputTokens, LIGHT),
          ' + ',
          badge($openAiLog->outputTokens, DARK),
          ' = ',
          badge($openAiLog->totalTokens),
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

  public function details(\Air\Crud\Model\OpenAiLog $id): string
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
