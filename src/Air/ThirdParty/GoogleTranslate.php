<?php

declare(strict_types=1);

namespace Air\ThirdParty;

use Air\Crud\Model\Language;
use Air\Http\Request;
use Air\Map;
use Exception;

class GoogleTranslate
{
  public function __construct(
    private ?string $key = null,
  )
  {
    if (!$this->key) {
      $settings = \Air\Crud\Model\GoogleTranslate::one();
      $this->key = $settings->key;
    }
  }

  public static function instance(?string $key = null): static
  {
    return new static($key);
  }

  public function translate(array $phrases, Language $language): array
  {
    $response = (new Request())
      ->type('json')
      ->method(Request::POST)
      ->url("https://translation.googleapis.com/language/translate/v2")
      ->get(['key' => $this->key])
      ->body([
        'format' => 'text',
        'q' => $phrases,
        'target' => $language->key,
      ])->do();

    if ($response->isOk()) {
      return Map::multiple($response->body['data']['translations'], function (array $translation) {
        return $translation['translatedText'];
      });
    }

    throw new Exception('GoogleTranslate error: ' . $response->body);
  }
}