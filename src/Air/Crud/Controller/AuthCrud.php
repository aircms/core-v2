<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Core\Controller;
use Air\Core\Front;
use Air\Crud\Auth;

abstract class AuthCrud extends Controller
{
  public function init(): void
  {
    parent::init();

    if (!Auth::getInstance()->isLoggedIn()) {
      $this->redirect(
        $this->getRouter()->assemble(
          ['controller' => Front::getInstance()->getConfig()['air']['admin']['auth']['route']],
          ['returnUrl' => $this->getRequest()->getUri()],
          true
        )
      );
    }

    if (!Auth::getInstance()->isCurrentRouteAllowedAuthorizedUser()) {
      if ($this->getRequest()->isAjax()) {
        $this->getView()->setPath(realpath(__DIR__ . '/../View'));
        echo $this->getView()->render('not-allowed');
        Front::getInstance()->stop();
      }
      $this->redirect(
        $this->getRouter()->assemble(['controller' => Front::getInstance()->getConfig()['air']['admin']['notAllowed']], [], true));
    }

    if ($this->getRequest()->isAjax()) {
      $this->getView()->setLayoutEnabled(false);
    }
  }
}