<?php

namespace Air\Core;

use Air\Core;
use Air\Core\Request\File;
use Throwable;

class Request
{
  const string METHOD_GET = 'get';
  const string METHOD_POST = 'post';
  const string METHOD_PUT = 'put';
  const string METHOD_DELETE = 'delete';

  /**
   * @var \Air\Type\File[]
   */
  private array $files = [];
  private array $postParams = [];
  private array $getParams = [];
  private array $params = [];
  private array $headers = [];
  private ?string $method = null;
  private ?string $uri = null;
  private ?string $uriParams = null;
  private string|null $uriRequest = null;
  private ?string $domain = null;
  private ?string $scheme = null;
  private ?int $port = null;
  private ?string $ip = null;
  private bool $isAjax = false;
  private ?string $userAgent = null;
  private ?bool $isIframe = null;

  public function getUserAgent(): string
  {
    return $this->userAgent;
  }

  public function setUserAgent(string $userAgent): void
  {
    $this->userAgent = $userAgent;
  }

  public function isAjax(): bool
  {
    return $this->isAjax;
  }

  public function setIsAjax(bool $isAjax): void
  {
    $this->isAjax = $isAjax;
  }

  public function isIframe(): ?bool
  {
    return $this->isIframe;
  }

  public function setIsIframe(?bool $isIframe): void
  {
    $this->isIframe = $isIframe;
  }

  public function fillRequestFromServer(): void
  {
    $this->getParams = $_GET ?? [];
    $this->postParams = $_POST ?? [];
    $this->params = $_REQUEST ?? [];

    if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
      try {
        $this->postParams = json_decode(file_get_contents('php://input'), true) ?? [];
        if (count($this->postParams)) {
          $this->params = $this->postParams;
        }
      } catch (Throwable) {
      }
    }

    foreach (getallheaders() as $key => $value) {
      $this->headers[strtolower($key)] = $value;
    }

    $this->method = strtolower($_SERVER['REQUEST_METHOD']);
    $this->uri = urldecode($_SERVER['REQUEST_URI']);
    $this->domain = $_SERVER['HTTP_HOST'];
    $this->port = (int)$_SERVER['SERVER_PORT'];
    $this->ip = $_SERVER['REMOTE_ADDR'];

    $this->isIframe = ($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '') === 'iframe';

    $this->scheme =
      $_SERVER['HTTP_X_FORWARDED_PROTO']
      ?? $_SERVER['HTTP_X_SCHEME']
      ?? $_SERVER['REQUEST_SCHEME']
      ?? 'http';

    $this->uriRequest = explode('?', $_SERVER['REQUEST_URI'])[0];
    $this->uriParams = explode('?', $_SERVER['REQUEST_URI'])[1] ?? '';

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
      && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $this->isAjax = true;
    }

    $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    foreach ($_FILES as $key => $file) {
      if (is_array($file['tmp_name'])) {
        $this->files[$key] = array_map(function ($name, $type, $tmpName, $error, $size) {
          return new File([
            'name' => $name,
            'type' => $type,
            'tmpName' => $tmpName,
            'error' => $error,
            'size' => $size
          ]);
        }, $_FILES[$key]['name'],
          $_FILES[$key]['type'],
          $_FILES[$key]['tmp_name'],
          $_FILES[$key]['error'],
          $_FILES[$key]['size']
        );
      } else {
        $this->files[$key] = new File([
          'name' => $file['name'],
          'type' => $file['type'],
          'tmpName' => $file['tmp_name'],
          'error' => $file['error'],
          'size' => $file['size']
        ]);
      }
    }
  }

  public function fillRequestFromCli(): void
  {
    $this->getParams = $_GET;

    global $argv;

    $route = null;

    foreach ($argv as $arg) {

      $e = explode("=", $arg);

      if ($e[0] == 'route') {
        $route = $e[1];
        continue;
      }

      if (count($e) == 2) {
        $this->getParams[$e[0]] = $e[1];
      }
    }

    $this->uri = '/' . $route;
    $this->domain = 'cli';
  }

  public function getGet(string $key, $default = null, array $filters = [])
  {
    $value = $this->getParams[$key] ?? $default;

    foreach ($filters as $filter) {
      $value = $filter->filter($value);
    }

    return $value;
  }

  public function getGetAll(): array
  {
    return $this->getParams;
  }

  public function getPost(string $key, $default = null, array $filters = []): mixed
  {
    $value = $this->postParams[$key] ?? $default;

    foreach ($filters as $filter) {

      $filter = new $filter();

      $value = $filter->filter($value);
    }

    return $value;
  }

  public function getPostAll(): array
  {
    return $this->postParams;
  }

  public function getParam(string $name, $default = null, array $filters = []): mixed
  {
    $value = $this->params[$name] ?? $default;

    foreach ($filters as $filter) {

      $filter = new $filter();

      $value = $filter->filter($value);
    }

    return $value;
  }

  public function getParams(): array
  {
    return $this->params;
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }

  public function getHeader(string $key): ?string
  {
    return $this->headers[$key] ?? null;
  }

  public function setHeader(string $key, string $value): void
  {
    $this->headers[$key] = $value;
  }

  public function getXHeaders(): array
  {
    $xHeaders = [];

    foreach ($this->headers as $name => $value) {
      if (str_starts_with($name, 'x-')) {
        $xHeaders[$name] = $value;
      }
    }

    return $xHeaders;
  }

  public function isGet(): bool
  {
    return $this->method == self::METHOD_GET;
  }

  public function isPost(): bool
  {
    return $this->method == self::METHOD_POST;
  }

  public function isPut(): bool
  {
    return $this->method == self::METHOD_PUT;
  }

  public function isDelete(): bool
  {
    return $this->method == self::METHOD_DELETE;
  }

  public function getMethod(): ?string
  {
    return $this->method;
  }

  public function setMethod(string $method): void
  {
    if (!in_array($method, [self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE])) {
      throw new Core\Exception\RequestMethodIsNotSupported($method);
    }

    $this->method = $method;
  }

  public function getUri(): string
  {
    return $this->uri;
  }

  public function setUri(string $uri): void
  {
    $this->uri = $uri;
  }

  public function getDomain(): string
  {
    return $this->domain;
  }

  public function setDomain(string $domain): void
  {
    $this->domain = $domain;
  }

  public function getScheme(): string
  {
    return $this->scheme;
  }

  public function setScheme(string $scheme): void
  {
    $this->scheme = $scheme;
  }

  public function getPort(): int
  {
    return $this->port;
  }

  public function setPort(int $port): void
  {
    $this->port = $port;
  }

  public function getIp(): string
  {
    return $this->ip;
  }

  public function setIp(string $ip): void
  {
    $this->ip = $ip;
  }

  public function setGetParam(string $key, $value, bool $replace = false): void
  {
    if ($replace || !isset($this->getParams[$key])) {
      $this->getParams[$key] = $value;
    }
  }

  public function getUriParams(): string
  {
    return $this->uriParams;
  }

  public function setUriParams(string $uriParams): void
  {
    $this->uriParams = $uriParams;
  }

  public function getUriRequest(): string
  {
    return $this->uriRequest;
  }

  public function setUriRequest(string $uriRequest): void
  {
    $this->uriRequest = $uriRequest;
  }

  public function getFile(string $fileKey): ?\Air\Type\File
  {
    return $this->files[$fileKey] ?? null;
  }

  public function getMultipleFile(string $fileKey): ?\Air\Type\File
  {
    return $this->files[$fileKey] ?? null;
  }

  public function getFiles(): array
  {
    return $this->files ?? [];
  }
}
