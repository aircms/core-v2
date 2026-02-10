<?php

declare(strict_types=1);

namespace Air\Crud\Controller\Many;

use Air\Crud\Controller\Language;
use Air\Crud\Controller\MultipleHelper\Accessor\Control;
use Air\Crud\Controller\MultipleHelper\Accessor\Filter;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Model\History;
use Air\Model\ModelAbstract;
use Air\Model\Paginator;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use ReflectionClass;
use Throwable;

trait Table
{
  protected function getConditions(array $filter = []): array
  {
    $conditions = [];

    foreach ($this->getFilterWithValues($filter) as $filter) {

      if (empty($filter['value'])) {
        continue;
      }

      if ($filter['type'] == 'search') {
        if (count($filter['by']) > 1) {
          foreach ($filter['by'] as $field) {
            $value = $filter['value'];
            if (str_starts_with($value, '!')) {
              $value = substr($filter['value'], 1);
              $conditions['$or'][] = [$field => ['$not' => new Regex(htmlspecialchars(quotemeta($value)), 'i')]];
            } else {
              $conditions['$or'][] = [$field => new Regex(htmlspecialchars(quotemeta($value)), 'i')];
            }
          }
        } else {
          if (str_starts_with($filter['value'], '!')) {
            $value = substr($filter['value'], 1);
            $conditions[$filter['by'][0]] = ['$not' => new Regex(htmlspecialchars(quotemeta($value)), 'i')];
          } else {
            if ($filter['by'][0] === 'id') {
              try {
                $conditions['_id'] = new ObjectId($filter['value']);
              } catch (Throwable) {
              }
            } else {
              $conditions[$filter['by'][0]] = new Regex(htmlspecialchars(quotemeta($filter['value'])), 'i');
            }
          }
        }

      } else if ($filter['type'] == 'bool') {

        if ($filter['value'] == 'true') {
          $conditions[$filter['by'] ?? 'id'] = true;

        } else if ($filter['value'] == 'false') {
          $conditions[$filter['by'] ?? 'id'] = false;
        }

      } else if ($filter['type'] == 'model') {
        $conditions[$filter['by']] = $filter['value'];

      } else if ($filter['type'] == 'dateTime' || $filter['type'] == 'date') {

        $from = strtotime($filter['value']['from']);
        $to = strtotime($filter['value']['to']);

        $dateTime = [];

        if ($from) {
          $dateTime['$gte'] = $from;
        }
        if ($to) {
          $dateTime['$lte'] = $to;
        }

        if (count($dateTime)) {
          $conditions[$filter['by']] = $dateTime;
        }

      } else {
        $conditions[$filter['by']] = $filter['value'];
      }
    }

    return $conditions;
  }

  protected function getFilterWithValues(array $filter = []): array
  {
    if (!count($filter)) {
      $filter = $this->getParam('filter', []);
    }
    $filters = [];
    foreach ($this->getFilter() as $_filter) {
      if ($_filter['type'] == 'search') {
        $_filter['value'] = $filter[$_filter['type']] ?? $_filter['value'] ?? null;
      } else {
        $_filter['value'] = $filter[$_filter['by']] ?? $_filter['value'] ?? null;
      }
      $filters[] = $_filter;
    }
    return $filters;
  }

  protected function getFilter(): array
  {
    if ($filters = $this->getMods('filter')) {
      return $filters;
    }

    $filters = [];

    $modelClassName = $this->getModelClassName();

    /** @var ModelAbstract $model */
    /** @var ModelAbstract $modelClassName */
    $model = new $modelClassName();

    $searchBy = ['id'];

    if ($model->getMeta()->hasProperty('title')) {
      $searchBy[] = 'title';
    }

    if ($model->getMeta()->hasProperty('subTitle')) {
      $searchBy[] = 'subTitle';
    }

    if ($model->getMeta()->hasProperty('description')) {
      $searchBy[] = 'description';
    }

    if ($model->getMeta()->hasProperty('url')) {
      $searchBy[] = 'url';
    }

    if ($model->getMeta()->hasProperty('search')) {
      $searchBy[] = 'search';
    }

    if (count($searchBy)) {
      $filters[] = Filter::search(by: $searchBy);
    }

    if ($model->getMeta()->hasProperty('enabled')) {
      $filters[] = Filter::enabled();
    }
    if ($model->getMeta()->hasProperty('createdAt')) {
      $filters[] = Filter::createdAt();
    }

    $reflectionClass = new ReflectionClass($model);
    $constants = $reflectionClass->getConstants();

    foreach ($model->getMeta()->getProperties() as $property) {
      if ($property->isModel()) {
        $filters[] = Filter::model(by: $property->getName());
      }

      $options = [];
      $upperProp = strtoupper($property->getName());
      foreach ($constants as $constantName => $constantValue) {
        if (str_starts_with(strtoupper($constantName), $upperProp . '_')) {
          $title = ucfirst(strtolower(str_replace(['_', '-'], ' ', $constantValue)));
          $options[$title] = $constantValue;
        }
      }

      if (count($options)) {
        $filters[] = Filter::select(
          Header::getTitleBasedOnModelOrPropertyName($property->getName()),
          $property->getName(),
          options: $options
        );
      }
    }

    return $filters;
  }

  protected function getSorting(): array
  {
    $sorting = $this->getMods('sorting');
    if (count($sorting)) {
      return $sorting[0];
    }
    $defaultSort = [];

    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    $model = new $modelClassName();

    if ($model->getMeta()->hasProperty('position')) {
      $defaultSort = [
        'position' => 1
      ];
    }
    return array_merge($defaultSort, array_filter($this->getRequest()->getGet('sort', $defaultSort)));
  }

  protected function getHeader(): array
  {
    $headers = [];

    foreach ($this->getMods('header') as $header) {
      $headers[$header['by']] = $header;
    }

    if (!count($headers)) {

      $modelClassName = $this->getModelClassName();

      /** @var ModelAbstract $model */
      /** @var ModelAbstract $modelClassName */
      $model = new $modelClassName();

      if ($model->getMeta()->hasProperty('image')) {
        $headers[] = Header::image();
      }

      if ($model->getMeta()->hasProperty('images')) {
        $headers[] = Header::images();
      }

      if ($model->getMeta()->hasProperty('title') && $model->getMeta()->hasProperty('description')) {
        $headers[] = Header::title(Header::LG);
        $headers[] = Header::longtext(by: 'description');

      } else if ($model->getMeta()->hasProperty('title')) {
        $headers[] = Header::title();

      } else if ($model->getMeta()->hasProperty('description')) {
        $headers[] = Header::longtext(by: 'description', size: Header::XL);
      }

      $reflectionClass = new ReflectionClass($model);
      $constants = $reflectionClass->getConstants();

      foreach ($model->getMeta()->getProperties() as $property) {
        if ($property->isModel() && !$property->isMultiple()) {
          $headers[] = Header::model($property->getRawType(), by: $property->getName());
        }

        $options = [];
        $upperProp = strtoupper($property->getName());
        foreach ($constants as $constantName => $constantValue) {
          if (str_starts_with(strtoupper($constantName), $upperProp . '_')) {
            $title = ucfirst(strtolower(str_replace(['_', '-'], ' ', $constantValue)));
            $options[$title] = $constantValue;
          }
        }

        if (count($options)) {
          $headers[] = Header::source(
            Header::getTitleBasedOnModelOrPropertyName($property->getName()),
            fn(ModelAbstract $model) => badge($model->{$property->getName()})
          );
        }
      }

      if ($model->getMeta()->hasProperty('enabled')) {
        $headers[] = Header::enabled();
      }
      if ($model->getMeta()->hasProperty('createdAt') && $model->getMeta()->hasProperty('updatedAt')) {
        $headers[] = Header::createdAndUpdated();
      } else {
        if ($model->getMeta()->hasProperty('createdAt')) {
          $headers[] = Header::createdAt();
        }
        if ($model->getMeta()->hasProperty('updatedAt')) {
          $headers[] = Header::updatedAt();
        }
      }
    }
    return $headers;
  }

  protected function getControls(): array
  {
    $modelClassName = $this->getModelClassName();
    /** @var ModelAbstract $model */
    $model = new $modelClassName();

    $controls = [
      Control::view()
    ];

    if ($this->getManageable()) {
      $controls[] = Control::manage();
    }

    $controls[] = Control::copy();
    if (Language::isAvailable() && $model->getMeta()->hasProperty('language')) {
      $controls[] = Control::localizedCopy();
    }

    if ($this->getPrintable()) {
      $controls[] = Control::print();
    }

    if ($model->getMeta()->hasProperty('enabled')) {
      $controls[] = Control::enabled();
    }
    $customControls = ($this->getMods('controls') ?? []);
    if (count($customControls)) {
      $controls = [...$controls, ...$customControls];
    }
    return $controls;
  }

  protected function getPaginator(): Paginator
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    $paginator = new Paginator(
      new $modelClassName(),
      $this->getConditions(),
      $this->getSorting()
    );

    $page = $this->getParam('page', '1');

    if (!strlen($page)) {
      $page = 1;
    }

    $paginator->setPage(intval($page));

    $paginator->setItemsPerPage(
      $this->getItemsPerPage()
    );

    return $paginator;
  }

  protected function getItemsPerPage(): int
  {
    if ($itemsPerPage = $this->getMods('items-per-page')) {
      return intval($itemsPerPage);
    }
    return 50;
  }

  protected function getBlock(): ?string
  {
    return null;
  }

  public function index()
  {
    $this->adminLog(History::TYPE_READ_TABLE);

    $this->getView()->setVars([
      'icon' => $this->getIcon(),
      'title' => $this->getTitle(),
      'manageable' => $this->getManageable(),
      'manageableMultiple' => $this->getManageableMultiple(),
      'quickManage' => $this->getQuickManage(),
      'printable' => $this->getPrintable(),
      'positioning' => $this->getPositioning(),
      'exportable' => $this->getExportable(),

      'filter' => $this->getFilterWithValues(),
      'header' => $this->getHeader(),
      'headerButtons' => $this->getHeaderButtons(),
      'controls' => $this->getControls(),
      'paginator' => $this->getPaginator(),
      'block' => $this->getBlock(),
      'controller' => $this->getRouter()->getController(),
    ]);

    $this->getView()->setScript('table/index');
  }
}