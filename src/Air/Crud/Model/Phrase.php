<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirPhrase
 *
 * @property string $id
 *
 * @property string $key
 * @property string $value
 * @property boolean $isEdited
 *
 * @property Language $language
 */
class Phrase extends ModelAbstract
{
  private static ?array $phrases = null;

  public static function updatePhrases(): void
  {
    self::$phrases = [];

    foreach (self::all() as $phrase) {
      $phraseData = $phrase->getData();
      self::$phrases[$phraseData['key'] . $phraseData['language']] = $phraseData['value'];
    }
  }

  public static function t(string $key, ?Language $language = null): string
  {
    if (!self::$phrases) {
      self::updatePhrases();
    }

    if (!$language) {
      $language = Language::getLanguage();

      if (!$language) {
        return $key;
      }
    }

    $languageData = $language->getData();
    $key = trim($key);

    if (isset(self::$phrases[$key . $languageData['id']])) {
      return self::$phrases[$key . $languageData['id']];
    }

    foreach (Language::all() as $language) {
      $phrase = new self([
        'key' => $key,
        'value' => $key,
        'language' => $language->id,
        'isEdited' => $language->isDefault
      ]);
      $phrase->save();
    }
    self::updatePhrases();

    return $key;
  }
}