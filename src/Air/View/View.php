<?php

declare(strict_types=1);

namespace Air\View;

use Air\Core\Exception\ViewTemplateWasNotFound;
use Air\Core\Front;
use Air\Type\Meta;
use Air\View\Helper\Asset;
use Air\View\Helper\Base;
use Air\View\Helper\Head;
use Air\View\Helper\HelperAbstract;
use Air\View\Helper\Partial;
use Air\View\Helper\PartialCached;
use Air\View\Helper\Uri;
use Closure;
use Exception;
use ReflectionClass;

/**
 * @method Asset|string asset(string[]|string $array): string
 * @method Base|string base(): string
 * @method Head|string head(string $charset = 'UTF-8', string $viewport = 'width=device-width, initial-scale=1.0, minimum-scale=1.0', string $favicon = null, string|array $assets = null): string
 * @method Partial|string partial(string $template, array $vars = []): string
 * @method PartialCached|string partialCached(string $template): string
 * @method Uri|string uri(array $route = [], array $params = [], bool $reset = true): string
 */
class View
{
  protected ?string $path = null;
  protected bool $layoutEnabled = true;
  protected ?string $layoutTemplate = null;
  protected ?string $script = null;
  protected bool $autoRender = true;
  protected ?string $content = null;
  protected array $vars = [];
  protected bool $isMinifyHtml = false;
  protected ?Meta $meta = null;
  protected ?Closure $defaultMeta = null;
  protected array $properties = [];

  public function __construct()
  {
    foreach ((new ReflectionClass($this))->getProperties() as $property) {
      $this->properties[] = $property->getName();
    }
  }

  public function assign(string $key, mixed $value): void
  {
    if (in_array(strtolower($key), $this->properties)) {
      throw new Exception("Var name '$key' is predefined");
    }

    $this->vars[$key] = $value;
  }

  public function getVars(): array
  {
    return $this->vars;
  }

  public function setVars(array $vars): void
  {
    foreach ($vars as $key => $value) {
      $this->assign($key, $value);
    }
  }

  public function getPath(): string
  {
    return $this->path;
  }

  public function setPath(string $path): void
  {
    $this->path = $path;
  }

  public function isLayoutEnabled(): bool
  {
    return $this->layoutEnabled;
  }

  public function setLayoutEnabled(bool $layoutEnabled): void
  {
    $this->layoutEnabled = $layoutEnabled;
  }

  public function isMinifyHtml(): bool
  {
    return $this->isMinifyHtml;
  }

  public function setIsMinifyHtml(bool $isMinifyHtml): void
  {
    $this->isMinifyHtml = $isMinifyHtml;
  }

  public function setMeta(?Meta $meta): void
  {
    $this->meta = $meta;
  }

  public function getMeta(): ?Meta
  {
    if (!$this->meta) {
      if ($defaultMeta = $this->getDefaultMeta()) {
        $this->meta = $defaultMeta();
      }
    }
    return $this->meta;
  }

  public function getBase(): string
  {
    return tag('base', attr: [
      'href' => Front::getInstance()->getConfig()['air']['asset']['prefix'] . '/'
    ]);
  }

  public function setDefaultMeta(Closure $defaultMeta): void
  {
    $this->defaultMeta = $defaultMeta;
  }

  public function getDefaultMeta(): ?Closure
  {
    return $this->defaultMeta;
  }

  public function __get(string $name)
  {
    return $this->vars[$name] ?? null;
  }

  public function __set(string $name, $value)
  {
    $this->vars[$name] = $value;
  }

  public function __call(string $name, array $arguments)
  {
    if (Front::getInstance()->getConfig()['air']['modules'] ?? false) {

      $helperClassName = implode('\\', [
        Front::getInstance()->getConfig()['air']['modules'],
        ucfirst(Front::getInstance()->getRouter()->getModule()),
        'View',
        'Helper',
        ucfirst($name)
      ]);
    } else {
      $helperClassName = implode('\\', [
        Front::getInstance()->getConfig()['air']['loader']['namespace'],
        'View',
        'Helper',
        ucfirst($name)
      ]);
    }

    if (!class_exists($helperClassName)) {
      $helperClassName = implode('\\', ['Air', 'View', 'Helper', ucfirst($name)]);
    }

    /** @var HelperAbstract $helper */
    $helper = new $helperClassName();
    $helper->setView($this);

    return call_user_func_array([$helper, 'call'], $arguments);
  }

  public function getLayoutTemplate(): string
  {
    return $this->layoutTemplate;
  }

  public function setLayoutTemplate(string $layoutTemplate): void
  {
    $this->layoutTemplate = $layoutTemplate;
  }

  public function getScript(): string
  {
    return $this->script;
  }

  public function setScript(string $script): void
  {
    $this->script = $script;
  }

  public function isAutoRender(): bool
  {
    return $this->autoRender;
  }

  public function setAutoRender(bool $autoRender): void
  {
    $this->autoRender = $autoRender;
  }

  public function getContent(): ?string
  {
    return $this->content;
  }

  public function setContent(string $content): void
  {
    $this->setAutoRender(false);
    $this->content = $content;
  }

  public function render(?string $template = null, array $vars = []): string
  {
    if (count($vars)) {

      $view = new self();

      $view->setPath($this->path);
      $view->setVars($vars);

      return $view->render($template);
    }

    try {

      $_template = $this->path . '/Scripts/' . ($template ?? $this->script) . '.phtml';

      if (!file_exists($_template)) {
        throw new ViewTemplateWasNotFound($_template);
      }

      $content = $this->_render($this->path . '/Scripts/' . ($template ?? $this->script) . '.phtml');

    } catch (ViewTemplateWasNotFound) {

      $config = Front::getInstance()->getConfig();

      if ($config['air']['modules'] ?? false) {
        $modules = implode('/', array_slice(explode('\\', $config['air']['modules']), 2));

        $viewPath = realpath(implode('/', [
          $config['air']['loader']['path'],
          $modules,
          ucfirst(Front::getInstance()->getRouter()->getModule()),
          'View'
        ]));

      } else {
        $viewPath = realpath(implode('/', [
          $config['air']['loader']['path'],
          'View'
        ]));
      }

      $content = $this->_render($viewPath . '/Scripts/' . ($template ?? $this->script) . '.phtml');
    }

    return $content;
  }

  private function _render(string $template): string
  {
    $exception = null;

    ob_start();

    try {
      include $template;
    } catch (Exception $e) {
      $exception = $e;
    }

    $content = ob_get_contents();

    ob_end_clean();

    if ($exception) {
      throw $exception;
    }

    if ($this->isMinifyHtml()) {
      $search = [
        // strip whitespaces after tags, except space
        '/\>[^\S ]+/s',

        // strip whitespaces before tags, except space
        '/[^\S ]+\</s',

        // shorten multiple whitespace sequences
        '/(\s)+/s',

        // Remove HTML comments
        '/<!--(.|\s)*?-->/'
      ];
      $replace = ['>', '<', '\\1', ''];
      $content = preg_replace($search, $replace, $content);
    }

    return $content;
  }

  public function renderLayout(): string
  {
    return $this->_render($this->path . '/Layouts/' . $this->layoutTemplate . '.phtml');
  }
}
