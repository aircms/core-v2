<?php

declare(strict_types=1);

namespace Air\ThirdParty;

use Air\Crud\Model\OpenAiLog;
use Air\Http\Request;
use Air\Log;
use Air\Type\File;
use Exception;

class OpenAi
{
  private array $inputs = [];

  public function __construct(
    private ?string $key = null,
    private ?string $model = null,
  )
  {
    if (!$this->key) {
      $settings = \Air\Crud\Model\OpenAi::one();

      $this->key = $settings->key;
      $this->model = $settings->model;
    }
  }

  public function addInput(string $question, ?File $file = null, string $role = 'user'): static
  {
    $item = [
      'role' => $role,
      'content' => [
        [
          'type' => 'input_text',
          'text' => $question
        ]
      ]
    ];
    if ($file) {
      $item['content'][] = [
        'type' => 'input_file',
        'file_url' => $file->getSrc(),
      ];
    }

    $this->inputs[] = $item;
    return $this;
  }

  public function ask(?bool $json = false): mixed
  {
    $body = [
      'model' => $this->model,
      'input' => $this->inputs,
    ];

    if ($json) {
      $body['text'] = [
        'format' => ['type' => 'json_object']
      ];
    }

    $answer = (new Request())
      ->url('https://api.openai.com/v1/responses')
      ->method(Request::POST)
      ->type('json')
      ->bearer($this->key)
      ->timeout(30000)
      ->body($body)
      ->do()
      ->body;

    $message = null;
    foreach ($answer['output'] as $output) {
      if ($output['type'] === 'message') {
        if ($json) {
          $message = json_decode($output['content'][0]['text'], true);
        } else {
          $message = $output['content'][0]['text'];
        }
      }
    }

    OpenAiLog::add($this->key, $this->model, $body, (array)$message);

    if (!$message) {
      Log::error('OpenAi error', [
        'request' => $body,
        'response' => $answer
      ]);
      throw new Exception('OpenAi error');
    }

    return $message;
  }
}