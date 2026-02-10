<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Model\ModelAbstract;

/**
 * @collection AirOpenAiLog
 *
 * @property string $id
 *
 * @property string $key
 * @property string $model
 *
 * @property array $input
 * @property array $output
 *
 * @property integer $inputTokens
 * @property integer $outputTokens
 * @property integer $totalTokens
 *
 * @property integer $createdAt
 */
class OpenAiLog extends ModelAbstract
{
  public static function add(string $key, string $model, array $input, array $output): void
  {
    $inputTokens = self::calculateTokens($input);
    $outputTokens = self::calculateTokens($output);

    (new self([
      'key' => $key,
      'model' => $model,
      'input' => $input,
      'output' => $output,
      'inputTokens' => $inputTokens,
      'outputTokens' => $outputTokens,
      'totalTokens' => $inputTokens + $outputTokens
    ]))->save();
  }

  public static function calculateTokens(array $input): int
  {
    $input = json_encode($input);
    $words = preg_split('/\s+/u', trim($input), -1, PREG_SPLIT_NO_EMPTY);
    $wordCount = count($words);

    $tokens = ($wordCount * 75) / 100;

    return (int)ceil($tokens);
  }
}