<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Crud\Model\DeepSeek;
use Air\Crud\Nav\Nav;
use Air\Model\ModelAbstract;
use Air\Crud\Model\OpenAi;
use Air\Type\File;

trait Magic
{
  const array TYPES = [
    'title' => 'String. A general name reflecting the meaning, could be H1 from the document.',
    'subTitle' => 'String. A complementary line expanding on the title',
    'description' => 'String. A concise text giving more details about the content, purpose, or main idea. Not moe than 160 symbols',
    'meta' => [
      'title' => 'String. META - title. Maximum 60 symbols',
      'description' => 'String. META - description. Maximum 160 symbols',
      'ogTitle' => 'String. Open Graph - title. Maximum 60 symbols',
      'ogDescription' => 'String. Open Graph - description. Maximum 160 symbols',
    ],
    'richContent' => [
      [
        'type' => 'html',
        'value' => 'String. HTML content of the article - it is important to adhere to the H2, H3, H4 tags (H1 is not necessary), and the rest of the text should be in the P tag, lists should be in the UL tag, each section of the article (from H2 inclusive to the next H2) should be in a separate \'richContent\' object also with the value type=\'html\''
      ]
    ],
    'content' => 'String. HTML content of the article - it is important to adhere to the H2, H3, H4 tags (H1 is not necessary), and the rest of the text should be in the P tag, lists should be in the UL tag.',
  ];

  protected function getStructure(): ?array
  {
    if (!$this->isOpenAiEnabled() && $this->isDeepSeekEnabled()) {
      return null;
    }

    /** @var ModelAbstract $model */
    $model = $this->getModelClassName();
    $meta = $model::meta();

    $structure = [];

    foreach (self::TYPES as $field => $description) {
      if ($meta->hasProperty($field)) {
        $structure[$field] = $description;
      }
    }

    return count($structure) ? $structure : null;
  }

  protected function isOpenAiEnabled(): bool
  {
    if (!Nav::getSettingsItem(Nav::SETTINGS_OPENAI)) {
      return false;
    }

    return OpenAi::singleOne()?->enabled ?? false;
  }

  protected function isDeepSeekEnabled(): bool
  {
    if (!Nav::getSettingsItem(Nav::SETTINGS_DEEPSEEK)) {
      return false;
    }

    return DeepSeek::singleOne()?->enabled ?? false;
  }

  protected function getPrompt(): string
  {
    return implode("\n", [
      'You are a helper who parses articles and returns strictly structured data in JSON format.',
      '',
      'You are provided with the text of an article.',
      'Your task is to parse it and return a JSON object **strictly according to the structure specified below**.',
      '',
      'Response format:',
      'Reply with **valid JSON only**, without unnecessary text, comments, explanations, Markdown blocks, or quotation marks around the entire object.',
      '',
      json_encode($this->getStructure()),
    ]);
  }

  public function deepSeek(string $prompt): array
  {
    set_time_limit(0);

    $deepSeek = new \Air\ThirdParty\DeepSeek();
    $deepSeek->addMessage(implode("\n", [
      'In this section text of my article',
      $prompt
    ]));

    return $deepSeek->message($this->getPrompt(), true);
  }

  public function openAi(File $file): array
  {
    set_time_limit(0);

    $openAi = new \Air\ThirdParty\OpenAi();
    $openAi->addInput($this->getPrompt(), $file);

    return $openAi->ask(true);
  }
}