<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Crud\Controller\Single;
use Air\Crud\Locale;
use Air\Crud\Model\History;
use Air\Crud\Model\Language;
use Air\Form\Element\Hidden;
use Air\Form\Form;
use Air\Map;
use Air\Model\ModelAbstract;
use Air\Model\ModelInterface;
use MongoDB\BSON\ObjectId;
use Throwable;

trait Manage
{
  protected function getManageable(): bool
  {
    $model = $this->getModelClassName();
    return !!$this->getForm(new $model);
  }

  protected function getForm($model = null): ?Form
  {
    return null;
  }

  protected function getManageableMultiple(): bool
  {
    return !!$this->getFormMultiple();
  }

  protected function getFormMultiple(): ?Form
  {
    return null;
  }

  protected function getFormValues(Form $form): array
  {
    return $form->getCleanValues();
  }

  protected function getFormBlock(ModelAbstract $model): ?string
  {
    return null;
  }

  protected function saveModel($formData, $model): void
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    $oldModel = new $modelClassName($model->getData());
    $oldData = [];

    foreach ($formData as $key => $value) {
      if ($model->{$key} instanceof ModelInterface) {
        $oldValue = $model->{$key}->id;
      } else {
        $oldValue = $model->{$key};
      }
      $oldData[$key] = $oldValue;
    }

    $this->adminLog(
      $model->id
        ? History::TYPE_WRITE_ENTITY
        : History::TYPE_CREATE_ENTITY,
      isset($oldData['title']) ? [$oldData['title']] : $oldData,
      null,
      $oldData,
      $formData
    );

    $model->populate($formData);

    $isCreating = !!$model->id;

    $model->save();

    if ($isCreating) {
      $this->didChanged($model, $formData, $oldModel);
    } else {
      $this->didCreated($model, $formData);
    }

    $this->didSaved($model, $formData, $oldModel);
  }

  public function getConditionsBasedOnIds(?array $filter = []): array
  {
    if ($this->getParam('ids')) {
      $ids = array_map(function ($id) {
        return new ObjectId($id);
      }, explode(',', $this->getParam('ids')));

      return ['_id' => ['$in' => $ids]];
    }
    return $this->getConditions($filter);
  }

  public function manageMultiple()
  {
    $form = $this->getFormMultiple();

    if ($this->getParam('ids')) {
      $form->addElement(new Hidden('ids', [
        'value' => $this->getParam('ids'),
      ]));

    }
    $form->addElement(new Hidden('filter', [
      'value' => base64_encode(serialize($this->getParam('filter'))),
    ]));

    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    if ($this->getRequest()->isPost()) {
      if ($form->isValid($this->getRequest()->getPostAll())) {

        $formData = $form->getCleanValues();

        $filter = unserialize(base64_decode($formData['filter']));
        unset($formData['filter']);

        $cond = $this->getConditionsBasedOnIds($filter);
        unset($formData['ids']);

        foreach ($modelClassName::fetchAll($cond) as $item) {
          $this->saveModel($formData, $item);
        }

        return ['count' => $modelClassName::count($cond)];

      } else {
        $this->getResponse()->setStatusCode(400);
      }
    }

    $returnUrl = $this->getParam('returnUrl');
    if (!$returnUrl) {
      $returnUrl = $this->getRouter()->assemble([
        'controller' => $this->getRouter()->getController(),
        'action' => 'index'
      ]);
    }

    $form->setReturnUrl($returnUrl);

    $count = $modelClassName::count($this->getConditionsBasedOnIds());

    $this->getView()->setVars([
      'icon' => $this->getAdminMenuItem()['icon'] ?? null,
      'title' => Locale::t($this->getTitle()) . ' (' . Locale::t('Editing') . ' ' . $count . ' ' . Locale::t('records') . ')',
      'form' => $form,
      'mode' => 'manage',
      'isQuickManage' => true,
      'isMultipleManage' => true
    ]);

    $this->setAdminNavVisibility(false);
    $this->getView()->setScript('form/index');
  }

  public function manage(?string $id = null)
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();
    $model = $modelClassName::fetchObject(['id' => $id]);

    $form = $this->getForm($model);

    if ($this->getRequest()->isPost()) {
      if ($form->isValid($this->getRequest()->getPostAll())) {

        $isCreating = !$model->id;
        $formData = $this->getFormValues($form);

        $this->saveModel($formData, $model);

        if ($isCreating && isset($formData['language'])) {
          foreach (Language::fetchAll() as $language) {
            if ($language->id !== $formData['language']) {
              $this->localizedCopy($model->id, $language);
            }
          }
        }

        $this->getView()->setLayoutEnabled(false);
        $this->getView()->setAutoRender(false);

        return [
          'url' => $this->getRouter()->assemble(
            ['controller' => $this->getEntity(), 'action' => 'manage'],
            [
              'returnUrl' => $this->getRequest()->getPost('return-url'),
              'id' => $model->id,
              'isQuickManage' => (bool)$this->getParam('isQuickManage') ?? false
            ]
          ),
          'quickSave' => $this->getParam('quick-save'),
          'newOne' => !$isCreating
        ];
      } else {
        $this->getResponse()->setStatusCode(400);
      }
    }

    if ($model->id) {
      try {
        $data = Map::execute($model, array_keys($this->getHeader()));
      } catch (Throwable) {
        $data = ['id' => $model->id];
      }
      $this->adminLog(History::TYPE_READ_ENTITY, $data);
    }

    $returnUrl = $this->getParam('returnUrl');
    if (!$returnUrl) {
      $returnUrl = $this->getRouter()->assemble([
        'controller' => $this->getRouter()->getController(),
        'action' => 'index'
      ]);
    }

    $form->setReturnUrl($returnUrl);

    $this->getView()->setVars([
      'icon' => $this->getAdminMenuItem()['icon'] ?? $this->getIcon() ?? null,
      'title' => $this->getTitle(),
      'form' => $form,
      'model' => $model,
      'controller' => $this->getRouter()->getController(),
      'mode' => 'manage',
      'isQuickManage' => (bool)$this->getParam('isQuickManage') ?? false,
      'isSelectControl' => (bool)$this->getParam('isQuickManage') ?? false,
      'isSingle' => is_subclass_of($this, Single::class),
      'isOpenAiEnabled' => $this->isOpenAiEnabled(),
      'isDeepSeekEnabled' => $this->isDeepSeekEnabled(),
      'structure' => $this->getStructure(),
      'formBlock' => $this->getFormBlock($model),
    ]);

    if ($this->getRequest()->isIframe()) {
      $this->setAdminNavVisibility(false);
    }

    $this->getView()->setScript('form/index');
  }
}