<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Control;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Controller\MultipleHelper\Accessor\HeaderButton;
use Air\Crud\Locale;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Email;
use Air\Type\FaIcon;

/**
 * @mod-sorting {"when": -1}
 *
 * @mod-filter {"type": "search", "by": ["toAddress", "toName", "subject", "body", "status"]}
 */
class EmailQueue extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_EMAIL_QUEUE;
  }

  public function details(\Air\Crud\Model\EmailQueue $id): string
  {
    $this->getView()->assign('email', $id);
    $this->getView()->assign('entity', $this->getEntity());

    return $this->getView()->render('emailQueue/details');
  }

  public function body(string $id): string
  {
    $this->getView()->setAutoRender(false);
    $this->getView()->setLayoutEnabled(false);

    return \Air\Crud\Model\EmailQueue::fetchOne(['id' => $id])->body;
  }

  public function getHeader(): array
  {
    return [
      Header::source('Status', function (\Air\Crud\Model\EmailQueue $emailQueue) {
        return vertical([
          badge(format($emailQueue->when), LIGHT),
          badge(format($emailQueue->when), DARK),
          match ($emailQueue->status) {
            \Air\Crud\Model\EmailQueue::STATUS_NEW => badge(Locale::t('Planned'), WARNING),
            \Air\Crud\Model\EmailQueue::STATUS_SUCCESS => badge(Locale::t('Success'), SUCCESS),
            \Air\Crud\Model\EmailQueue::STATUS_FAIL => badge(Locale::t('Fail'), DANGER),
          }
        ]);
      }),
      Header::source('Destination', function (\Air\Crud\Model\EmailQueue $emailQueue) {
        return vertical([
          badge($emailQueue->toName, INFO),
          badge($emailQueue->toAddress, INFO),
        ]);
      }),
      Header::text(by: 'subject'),
      Header::longtext(by: 'body')
    ];
  }

  public function send(\Air\Crud\Model\EmailQueue $id): array
  {
    return ['success' => Email::send($id)];
  }

  public function clear(): void
  {
    \Air\Crud\Model\EmailQueue::batchRemove([
      'status' => ['$ne' => \Air\Crud\Model\EmailQueue::STATUS_NEW]
    ]);

    $this->redirect('/' . $this->getEntity());
  }

  public function clearAllForce(): void
  {
    \Air\Crud\Model\EmailQueue::batchRemove();
    $this->redirect('/' . $this->getEntity());
  }

  protected function getHeaderButtons(): array
  {
    return [
      HeaderButton::item(
        title: Locale::t('Delete all successful ones?'),
        url: ['controller' => $this->getEntity(), 'action' => 'clear'],
        confirm: Locale::t('Are you sure want to remove all successful emails?'),
        style: DANGER,
        icon: FaIcon::ICON_XMARK
      ),
      HeaderButton::item(
        title: Locale::t('Clear all Emails?'),
        url: ['controller' => $this->getEntity(), 'action' => 'clearAllForce'],
        confirm: Locale::t('Are you sure want to remove all successful emails?'),
        style: DANGER,
        icon: FaIcon::ICON_XMARK
      ),
    ];
  }

  protected function getControls(): array
  {
    return [
      Control::html(
        title: Locale::t('Details'),
        url: ['controller' => $this->getEntity(), 'action' => 'details'],
        icon: FaIcon::ICON_PAGE
      )
    ];
  }
}