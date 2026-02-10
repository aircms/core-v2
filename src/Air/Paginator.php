<?php

declare(strict_types=1);

namespace Air;

use Closure;

class Paginator
{
  /**
   * @var array|null
   */
  private ?array $items;

  /**
   * @var array
   */
  private array $filteredItems = [];

  /**
   * @var Closure|null
   */
  private ?Closure $filter = null;

  /**
   * @var int
   */
  private int $page = 1;

  /**
   * @var int
   */
  private int $itemsPerPage = 10;

  /**
   * @var int
   */
  private int $range = 5;

  /**
   * @param array|null $items
   * @param callable|null $filter
   * @param int|null $page
   * @param int|null $itemsPerPage
   */
  public function __construct(?array $items = [], ?callable $filter = null, ?int $page = 1, ?int $itemsPerPage = 10)
  {
    $this->setItems($items);
    $this->setFilter($filter);
    $this->setPage($page);
    $this->setItemsPerPage($itemsPerPage);
  }

  /**
   * @return array
   */
  public function getItems(): array
  {
    return $this->items;
  }

  /**
   * @param array $items
   */
  public function setItems(array $items): void
  {
    $this->items = $items;
  }

  /**
   * @return Closure|null
   */
  public function getFilter(): ?Closure
  {
    return $this->filter;
  }

  /**
   * @param Closure|null $filter
   */
  public function setFilter(?Closure $filter): void
  {
    $this->filter = $filter;
  }

  /**
   * @return int
   */
  public function getPage(): int
  {
    return $this->page;
  }

  /**
   * @param int $page
   */
  public function setPage(int $page): void
  {
    if ($page) {
      $this->page = $page;
    }
  }

  /**
   * @return int
   */
  public function getItemsPerPage(): int
  {
    return $this->itemsPerPage;
  }

  /**
   * @param int $itemsPerPage
   */
  public function setItemsPerPage(int $itemsPerPage): void
  {
    $this->itemsPerPage = $itemsPerPage;
  }

  /**
   * @return int
   */
  public function getRange(): int
  {
    return $this->range;
  }

  /**
   * @param int $range
   */
  public function setRange(int $range): void
  {
    $this->range = $range;
  }

  /**
   * @return array|null
   */
  public function getFilteredItems(): ?array
  {
    if (!$this->filteredItems) {
      if ($filter = $this->getFilter()) {
        $this->filteredItems = array_filter($this->getItems(), $filter, ARRAY_FILTER_USE_BOTH);
      } else {
        $this->filteredItems = $this->getItems();
      }
    }
    return $this->filteredItems;
  }

  /**
   * @return int
   */
  public function getFilteredCount(): int
  {
    return count($this->getFilteredItems());
  }

  /**
   * @param array|null $filteredItems
   */
  public function setFilteredItems(?array $filteredItems): void
  {
    $this->filteredItems = $filteredItems;
  }

  /**
   * @return array
   */
  public function getPageItems(): array
  {
    return array_slice(
      $this->getFilteredItems(),
      $this->getItemsPerPage() * ($this->getPage() - 1),
      $this->getItemsPerPage()
    );
  }

  /**
   * @return array|null
   */
  public function calculate(): ?array
  {
    $totalCount = count($this->getFilteredItems());
    $currentPage = $this->getPage();
    $itemsPerPage = $this->getItemsPerPage();
    $range = $this->getRange();

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
