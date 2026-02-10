<?php

declare(strict_types=1);

namespace Air\ThirdParty;

use Air\Http\Request;
use Air\ThirdParty\Gatewayapi\Exception\InvalidResponse;
use Air\ThirdParty\Gatewayapi\Response;

class Gatewayapi
{
  public function __construct(
    protected ?string $key = null,
    protected ?string $sender = null,
  )
  {
  }

  public function send(string $to, string $message): Response
  {
    $data = [
      'message' => $message,
      'recipients' => [['msisdn' => $to]],
      'sender' => $this->sender,
    ];

    $sms = (new Request())
      ->url('https://gatewayapi.eu/rest/mtsms')
      ->headers(['authorization' => 'Token ' . $this->key])
      ->timeout(2)
      ->method(Request::POST)
      ->type('json')
      ->body($data)
      ->do();

    if (!$sms->isOk() || !isset($sms->body['ids'])) {
      throw new InvalidResponse([
        'request' => $data,
        'response' => $sms->body
      ]);
    }

    return new Response($sms->body);
  }
}
