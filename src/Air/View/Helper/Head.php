<?php

declare(strict_types=1);

namespace Air\View\Helper;

use Air\Core\Front;
use Air\Crud\Model\Font;
use Air\Crud\Controller\Codes;

class Head extends HelperAbstract
{
  public function call(
    string        $charset = 'UTF-8',
    string        $viewport = 'width=device-width, initial-scale=1.0, minimum-scale=1.0',
    ?string       $favicon = null,
    string|array  $assets = null
  ): string
  {
    $baseHref = Front::getInstance()->getConfig()['air']['asset']['prefix'];

    $head = [
      Codes::render(),
      tag('meta', attr: ['charset' => $charset]),
      tag('meta', attr: ['name' => 'viewport', 'content' => $viewport]),
      tag('base', attr: ['href' => $baseHref . '/']),
      $this->getView()->getMeta()
    ];

    if (Font::quantity()) {
      $head[] = tag('link', attr: [
        'rel' => 'stylesheet',
        'href' => Font::href()
      ]);
    }

    if ($favicon) {
      $faviconNameParts = explode('.', $favicon);
      $ext = $faviconNameParts[count($faviconNameParts) - 1];

      $head[] = tag('link', attr: [
        'rel' => 'icon',
        'type' => 'image/' . $ext,
        'href' => $this->getView()->asset($favicon)
      ]);
    }

    if ($assets) {
      $head[] = $this->getView()->asset($assets);
    }

    return implode('', array_filter($head));
  }
}
