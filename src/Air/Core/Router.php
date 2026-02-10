<?php

declare(strict_types=1);

namespace Air\Core;

use Air\Core\Exception\DomainMustBeProvided;
use Air\Core\Exception\RouterDomainWasNotFound;
use Air\Core\Exception\RouterWasNotFound;

class Router
{
  private ?Request $request = null;
  private string $module = '';
  private string $context = '';
  private string $controller = '';
  private string $action = '';
  private array $routes = [];
  private array $urlParams = [];
  private array $injector = [];
  private array $config = [];
  private bool $isError = false;

  public function getModule(): string
  {
    return $this->module;
  }

  public function setModule(string $module): void
  {
    $this->module = $module;
  }

  public function getContext(): string
  {
    return $this->context;
  }

  public function setContext(string $context): void
  {
    $this->context = $context;
  }

  public function getController(): string
  {
    return $this->controller;
  }

  public function setController(string $controller): void
  {
    $this->controller = $controller;
  }

  public function getAction(): string
  {
    return $this->action;
  }

  public function setAction(string $action): void
  {
    $this->action = $action;
  }

  public function getRoutes(): array
  {
    return $this->routes;
  }

  public function setRoutes(array $routes): void
  {
    $this->routes = $routes;
  }

  public function getUrlParams(): array
  {
    return $this->urlParams;
  }

  public function setUrlParams(array $urlParams): void
  {
    $this->urlParams = $urlParams;
  }

  public function getInjector(): array
  {
    return $this->injector;
  }

  public function setInjector(array $injector): void
  {
    $this->injector = $injector;
  }

  public function getConfig(): array
  {
    return $this->config;
  }

  public function setConfig(array $config): void
  {
    $this->config = $config;
  }

  public function isError(): bool
  {
    return $this->isError;
  }

  public function setIsError(bool $isError): void
  {
    $this->isError = $isError;
  }

  public function assemble(
    array $requestedRoute = [],
    array $params = [],
    bool  $reset = false,
    bool  $onlyUri = false
  ): string
  {
    $module = $requestedRoute['module'] ?? $this->module;
    $context = $requestedRoute['context'] ?? $this->context;
    $controller = $requestedRoute['controller'] ?? ($reset ? '' : $this->controller);
    $action = $requestedRoute['action'] ?? ($reset ? '' : $this->action);

    if (!$reset) {
      $params = array_merge($this->urlParams, $params);
    }

    $uri = null;
    $selectedDomain = null;

    foreach ($this->routes as $domain => $router) {

      if (($router['module'] ?? null) == $module) {

        $selectedDomain = $domain;

        if (str_contains($domain, '*')) {
          $selectedDomain = $this->getRequest()->getDomain();
        }

        foreach ($router['routes'] ?? [] as $routeUri => $route) {

          if ($routeUri == '*' && is_callable($route)) {
            continue;
          }

          $routeUri = ($router['prefix'] ?? '') . $routeUri;

          $currentRouteSettings = [
            'context' => $route['context'] ?? '',
            'controller' => $route['controller'] ?? 'index',
            'action' => $route['action'] ?? 'index'
          ];

          $chContext = $context == '' ? '' : $context;
          $chController = $controller == '' ? 'index' : $controller;
          $chAction = $action == '' ? 'index' : $action;

          if ($chContext == $currentRouteSettings['context']
            && $chController == $currentRouteSettings['controller']
            && $chAction == $currentRouteSettings['action']) {

            $releaseParts = [];

            foreach (explode('/', $routeUri) as $part) {

              if (str_starts_with($part, ':')) {

                $var = substr($part, 1);

                if ($params[$var] ?? null) {
                  $releaseParts[] = $params[$var];
                  unset($params[$var]);
                }

              } else {
                $releaseParts[] = $part;
              }
            }

            $uri = implode('/', $releaseParts);
          }
        }
      }
    }

    if (!$selectedDomain) {
      throw new DomainMustBeProvided();
    }

    if (!$uri) {
      $uri = '/' . implode('/', array_filter([$controller, $action]));
    }

    if (count($params)) {
      $uri = $uri . '?' . http_build_query($params);
    }

    if ($selectedDomain == '*') {
      $selectedDomain = $this->getRequest()->getDomain();
    }

    if ($onlyUri) {
      return $uri;
    }

    $port = '';

    if ($this->getRequest()->getPort() != 80 && $this->getRequest()->getPort() != 443) {
      $port = ':' . $this->getRequest()->getPort();
    }

    return $this->getRequest()->getScheme() . '://' . $selectedDomain . $port . $uri;
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function setRequest(Request $request): void
  {
    $this->request = $request;
  }

  public function parse(): void
  {
    $domain = $this->getRequest()->getDomain();

    if (!isset($this->routes[$domain]) && count($this->routes)) {

      $isSet = false;
      foreach ($this->routes as $route => $settings) {
        if (str_contains($domain, str_replace('*', '', $route)) && $route !== 'cli') {
          $domain = $route;
          $isSet = true;
          break;
        }
      }

      if (!$isSet && !isset($this->routes['*'])) {
        throw new RouterDomainWasNotFound($domain);
      }

    } else if (!isset($this->routes[$domain])) {
      $domain = '*';
    }

    $this->setConfig($this->routes[$domain]['air'] ?? []);

    $routes = $this->routes[$domain] ?? [];
    $this->module = $routes['module'] ?? '';

    $uri = explode('?', $this->getRequest()->getUri())[0];

    $parts = array_values(array_filter(explode('/', $uri)));

    if (!isset($routes['routes'])) {

      if (($routes['strict'] ?? false) === true) {
        throw new RouterWasNotFound($uri);
      }

      $this->controller = $parts[0] ?? 'index';
      $this->action = $parts[1] ?? 'index';

      return;
    }

    $prefix = $routes['prefix'] ?? '';

    if ($uri != '/' && str_ends_with($uri, '/')) {
      $uri = $uri . '/';
    }

    $match = false;
    $withPrefix = false;

    foreach ($routes['routes'] as $routerUri => $settings) {

      if (str_ends_with($uri, '//') && $routerUri != '/') {
        $routerUri = $routerUri . '/';
      }

      $pattern = preg_replace('/\\\:[А-Яа-яЁёa-zA-Z0-9\_\-]+/', '([А-Яа-яЁёa-zA-Z0-9\-\_]+)', preg_quote($routerUri, '@'));
      $pattern = "@^$pattern/?$@uD";

      $matches = [];

      if (preg_match($pattern, $uri, $matches)) {
        $match = true;
        break;
      }
    }

    if (!$match) {

      $withPrefix = true;

      foreach ($routes['routes'] as $routerUri => $settings) {

        if (str_ends_with($uri, '//') && $routerUri != '/') {
          $routerUri = $routerUri . '/';
        }

        $patternPrefix = preg_replace(
          '/\\\:[А-Яа-яЁёa-zA-Z0-9\_\-]+/',
          '([А-Яа-яЁёa-zA-Z0-9\-\_]+)',
          preg_quote($prefix . $routerUri, '@')
        );

        $patternPrefix = "@^$patternPrefix/?$@uD";

        if (preg_match($patternPrefix, $uri, $matches)) {
          $match = true;
          break;
        }
      }
    }

    if ($match) {

      array_shift($matches);

      $this->context = $settings['context'] ?? '';
      $this->controller = $settings['controller'] ?? 'index';
      $this->action = $settings['action'] ?? 'index';
      $this->urlParams = $settings['params'] ?? [];

      $paramIndex = 0;

      if ($withPrefix) {
        $routerUri = $prefix . $routerUri;
      }

      foreach (explode('/', $routerUri) as $routerUriPart) {

        if (str_starts_with($routerUriPart, ':')) {

          $this->getRequest()->setGetParam(
            substr($routerUriPart, 1),
            $matches[$paramIndex] ?? null
          );

          $this->urlParams[substr($routerUriPart, 1)] = $matches[$paramIndex] ?? null;

          $paramIndex++;
        }
      }

      $this->injector = array_merge($settings['injector'] ?? [], $routes['prefixInjector'] ?? []);

      foreach ($this->urlParams as $key => $value) {
        $this->getRequest()->setGetParam($key, $value);
      }

      return;
    }

    if (isset($routes['routes']['*']) && is_callable($routes['routes']['*'])) {

      $route = $routes['routes']['*']($uri);

      if (is_array($route)) {

        $this->context = $route['context'] ?? '';
        $this->controller = $route['controller'] ?? 'index';
        $this->action = $route['action'] ?? 'index';
        $this->urlParams = $route['params'] ?? [];
        $this->injector = $route['injector'] ?? [];

        foreach ($this->urlParams as $key => $value) {
          $this->getRequest()->setGetParam($key, $value);
        }

        return;
      }
    }

    $fontsUi = Front::getInstance()->getConfig()['air']['fontsUi'] ?? false;

    if (
      isset($routes['strict'])
      && $routes['strict'] === true
      && ($parts[0] ?? false) !== 'robots.txt'
      && !(($parts[0] ?? false) === $fontsUi)
    ) {
      throw new RouterWasNotFound($uri);
    }

    if (($parts[0] ?? false) === 'fonts' && ($parts[1] ?? false) === 'css') {
      $this->controller = 'fonts';
      $this->action = 'index';

    } else {
      $this->controller = $parts[0] ?? 'index';
      $this->action = $parts[1] ?? 'index';
    }

    $parts = array_slice($parts, 2);

    for ($i = 0; $i < count($parts); $i += 2) {

      if (isset($parts[$i + 1])) {
        $this->getRequest()->setGetParam($parts[$i], $parts[$i + 1]);
        $this->urlParams[$parts[$i]] = $parts[$i + 1];
      }
    }
  }
}
