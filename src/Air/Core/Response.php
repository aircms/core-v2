<?php

declare(strict_types=1);

namespace Air\Core;

class Response
{
  private int $statusCode = 200;
  private string $statusMessage = '';
  private mixed $body = null;
  private array $headers = [];

  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  public function setStatusCode(int $statusCode): void
  {
    $this->statusCode = $statusCode;
  }

  public function getStatusMessage(): string
  {
    return $this->statusMessage;
  }

  public function setStatusMessage(string $statusMessage): void
  {
    $this->statusMessage = $statusMessage;
  }

  public function getBody(): mixed
  {
    return $this->body;
  }

  public function setBody($body): void
  {
    $this->body = $body;
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }

  public function setHeaders(array $headers): void
  {
    $this->headers = $headers;
  }

  public function addHeaders(array $headers): void
  {
    foreach ($headers as $key => $val) {
      $this->headers[$key] = $val;
    }
  }

  public function setHeader(string $key, string $value): void
  {
    $this->headers[$key] = $value;
  }
}