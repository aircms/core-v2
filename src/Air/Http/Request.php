<?php

declare(strict_types=1);

namespace Air\Http;

use Exception;
use function Air\dd;

class Request
{
  public static bool $debug = false;

  const string GET = 'GET';
  const string POST = 'POST';

  const string CONTENT_TYPE_JSON = 'application/json';
  const string CONTENT_TYPE_FORM_DATA = 'multipart/form-data';
  const string CONTENT_TYPE_X_FORM_DATA = 'application/x-www-form-urlencoded';

  public string $url;
  public string $method = self::GET;
  public array $get = [];
  public array $headers = [];
  public array $cookies = [];
  public ?string $bearer = null;
  public mixed $body = null;
  public int $timeout = 30;
  public array $files = [];

  public function url(string $url): self
  {
    $this->url = $url;
    return $this;
  }

  public function method(string $method): self
  {
    $this->method = $method;
    return $this;
  }

  public function get(array $get): self
  {
    $this->get = [...$this->get, ...$get];
    return $this;
  }

  public function headers(array $header): self
  {
    $this->headers = [...$this->headers, ...$header];
    return $this;
  }

  public function cookies(array $cookies): self
  {
    $this->cookies = [...$this->cookies, ...$cookies];
    return $this;
  }

  public function bearer(string $bearer): self
  {
    $this->bearer = $bearer;
    return $this;
  }

  public function body(mixed $body): self
  {
    $this->body = $body;
    return $this;
  }

  public function type(string $type): self
  {
    $types = [
      'json' => self::CONTENT_TYPE_JSON,
      'formData' => self::CONTENT_TYPE_FORM_DATA,
      'xFormData' => self::CONTENT_TYPE_X_FORM_DATA,
    ];

    $this->headers['content-type'] = $types[$type] ?? $type;
    return $this;
  }

  public function timeout(int $timeout): self
  {
    $this->timeout = $timeout;
    return $this;
  }

  public function file(string $name, string|array $fileOrFiles): self
  {
    $this->files[$name] = $fileOrFiles;
    return $this;
  }

  public function files(array $files): self
  {
    foreach ($files as $key => $value) {
      $this->file($key, $value);
    }
    return $this;
  }

  public function do(): Response
  {
    $ch = curl_init();

    $url = $this->url;
    if (count($this->get)) {
      $url .= '?' . http_build_query($this->get);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $headers = $this->headers;

    if ($this->bearer) {
      $headers['Authorization'] = 'Bearer ' . $this->bearer;
    }

    if (count($this->files)) {
      foreach ($this->files as $name => $fileOrFiles) {
        if (is_array($fileOrFiles)) {
          foreach ($fileOrFiles as $index => $file) {
            $this->body[$name . '[' . $index . ']'] = curl_file_create($file);
          }
        } else {
          $this->body[$name] = curl_file_create($fileOrFiles);
        }
      }
      $this->method = self::POST;
      $headers['content-type'] = 'multipart/form-data';
    }

    if (count($headers)) {
      $_headers = [];
      foreach ($headers as $name => $header) {
        $_headers[] = ucfirst($name) . ": " . $header;
      }
      curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
    }

    if ($this->method === self::POST) {
      $body = self::convertDataToContentType(
        $headers['content-type'] ?? '',
        $this->body
      );
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

    if (self::$debug) {
      var_export([
        "method" => $this->method,
        "url" => $url,
        "headers" => $headers,
        "body" => $body ?? null,
      ]);
      die();
    }

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
      throw new Exception('Curl error: ' . curl_error($ch));
    }

    return new Response($response);
  }

  public static function convertDataToContentType(string $contentType, mixed $data): mixed
  {
    return match ($contentType) {
      'application/json' => json_encode($data),
      'multipart/form-data' => $data,
      default => http_build_query($data ?? []),
    };
  }

  public static function run(string $url, array $options = []): Response
  {
    $notAllowedMethods = [
      'do',
      'convertDataToContentType',
      '__callStatic',
      'run'
    ];

    $request = new self();
    $request->url($url);

    foreach ($options as $key => $value) {
      if (in_array($key, $notAllowedMethods)) {
        throw new Exception("Method $key is now allowed");
      }

      if (method_exists($request, $key)) {
        $request->{$key}($value);
      }
    }

    return $request->do();
  }

  public static function postJson(string $url, mixed $data = null): Response
  {
    return self::run($url, [
      'method' => self::POST,
      'type' => 'json',
      'body' => $data
    ]);
  }

  public static function getQuery(string $url, array $get = []): Response
  {
    return self::run($url, [
      'get' => $get
    ]);
  }

  public static function postQuery(string $url, array $get = []): Response
  {
    return self::run($url, [
      'method' => self::POST,
      'type' => 'json',
      'get' => $get
    ]);
  }

  public static function fetch(
    string $url,
    ?array $get = null,
    ?array $headers = null,
  ): Response
  {
    $request = new self();
    $request->method(self::GET);
    $request->url($url);

    if ($get) {
      $request->get($get);
    }

    if ($headers) {
      $request->headers($headers);
    }

    return $request->do();
  }

  public static function post(
    string  $url,
    ?array  $get = null,
    ?array  $body = null,
    ?array  $headers = null,
    ?string $type = self::CONTENT_TYPE_X_FORM_DATA
  ): Response
  {
    $request = new self();
    $request->method(self::POST);
    $request->url($url);

    if ($get) {
      $request->get($get);
    }

    if ($body) {
      $request->body($body);
    }

    if ($type) {
      $request->type($type);
    }

    if ($headers) {
      $request->headers($headers);
    }

    return $request->do();
  }
}