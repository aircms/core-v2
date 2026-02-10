<?php

declare(strict_types=1);

namespace Air\Crud\Controller;

use Air\Crud\Controller\MultipleHelper\Accessor\Header;
use Air\Crud\Nav\Nav;
use Air\Crud\Nav\NavController;
use Air\Form\Form;
use Air\Form\Generator;
use Air\Form\Input;

class Font extends Multiple
{
  use NavController;

  protected function getNav(): string
  {
    return Nav::SETTINGS_FONTS;
  }

  protected function getHeader(): array
  {
    return [
      Header::title(),
      Header::enabled(),
    ];
  }

  /**
   * @param \Air\Crud\Model\Font $model
   * @return Form
   */
  protected function getForm($model = null): Form
  {
    return Generator::full($model, [
      'My font' => [
        Input::storage('eotIe9', allowNull: true),
        Input::storage('eotIe6Ie8', allowNull: true),
        Input::storage('otf', allowNull: true),
        Input::storage('woff2', allowNull: true),
        Input::storage('woff', allowNull: true),
        Input::storage('ttf', allowNull: true),
        Input::storage('svg', allowNull: true),
      ],
      'Google font' => [
        Input::text('googleFontName', description: 'Enter the name of the Google Font', allowNull: true),
        Input::text('googleFontImportUrl', description: 'Google font import URL', allowNull: true),
      ],
    ]);
  }

  public function css(): string
  {
    $this->getView()->setPath(realpath(__DIR__ . '/../View'));
    $this->getResponse()->setHeader('Content-type', 'text/css');

    $css = "";
    foreach (\Air\Crud\Model\Font::all() as $font) {
      $css .= $font->asCss();
    }
    return $css;
  }

  public function fonts(): string
  {
    $this->getView()->setLayoutEnabled(false);

    $fonts = [];
    foreach (\Air\Crud\Model\Font::all() as $font) {
      if ($font->isGoogleFont()) {
        $fonts[] = $font->title . '=' . $font->googleFontName;
      } else {
        $fonts[] = $font->title . '=' . $font->title;
      }
    }
    return implode('; ', $fonts);
  }
}
