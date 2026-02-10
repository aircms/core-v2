<?php

declare(strict_types=1);

namespace Air\Model;

use Air\Model\Driver\CursorAbstract;

class Paginator
{
  private ?ModelAbstract $model;
  private ?array $cond;
  private ?array $sort;
  private int $page = 0;
  private int $itemsPerPage = 10;

  public function __construct(ModelAbstract $model, ?array $cond = null, ?array $sort = null)
  {
    $this->model = $model;
    $this->cond = $cond;
    $this->sort = $sort;
  }

  public function getItems(): CursorAbstract|array
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = get_class($this->model);

    $limit = $this->getItemsPerPage();
    $offset = ($this->getPage() - 1) * $limit;

    return $modelClassName::fetchAll($this->cond, $this->sort, $limit, $offset);
  }

  public function getItemsPerPage(): int
  {
    return $this->itemsPerPage;
  }

  public function setItemsPerPage(int $itemsPerPage): void
  {
    $this->itemsPerPage = $itemsPerPage;
  }

  public function getPage(): int
  {
    return $this->page;
  }

  public function setPage(int $page): void
  {
    $this->page = $page;
  }

  public function count(): int
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = get_class($this->model);
    return $modelClassName::count($this->cond);
  }

  public function calculate(): ?array
  {
    $totalCount = $this->model::count($this->cond);
    $currentPage = $this->page;
    $itemsPerPage = $this->itemsPerPage;
    $range = 5;

    if (!$totalCount) {
      return null;
    }

    $prev = $currentPage != 1 ? $currentPage - 1 : false;
    $totalPages = intval(ceil($totalCount / $itemsPerPage));
    $next = $currentPage < $totalPages ? $currentPage + 1 : false;

    $pages = range(1, $totalPages);

    if ($totalPages > $range) {

      $start = $currentPage - ceil($range / 2);

      if ($start < 0) {
        $start = 0;
      }

      if ($start + $range > $totalPages) {
        $start = $totalPages - $range;
      }

      $pages = array_slice($pages, intval($start), $range);
    }

    return [
      'prev' => $prev,
      'pages' => $pages,
      'next' => $next,
    ];
  }
}
