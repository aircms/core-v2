<?php

declare(strict_types=1);

namespace Air;

use Air\Crud\Nav\Nav;
use Air\Type\RichContent;
use Throwable;

class Config
{
  public static function defaults(
    ?string $title = 'AirCms',
    ?array  $settings = null,
    ?array  $extensions = [],
    array   $nav = [],
    array   $routes = [],
    bool    $strictRoutes = true,
    bool    $reportErrors = false,
    ?array  $richContent = null,
    string  $timezone = "Europe/Kyiv",
    bool    $single = false,
    bool    $adminGrouped = false,
    array   $require = [],
    array   $adminRequire = [],
    bool    $cacheEnabled = false
  ): array
  {
    $appEntryPoint = realpath(dirname($_SERVER['SCRIPT_FILENAME'], 2));

    if (!$routes && is_file($appEntryPoint . '/config/routes.php')) {
      $routes = require_once $appEntryPoint . '/config/routes.php';
    }

    if (!$nav && is_file($appEntryPoint . '/config/nav.php')) {
      $nav = require_once $appEntryPoint . '/config/nav.php';
    }

    $contextAvailable = false;

    try {
      foreach (glob($appEntryPoint . '/app/Context/*') as $folder) {
        $contextAvailable = true;
        $folder = realpath($folder);

        if (is_dir($folder)) {
          if (is_file($folder . '/Routes.php')) {
            $routes = array_merge($routes, require_once $folder . '/Routes.php');
          }
          if (is_file($folder . '/Config.php')) {
            $extensions[lcfirst(basename($folder))] = require_once $folder . '/Config.php';
          }
        }
      }
    } catch (Throwable) {
      $contextAvailable = false;
    }

    $router = [];
    $modules = [];

    if (!$single) {
      $modules['modules'] = '\\App\\Module';
      $router['router'] = [
        'cli' => [
          'module' => 'cli'
        ],
        'admin.*' => [
          'module' => 'admin',
          'air' => [
            'asset' => [
              'underscore' => false,
              'prefix' => '/assets/air',
            ],
            'require' => array_merge(
              [
                'vendor/aircms/core-v2/src/Air/View/Shorts/shorts.php',
                'vendor/aircms/core-v2/src/Air/Crud/View/Ui/ui.php',
              ], $adminRequire
            ),
          ],
        ],
        'api.*' => [
          'strict' => $strictRoutes,
          'module' => 'api',
          'routes' => $routes,
          'air' => [
            'strictInject' => true,
            'contexts' => $contextAvailable ? '\\App\\Context' : null,
            'cache' => [
              'enabled' => $cacheEnabled,
            ],
          ],
        ],
        '*' => [
          'strict' => $strictRoutes,
          'module' => 'ui',
          'routes' => $routes,
          'air' => [
            'strictInject' => true,
            'asset' => [
              'underscore' => false,
              'prefix' => '/assets/ui',
            ],
            'cache' => [
              'enabled' => $cacheEnabled,
            ],
          ],
        ]
      ];
    }

    return array_replace_recursive(
      [
        'air' => [
          ...$modules,
          'exception' => $reportErrors,
          'phpIni' => [
            'display_errors' => $reportErrors ? '1' : '0',
          ],
          'startup' => [
            'error_reporting' => $reportErrors ? E_ALL : 0,
            'date_default_timezone_set' => $timezone,
          ],
          'loader' => [
            'namespace' => 'App',
            'path' => $appEntryPoint . '/app',
            'groupedController' => $adminGrouped,
          ],
          'db' => [
            'driver' => 'mongodb',
            'user' => getenv("AIR_DB_USER"),
            'pass' => getenv("AIR_DB_PASS"),
            'servers' => [[
              'host' => getenv("AIR_DB_HOST") ?: 'localhost',
              'port' => getenv("AIR_DB_PORT") ?: 27017,
            ]],
            'db' => getenv('AIR_DB_DB')
          ],
          'storage' => [
            'url' => getenv('AIR_FS_URL'),
            'key' => getenv('AIR_FS_KEY'),
          ],
          'logs' => [
            'enabled' => true,
            'exception' => true,
          ],
          'crypt' => [
            'secret' => getenv('AIR_CRYPT_SECRET'),
          ],
          'fontsUi' => 'fontsUi',
          'admin' => [
            'title' => $title,
            'logo' => '/assets/air/logo.png',
            'favicon' => '/assets/air/logo.png',
            'notAllowed' => '_notAllowed',
            'settings' => $settings ?: Nav::getAllSettings(),
            'rich-content' => $richContent ?: RichContent::getAllTypes(),
            'auth' => [
              'route' => '_auth',
              'source' => 'database',
              'root' => [
                'login' => getenv('AIR_ADMIN_AUTH_ROOT_LOGIN'),
                'password' => getenv('AIR_ADMIN_AUTH_ROOT_PASSWORD'),
              ],
            ],
            'tiny' => getenv('AIR_ADMIN_TINY_KEY'),
            'menu' => $nav,
          ],
          'require' => $require,
        ],
        ...$router
      ],
      $extensions ?: []
    );
  }
}
