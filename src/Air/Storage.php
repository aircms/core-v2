<?php

declare(strict_types=1);

namespace Air;

use Air\Core\Front;
use Air\Http\Request;
use Air\Http\Response;
use Air\Type\File;
use Exception;
use Throwable;

class Storage
{
  public static function createFolder(
    string $path,
    string $name,
    bool   $recursive = false,
    bool   $sharding = false
  ): string
  {
    if ($sharding) {
      $hash = md5(microtime());

      $dir1 = substr($hash, 0, 2);
      $dir2 = substr($hash, 2, 2);

      $name = $name . '/' . $dir1 . '/' . $dir2;
      $recursive = true;
    }

    $response = self::action('createFolder', [
      'path' => $path,
      'name' => $name,
      'recursive' => $recursive
    ]);

    if (!$response->isOk()) {
      throw new Exception($response->body['message']);
    }

    return $response->body['src'];
  }

  public static function deleteFolder(string $path): bool
  {
    return self::action('deleteFolder', ['path' => $path])->isOk();
  }

  public static function uploadByUrl(string $path, string $url, bool $sharding = false): File
  {
    if ($sharding) {
      $path = self::createFolder('/', $path, true, $sharding);
    }

    $response = self::action('uploadByUrl', [
      'path' => $path,
      'url' => $url,
    ]);

    if (!$response->isOk()) {
      throw new Exception($response->body['message']);
    }

    return new File($response->body);
  }

  public static function deleteFile(string $path): bool
  {
    return self::action('deleteFile', ['path' => $path])->isOk();
  }

  /**
   * @param string $path
   * @param array $files
   * @param bool $sharding
   * @return File[]
   * @throws Exception
   */
  public static function uploadFiles(string $path, array $files, bool $sharding = false): array
  {
    if ($sharding) {
      $path = self::createFolder('/', $path, true, $sharding);
    }

    $storageConfig = Front::getInstance()->getConfig()['air']['storage'];
    $url = $storageConfig['url'] . '/api/uploadFile';

    $response = Request::run($url, [
      'body' => [
        'path' => $path,
        'key' => $storageConfig['key'],
      ],
      'files' => [
        'files' => $files
      ]
    ]);

    if (!$response->isOk()) {
      throw new Exception($response->body['message']);
    }

    return array_map(fn(array $file) => new File($files), $response->body);
  }

  /**
   * @param string $path
   * @param array $datum
   * @param bool $sharding
   * @return File[]
   * @throws Exception
   */
  public static function uploadDatum(string $path, array $datum, bool $sharding = false): array
  {
    $path = self::createFolder('/', $path, true, $sharding);

    ini_set('memory_limit', -1);

    $response = self::action('uploadDatum', [
      'path' => $path,
      'datum' => $datum,
    ]);

    if (!$response->isOk()) {
      throw new Exception($response->body['message'] ?? 'Unknown exception');
    }

    return Map::multiple($response->body, function (array $file) {
      return new File($file);
    });
  }

  /**
   * @param string $path
   * @param array $datum
   * @param bool $sharding
   * @return File[]
   * @throws Exception
   */
  public static function uploadBase64Datum(string $path, array $datum, bool $sharding = false): array
  {
    foreach ($datum as $index => $image) {
      $datum[$index] = [
        'type' => 'base64',
        'data' => $image
      ];
    }
    return self::uploadDatum($path, $datum, $sharding);
  }

  /**
   * @param array|string $paths
   * @return File[]|File
   * @throws Exception
   */
  public static function info(array|string $paths): array|File
  {
    $response = self::action('info', [
      'paths' => $paths,
    ]);

    $files = [];
    foreach ($response->body as $file) {
      $files[] = new File($file);
    }

    return is_array($paths) ? $files : $files[0];
  }

  public static function refactor(
    File $file,
    ?int $width = null,
    ?int $height = null,
    ?int $quality = null,
  ): bool
  {
    if (!$file->isImage()) {
      return false;
    }

    try {
      return self::action('refactor', [
        'path' => $file->src,
        'width' => $width,
        'height' => $height,
        'quality' => $quality,
      ])->isOk();

    } catch (Throwable) {

    }
    return false;
  }

  public static function annotation(
    string $folder,
    string $fileName,
    string $title,
    string $backColor,
    string $frontColor
  ): ?File
  {
    $response = self::action('annotation', [
      'folder' => $folder,
      'fileName' => $fileName,
      'title' => $title,
      'backColor' => $backColor,
      'frontColor' => $frontColor
    ]);

    if (!$response->isOk()) {
      return null;
    }

    return new File($response->body);
  }

  public static function action(string $endpoint, ?array $params = []): Response
  {
    $storageConfig = Front::getInstance()->getConfig()['air']['storage'];

    $url = $storageConfig['url'] . '/api/' . $endpoint;
    $params['key'] = $storageConfig['key'];

    return Request::run($url, [
      'body' => $params,
      'method' => Request::POST
    ]);
  }

  public static function isImage(string|array $input): bool
  {
    try {
      if (is_array($input)
        && str_starts_with($input['src'], Front::getInstance()->getConfig()['air']['storage']['url'])
        && str_starts_with($input['thumbnail'], Front::getInstance()->getConfig()['air']['storage']['url'])) {
        return true;
      }
    } catch (Throwable) {
      return false;
    }

    try {
      if (stripos($input, 'data:') === 0) {
        $commaPos = strpos($input, ',');
        if ($commaPos === false) {
          return false;
        }
        $input = substr($input, $commaPos + 1);
      }

      $input = preg_replace('/\s+/', '', $input);

      $binary = base64_decode($input, true);
      if ($binary === false || $binary === '') {
        return false;
      }

      return !!getimagesizefromstring($binary);

    } catch (Throwable) {
      return false;
    }
  }
}
