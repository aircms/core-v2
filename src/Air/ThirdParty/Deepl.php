<?php

declare(strict_types=1);

namespace Air\ThirdParty;

use Air\Core\Front;
use Air\Crud\Model\Language;
use Air\Http\Request;
use Air\Map;
use Exception;

class Deepl
{
  public function __construct(
    private ?string $key = null,
    private ?bool   $isFree = null
  )
  {
    if (!$this->key) {
      $settings = \Air\Crud\Model\Deepl::one();

      $this->key = $settings->key;
      $this->isFree = $settings->isFree;
    }
  }

  public static function instance(?string $key = null, ?bool $isFree = null): static
  {
    return new static($key, $isFree);
  }

  public function translate(array $phrases, Language $language): array
  {
    $response = (new Request())
      ->type('json')
      ->method(Request::POST)
      ->url(match ($this->isFree) {
        true => 'https://api-free.deepl.com/v2/translate',
        false => 'https://api.deepl.com/v2/translate'
      })
      ->headers([
        'Authorization' => 'DeepL-Auth-Key ' . $this->key
      ])
      ->body([
        'text' => $phrases,
        'target_lang' => $language->key,
        'tag_handling' => 'html'
      ])->do();

    if ($response->isOk()) {
      return Map::multiple($response->body['translations'], function (array $translation) {
        return $translation['text'];
      });
    }

    throw new Exception('Deepl error: ' . $response->body['message']);
  }
}