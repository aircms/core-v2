<?php

declare(strict_types=1);

namespace Air;

use Air\Model\Driver\CursorAbstract;
use Air\Model\ModelAbstract;
use Closure;

class PaginatorModel
{
  public int $totalCount = 0;

  public function __construct(
    public string|ModelAbstract $model,
    public array                $cond = [],
    public array                $sort = [],
    public int                  $page = 1,
    public int                  $itemsPerPage = 10,
    public int                  $range = 5,
    public bool                 $strict = true,
  )
  {
    if ($this->strict) {
      $this->totalCount = ($this->model)::quantity($this->cond);
    } else {
      $this->totalCount = ($this->model)::count($this->cond);
    }
  }

  public function calc(): array|false
  {
    $currentPage = $this->page;
    $totalCount = $this->totalCount;
    $itemsPerPage = $this->itemsPerPage;
    $range = $this->range;

    if ($totalCount <= $itemsPerPage) {
      return false;
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

      $pages = array_slice($pages, intval($start), intval($range));
    }

    return [
      'prev' => $prev,
      'next' => $next,
      'pages' => $pages,
      'page' => $currentPage,
    ];
  }

  public function getItems(): CursorAbstract
  {
    if ($this->strict) {
      return ($this->model)::all($this->cond, $this->sort, $this->itemsPerPage, $this->itemsPerPage * ($this->page - 1));
    }
    return ($this->model)::fetchAll($this->cond, $this->sort, $this->itemsPerPage, $this->itemsPerPage * ($this->page - 1));
  }
}
