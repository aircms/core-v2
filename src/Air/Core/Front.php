<?php

declare(strict_types=1);

namespace Air\Core;

use Air\Core\Exception\ActionMethodIsReserved;
use Air\Core\Exception\ActionMethodWasNotFound;
use Air\Core\Exception\ControllerClassWasNotFound;
use Air\Core\Exception\Stop;
use Air\Crud\Controller\Login;
use Air\Crud\Controller\NotAllowed;
use Air\Crud\Controller\RobotsTxtUi;
use Air\Crud\Nav\Nav;
use Air\Model\ModelAbstract;
use Air\View\View;
use Error;
use Exception;
use MongoDB\BSON\ObjectId;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

final class Front
{
  private static ?Front $instance = null;
  private array $config;
  private ?Loader $loader;
  private mixed $bootstrap = null;
  private ?Request $request = null;
  private ?Response $response = null;
  private ?Router $router = null;
  private ?View $view = null;

  private function __construct(array $config = [])
  {
    $this->config = $config;
    $this->loader = new Loader($this->config);

    $bootstrapClassName = '\\' . $this->config['air']['loader']['namespace'] . '\\Bootstrap';

    if (class_exists($bootstrapClassName)) {
      $this->bootstrap = new $bootstrapClassName();
      $this->bootstrap->setConfig($this->config);
    }
  }

  public static function getInstance(array $config = []): Front
  {
    if (!self::$instance) {
      self::$instance = new self($config);
    }

    return self::$instance;
  }

  public function getRouter(): Router
  {
    return $this->router;
  }

  public function setRouter(Router $router): void
  {
    $this->router = $router;
  }

  public function getResponse(): Response
  {
    return $this->response;
  }

  public function setResponse(Response $response): void
  {
    $this->response = $response;
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function setRequest(Request $request): void
  {
    $this->request = $request;
  }

  public function getBootstrap(): BootstrapAbstract
  {
    return $this->bootstrap;
  }

  public function setBootstrap(BootstrapAbstract $bootstrap): void
  {
    $this->bootstrap = $bootstrap;
  }

  public function getLoader(): Loader
  {
    return $this->loader;
  }

  public function setLoader(Loader $loader): void
  {
    $this->loader = $loader;
  }

  public function getView(): View
  {
    return $this->view;
  }

  public function setView(View $view): void
  {
    $this->view = $view;
  }

  public function bootstrap(): self
  {
    set_error_handler(function ($number, $message, $file, $line) {
      throw new Exception(implode(':', [$message, $file, $line]), $number);
    });

    if (!$this->request) {

      $this->request = new Request();

      if (php_sapi_name() !== 'cli') {
        $this->request->fillRequestFromServer();
      } else {
        $this->request->fillRequestFromCli();
      }
    }

    if (!$this->response) {
      $this->response = new Response();
    }

    if (!$this->router) {
      $this->router = new Router();
      $this->router->setRoutes($this->config['router'] ?? []);
      $this->router->setRequest($this->request);
      $this->router->parse();
    }

    $this->config['air'] = array_replace_recursive(
      $this->config['air'],
      $this->router->getConfig()
    );

    foreach ($this->config['air']['phpIni'] ?? [] as $key => $val) {
      ini_set($key, $val);
    }

    foreach ($this->config['air']['startup'] ?? [] as $key => $val) {
      if (function_exists($key)) {
        if (!is_array($val)) {
          $val = [$val];
        }
        call_user_func_array($key, $val);
      }
    }

    foreach ($this->config['air']['require'] as $file) {
      if (str_starts_with($file, 'vendor')) {
        require_once dirname($this->config['air']['loader']['path']) . '/' . $file;
      } else {
        require_once $file;
      }
    }

    foreach ($this->config['air']['headers'] ?? [] as $key => $val) {
      $this->response->setHeader($key, $val);
    }

    $this->view = new View();
    $this->view->setIsMinifyHtml($this->config['air']['view']['minify'] ?? false);

    if ($this->bootstrap) {

      $bootReflection = new ReflectionClass(
        $this->bootstrap
      );

      foreach ($bootReflection->getMethods() as $method) {
        $this->bootstrap->{$method->name}();
      }
    }
    return $this;
  }

  public function run(Exception|Error|null $exception = null)
  {
    try {
      $modules = null;
      $contexts = null;

      if ($this->config['air']['contexts'] ?? false) {
        $contexts = implode('/', array_slice(explode('\\', $this->config['air']['contexts']), 2));
      }

      if ($this->config['air']['modules'] ?? false) {
        $modules = implode('/', array_slice(explode('\\', $this->config['air']['modules']), 2));
      }

      $viewPath = null;

      if ($contexts) {
        $viewPath = realpath(implode('/', [
          $this->config['air']['loader']['path'],
          $contexts,
          ucfirst($this->router->getContext()),
          'View'
        ]));

      }
      if ($modules && !$viewPath) {
        $viewPath = realpath(implode('/', [
          $this->config['air']['loader']['path'],
          $modules,
          ucfirst($this->router->getModule()),
          'View'
        ]));

      }
      if (!$viewPath && !$contexts && !$modules) {
        $viewPath = realpath(implode('/', [
          $this->config['air']['loader']['path'],
          'View'
        ]));
      }

      if ($viewPath) {
        $this->view->setPath($viewPath);
        $this->view->setLayoutEnabled(true);
        $this->view->setLayoutTemplate('index');
        $this->view->setScript($this->router->getController() . '/' . $this->router->getAction());

      } else {
        $this->view->setAutoRender(false);
        $this->view->setLayoutEnabled(false);
      }

      $controllerClassName = $this->getControllerClassName($this->router);

      if (!class_exists($controllerClassName) || !is_subclass_of($controllerClassName, Controller::class)) {

        if ($exception) {
          throw $exception;
        }

        throw new ControllerClassWasNotFound($controllerClassName);
      }

      /** @var Controller|ErrorController $controller */
      $controller = new $controllerClassName();

      $controller->setRequest($this->request);
      $controller->setResponse($this->response);
      $controller->setRouter($this->router);
      $controller->setView($this->view);

      if ($exception && is_subclass_of($controller, ErrorController::class)) {
        $controller->setException($exception);
        $controller->setExceptionEnabled($this->config['air']['exception'] ?? false);

      } else if ($exception) {
        throw $exception;
      }

      /** @var Plugin[] $plugins */

      $plugins = [];

      $pluginsPaths = [
        '\\' . $this->config['air']['loader']['namespace'] . '\\Plugin\\' => realpath($this->config['air']['loader']['path'] . '/Plugin')
      ];

      if ($contexts) {
        $pluginsPaths['\\' . $this->config['air']['loader']['namespace'] . '\\' . $contexts . '\\' . ucfirst($this->router->getContext()) . '\\Plugin\\'] =
          realpath(implode('/', [
            $this->config['air']['loader']['path'],
            $contexts,
            ucfirst($this->router->getContext()),
            'Plugin'
          ]));

      }

      if ($modules) {
        $pluginsPaths['\\' . $this->config['air']['loader']['namespace'] . '\\' . $modules . '\\' . ucfirst($this->router->getModule()) . '\\Plugin\\'] =
          realpath(implode('/', [
            $this->config['air']['loader']['path'],
            $modules,
            ucfirst($this->router->getModule()),
            'Plugin'
          ]));
      }

      foreach (array_filter($pluginsPaths) as $pluginNamespace => $pluginsPath) {
        foreach (glob($pluginsPath . '/*.php') as $pluginClass) {
          $pluginClassName = $pluginNamespace . str_replace('.php', '', basename($pluginClass));
          if (is_subclass_of($pluginClassName, Plugin::class)) {
            $plugins[] = new $pluginClassName();
          }
        }
      }

      foreach ($plugins as $plugin) {
        $plugin->preRun($this->request, $this->response, $this->router);
      }

      $controller->init();

      if (is_callable([$controller, $this->router->getAction()])) {

        if (in_array($this->router->getAction(), get_class_methods(Controller::class))) {
          throw new ActionMethodIsReserved($this->router->getAction());
        }

        $content = call_user_func_array(
          [$controller, $this->router->getAction()],
          $this->inject($controller, $this->router, $this->request)
        );

        $needLayout = true;

        if (!is_null($content)) {
          $needLayout = false;
        }

        $controller->postRun();

        if (is_null($content) && $this->view->isAutoRender()) {
          $content = $this->view->render();
        }

        if (is_null($content)) {
          $content = $this->view->getContent();
        }

        if (is_object($content) && method_exists($content, 'toArray')) {
          $content = $content->toArray();
        }

        if (is_array($content)) {
          $content = json_encode($content);
          $this->response->setHeader('Content-type', 'application/json');

        } else if ($this->view->isLayoutEnabled() && $needLayout) {
          $this->view->setContent($content ?? '');
          $content = $this->view->renderLayout();
        }

        $this->response->setBody($content);

        foreach ($plugins as $plugin) {
          $plugin->postRun($this->request, $this->response, $this->router);
        }
      } else {
        throw new ActionMethodWasNotFound($this->router->getAction());
      }

      return $this->render($this->response);
    } catch (Throwable $localException) {

      if ($localException instanceof Stop) {
        return $this->render($this->response);
      }

      if (!$exception) {

        $errorRouter = new Router();

        $errorRouter->setRequest($this->request);
        $errorRouter->setContext($this->router->getContext());
        $errorRouter->setModule($this->router->getModule());
        $errorRouter->setController('error');
        $errorRouter->setAction('index');
        $errorRouter->setRoutes($this->config['router'] ?? []);
        $errorRouter->setConfig($this->router->getConfig());
        $errorRouter->setIsError(true);

        $this->setRouter($errorRouter);
        return $this->run($localException);
      }

      if ($this->config['air']['exception'] ?? false) {
        throw $localException;
      }
    }
  }

  public function getControllerClassName(Router $router): string
  {
    $module = $router->getModule();
    $controller = $router->getController();

    if ($nav = Nav::getSettingsItemWithAlias($controller)) {
      return $nav['controller'];
    }

    if ($controller === 'robots.txt') {
      return RobotsTxtUi::class;
    }

    if (($this->getConfig()['air']['admin']['auth']['route'] ?? false) === $controller) {
      return Login::class;
    }

    if (($this->getConfig()['air']['admin']['notAllowed'] ?? false) === $controller) {
      return NotAllowed::class;
    }

    if ($this->config['air']['contexts'] ?? false) {
      $contextController = implode('\\', [
        $this->config['air']['contexts'],
        ucfirst($router->getContext()),
        'Controller',
        ucfirst($controller),
      ]);

      if (class_exists($contextController)) {
        return $contextController;
      }
    }

    if ($this->config['air']['modules'] ?? false) {
      return $this->groupedController(
        implode('\\', [
          $this->config['air']['modules'],
          ucfirst($module),
          'Controller',
          ucfirst($controller),
        ])
      );
    }

    return implode('\\', [
      $this->config['air']['loader']['namespace'],
      'Controller',
      ucfirst($controller),
    ]);
  }

  public function groupedController(string $fullControllerName): string
  {
    if ($this->config['air']['loader']['groupedController'] ?? false) {
      if (class_exists($fullControllerName . '\\Controller')) {
        return $fullControllerName . '\\Controller';
      }
    }

    return $fullControllerName;
  }

  public function getConfig(): array
  {
    return $this->config;
  }

  public function setConfig(array $config): void
  {
    $this->config = $config;

    if ($this->bootstrap) {
      $this->bootstrap->setConfig($config);
    }
  }

  public function inject(Controller $controller, Router $router, Request $request): array
  {
    $reflection = new ReflectionMethod($controller, $router->getAction());

    $injector = $router->getInjector();
    $docComment = $reflection->getDocComment();

    $params = [];

    if ($docComment) {
      $docComment = str_replace('*', '', $reflection->getDocComment());

      $docComment = array_filter(array_map(function ($line) {

        $line = trim($line);

        if (strlen($line) > 0) {
          return $line;
        }
        return null;
      }, explode("\n", $docComment)));

      foreach ($docComment as $line) {

        if (str_starts_with($line, '@param')) {

          try {
            $param = explode('|', explode('$', $line)[1]);
          } catch (Exception) {
          }

          $var = trim($param[0]);

          $main = trim((explode('&', ($param[1] ?? 'id'))[0]));
          $main = strlen($main) ? $main : 'id';

          try {
            $cond = json_decode(explode('&', $param[1])[1] ?? [], true) ?? [];
          } catch (Throwable) {
            $cond = [];
          }

          $params[$var] = [
            'main' => $main,
            'cond' => $cond
          ];
        }
      }
    }

    if (!count($params)) {
      foreach ([...$request->getGetAll(), ...$request->getPostAll()] as $paramName => $paramValue) {
        $params[$paramName] = [
          'main' => 'id',
          'cond' => []
        ];
      }
    }

    $args = [];

    foreach ($reflection->getParameters() as $parameter) {

      $var = $parameter->getName();

      if (isset($injector[$var])) {
        $args[$var] = $injector[$var]($router->getUrlParams()[$var] ?? null);
      } else {
        $value = $router->getUrlParams()[$var] ?? $request->getParam($var);
        $defaultValue = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

        if (!$parameter->getType()) {
          $args[$var] = $value;
          continue;
        }

        switch ($parameter->getType()->getName()) {

          case 'int':
            $args[$var] = $value !== null ? intval($value) : $defaultValue;
            break;

          case 'string':
            $args[$var] = $value !== null ? strval($value) : $defaultValue;
            break;

          case 'bool':
            $args[$var] = $value !== null ? boolval($value) : $defaultValue;
            break;

          case 'array':
            $args[$var] = $value !== null ? (array)$value : $defaultValue;
            break;

          default:
            $className = $parameter->getType()->getName();

            try {

              /** @var ModelAbstract $model */
              $model = new $className();

              if (is_subclass_of($model, ModelAbstract::class)) {

                if (isset($params[$parameter->getName()])) {

                  $property = $model->getMeta()->getPropertyWithName(
                    $params[$parameter->getName()]['main']
                  );

                  try {
                    settype($value, $property->getType());
                  } catch (Exception) {
                    $value = (string)$value;
                  }

                  $allCond = [];

                  if ($params[$parameter->getName()]['main'] === 'id') {
                    try {
                      if ($this->config['air']['db']['driver'] === 'mongodb') {
                        new ObjectId($value);
                      }
                      $allCond['id'] = $value;
                    } catch (Throwable) {
                      $allCond['url'] = $value;
                    }
                    $userCond = [];
                  } else {
                    $allCond = [$params[$parameter->getName()]['main'] => $value];
                    $userCond = ($params[$parameter->getName()]['cond'] ?? []);
                  }

                  if (count($userCond)) {
                    $allCond = [...$userCond, ...$allCond];
                  }

                  /** @var ModelAbstract $className */

                  if ($this->getRouter()->getConfig()['strictInject'] ?? false) {
                    $args[$var] = $className::one($allCond);
                  } else {
                    $args[$var] = $className::fetchOne($allCond);
                  }
                }
              } else {
                $args[$var] = new $className($value);
              }
            } catch (Throwable) {
              $args[$var] = $value;
            }
        }
      }
    }

    return $args;
  }

  public function render(Response $response): mixed
  {
    $statusCode = $response->getStatusCode();

    $phpSapiName = substr(php_sapi_name(), 0, 3);

    if ($phpSapiName == 'cli') {
      return null;
    }

    if ($phpSapiName == 'cgi' || $phpSapiName == 'fpm') {
      header('Status: ' . $statusCode);
    } else {
      $protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0';
      header($protocol . ' ' . $statusCode);
    }

    foreach ($response->getHeaders() as $name => $value) {
      header($name . ': ' . $value);
    }

    return $response->getBody();
  }

  public function stop()
  {
    throw new Stop();
  }
}
