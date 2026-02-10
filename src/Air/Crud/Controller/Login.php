<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Core\Controller;
use Air\Core\Front;
use Air\Crud\Auth;
use Air\Filter\Trim;

class Login extends Controller
{
  public function index(): string|array
  {
    if ($this->getRequest()->isPost()) {

      $login = Trim::clean($this->getParam('login'));
      $password = Trim::clean($this->getParam('password'));

      if (Auth::getInstance()->isValid($login, $password)) {
        Auth::getInstance()->authorize($login, $password);
        return [];
      }

      $this->getResponse()->setStatusCode(400);
      return [];
    }

    $this->getView()->assign('title', Front::getInstance()->getConfig()['air']['admin']['title']);
    $this->getView()->assign('returnUrl', $this->getParam('returnUrl'));
    $this->getView()->setPath(__DIR__ . '/../View');

    return $this->getView()->render('login');
  }

  public function logout(): void
  {
    Auth::getInstance()->remove();

    $authRoute = Front::getInstance()->getConfig()['air']['admin']['auth']['route'];
    $this->redirect($this->getRouter()->assemble(['controller' => $authRoute], [], true));
  }
}