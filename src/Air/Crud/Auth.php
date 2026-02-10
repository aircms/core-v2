<?php

declare(strict_types=1);

namespace Air\Crud;

use Air\Cookie;
use Air\Core\Front;
use Air\Crud\Model\Admin;
use Throwable;

class Auth
{
  const string SOURCE_ROOT = 'root';
  const string SOURCE_DATABASE = 'database';

  private static ?Auth $instance = null;
  private string $cookieName = 'authIdentity';

  public static function getInstance(): Auth
  {
    if (!self::$instance) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  private function isControllerAllowedForUser(string $controller, Admin $admin): bool
  {
    if ($admin->isRoot) {
      return true;
    }

    if ((Front::getInstance()->getConfig()['air']['admin']['notAllowed'] ?? false) == $controller) {
      return true;
    }

    foreach ($admin->permissions as $permission) {
      if (strtolower($permission['controller']) === strtolower($controller)) {
        return true;
      }
    }
    return false;
  }

  private function getLoginSource(string $login, string $password): ?string
  {
    $config = Front::getInstance()->getConfig()['air']['admin']['auth'];

    if (($config['root']['login'] ?? false) == $login && ($config['root']['password'] ?? false) == $password) {
      return self::SOURCE_ROOT;
    }

    if (!empty($config['source']) && $config['source'] === self::SOURCE_DATABASE) {
      $admin = Admin::quantity(['login' => $login, 'password' => $password]);
      if ($admin) {
        return self::SOURCE_DATABASE;
      }
    }

    return null;
  }

  public function authorize(string $login, string $password): bool
  {
    return Cookie::set($this->cookieName, [
      'source' => $this->getLoginSource($login, $password),
      'login' => $login,
      'password' => $password
    ]);
  }

  public function isLoggedIn(): bool
  {
    try {
      $credentials = Cookie::get($this->cookieName);

      $source = $credentials['source'];
      $login = $credentials['login'];
      $password = $credentials['password'];

      if ($source === self::SOURCE_ROOT) {
        $config = Front::getInstance()->getConfig()['air']['admin']['auth'];
        if ($config['root']['login'] == $login && $config['root']['password'] == $password) {
          return true;
        }

      } elseif ($source === self::SOURCE_DATABASE) {
        return !!Admin::quantity(['login' => $login, 'password' => $password]);
      }

      return false;

    } catch (Throwable) {
    }

    return false;
  }

  public function isCurrentRouteAllowedAuthorizedUser(): bool
  {
    $credentials = Cookie::get($this->cookieName);

    $source = $credentials['source'];
    $login = $credentials['login'];
    $password = $credentials['password'];

    if ($source === self::SOURCE_ROOT) {
      $config = Front::getInstance()->getConfig()['air']['admin']['auth'];
      if ($config['root']['login'] == $login && $config['root']['password'] == $password) {
        return true;
      }

    } elseif ($source === self::SOURCE_DATABASE) {

      $controller = Front::getInstance()->getRouter()->getController();

      if ($controller === 'index') {
        return true;
      }

      $admin = Admin::one(['login' => $login, 'password' => $password]);

      if (!$admin) {
        return false;
      }

      return $this->isControllerAllowedForUser(
        Front::getInstance()->getRouter()->getController(),
        $admin
      );
    }

    return false;
  }

  public function isValid(string $login, string $password): bool
  {
    return in_array($this->getLoginSource($login, $password), [
      self::SOURCE_ROOT,
      self::SOURCE_DATABASE,
    ]);
  }

  public function remove(): bool
  {
    return Cookie::remove($this->cookieName);
  }

  public function getName(): string|null
  {
    try {
      $credentials = Cookie::get($this->cookieName);

      $source = $credentials['source'];
      $login = $credentials['login'];
      $password = $credentials['password'];

      if ($source === self::SOURCE_ROOT) {
        return $login;
      }
      if ($source === self::SOURCE_DATABASE) {
        $admin = Admin::fetchOne(['login' => $login, 'password' => $password]);
        return $admin?->name;
      }
    } catch (Throwable) {
    }
    return null;
  }
}