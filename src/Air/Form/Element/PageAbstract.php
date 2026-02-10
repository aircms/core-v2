<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;

abstract class PageAbstract extends ElementAbstract
{
  public array $sizes = [
    [
      'title' => '',
      'size' => [
        'width' => \Air\Type\Page::WIDTH,
        'height' => \Air\Type\Page::HEIGHT,
      ],
    ],
    [
      'title' => '',
      'size' => [
        'width' => \Air\Type\Page::HEIGHT,
        'height' => \Air\Type\Page::WIDTH,
      ],
    ]
  ];

  public array $gutters = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

  public function __construct(string $name, array $userOptions = [])
  {
    $this->sizes[0]['title'] = Locale::t('A4 portrait');
    $this->sizes[1]['title'] = Locale::t('A4 landscape');

    parent::__construct($name, $userOptions);
  }

  public function getSizes(): array
  {
    return $this->sizes;
  }

  public function setSizes(array $sizes): void
  {
    $this->sizes = $sizes;
  }

  public function getGutters(): array
  {
    return $this->gutters;
  }

  public function setGutters(array $gutters): void
  {
    $this->gutters = $gutters;
  }
}