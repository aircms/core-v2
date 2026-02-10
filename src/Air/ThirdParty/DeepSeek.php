<?php

declare(strict_types=1);

namespace Air\ThirdParty;

use Air\Crud\Model\DeepSeekLog;
use Air\Http\Request;
use Exception;

class DeepSeek
{
  private array $messages = [];

  public function __construct(
    private ?string $key = null,
    private ?string $model = null,
  )
  {
    if (!$this->key || !$this->model) {
      $settings = \Air\Crud\Model\DeepSeek::one();
      $this->key = $settings->key;
      $this->model = $settings->model;
    }
  }

  public function addMessage(string $content, string $role = 'user'): void
  {
    $this->messages[] = [
      'role' => $role,
      'content' => $content
    ];
  }

  public function message(string $question, ?bool $json = false): mixed
  {
    if ($json) {
      $this->messages[] = [
        'role' => 'system',
        'content' => 'You are a helpful assistant designed to output JSON.'
      ];
    }

    $this->messages[] = [
      'role' => 'user',
      'content' => $question
    ];

    $body = [
      'model' => $this->model,
      'messages' => $this->messages,
      'stream' => false
    ];

    if ($json) {
      $body['response_format'] = ['type' => 'json_object'];
    }

    $answer = (new Request())
      ->url('https://api.deepseek.com/chat/completions')
      ->method(Request::POST)
      ->type('json')
      ->bearer($this->key)
      ->timeout(30000)
      ->body($body)
      ->do()
      ->body;

    $message = $answer['choices'][0]['message'] ?? false;

    if (!$message) {
      throw new Exception('OpenAi error:' . $answer['error']['type'] . '. Message: ' . $answer['error']['message']);
    }

    if ($json) {
      $response = json_decode($message['content'], true);
    } else {
      $response = $message['content'];
    }

    DeepSeekLog::add($this->key, $this->model, $body, (array)$response);

    return $response;
  }

  public function balance(): array
  {
    $balance = (new Request())
      ->url('https://api.deepseek.com/user/balance')
      ->method(Request::GET)
      ->type('json')
      ->bearer($this->key)
      ->do()
      ->body;

    return [
      'isAvailable' => $balance['is_available'],
      'balance' => $balance['balance_infos'][0]['total_balance'] . ' ' . $balance['balance_infos'][0]['currency'],
    ];
  }

  public static function ask(string $question, ?bool $json = false): mixed
  {
    return (new static())->message($question, $json);
  }
}