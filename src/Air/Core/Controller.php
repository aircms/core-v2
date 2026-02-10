<?php

declare(strict_types=1);

namespace Air\Core;

use Air\View\View;

class Controller
{
  private ?Request $request = null;
  private ?Response $response = null;
  private ?Router $router = null;
  private ?View $view = null;

  public function getResponse(): ?Response
  {
    return $this->response;
  }

  public function setResponse(Response $response): void
  {
    $this->response = $response;
  }

  public function getRouter(): ?Router
  {
    return $this->router;
  }

  public function setRouter(Router $router): void
  {
    $this->router = $router;
  }

  public function getView(): View
  {
    return $this->view;
  }

  public function setView(View $view): void
  {
    $this->view = $view;
  }

  public function redirect(string $uri, ?int $code = 0): void
  {
    header('Location: ' . $uri, true, $code);
    die();
  }

  public function getParam(string $name, $default = null, array $filters = []): mixed
  {
    return $this->getRequest()->getParam($name, $default, $filters) ??
      $this->getRequest()->getGet($name, $default, $filters);
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function setRequest(Request $request): void
  {
    $this->request = $request;
  }

  public function getParams(): array
  {
    return array_merge(
      $this->getRequest()->getParams(),
      $this->getRequest()->getGetAll()
    );
  }

  public function init(): void
  {
  }

  public function postRun(): void
  {
  }
}
