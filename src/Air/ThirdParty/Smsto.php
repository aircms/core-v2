<?php

declare(strict_types=1);

namespace Air\ThirdParty;

use Air\Http\Request;
use Air\Log;
use Air\ThirdParty\Smsto\Exception\InvalidResponse;
use Air\ThirdParty\Smsto\Response;
use Exception;
use Throwable;

class Smsto
{
  public function __construct(
    protected ?string $key = null,
    protected string  $senderId = 'SMSto'
  )
  {
  }

  public function send(
    string  $to,
    string  $message,
    bool    $bypassOptout = true,
    ?string $callbackUrl = null
  ): Response
  {
    $data = array_filter([
      'message' => $message,
      'to' => $to,
      'bypass_optout' => $bypassOptout,
      'sender_id' => $this->senderId,
      ...$callbackUrl ? ['callback_url' => $callbackUrl] : []
    ]);

    $smsto = (new Request())
      ->url('https://api.sms.to/sms/send')
      ->bearer($this->key)
      ->method(Request::POST)
      ->type('json')
      ->body($data)
      ->do();

    if (!$smsto->isOk() || !$smsto->body->success) {
      throw new InvalidResponse([
        'request' => $data,
        'response' => $smsto->body
      ]);
    }

    return new Response($smsto->body);
  }
}
