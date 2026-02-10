<?php

declare(strict_types=1);

namespace Air\Crud\Model;

use Air\Core\Exception\ClassWasNotFound;
use Air\Core\Front;
use Air\Http\Request;
use Air\Model\ModelAbstract;
use Air\Type\File;
use Exception;

/**
 * @collection AirFont
 *
 * @property string $id
 *
 * @property string $title
 *
 * @property File $eotIe9
 * @property File $eotIe6Ie8
 * @property File $otf
 * @property File $woff2
 * @property File $woff
 * @property File $ttf
 * @property File $svg
 *
 * @property string $googleFontName
 * @property string $googleFontImportUrl
 *
 * @property boolean $enabled
 */
class Font extends ModelAbstract
{
  public function asCss(): string
  {
    if ($this->isGoogleFont()) {
      $css = Request::getQuery($this->googleFontImportUrl);
      if ($css->isOk()) {
        return $css->body;
      }
      return '';
    }

    $css =
      "@font-face {
        font-family: '" . $this->title . "';
      ";

    if ($this->eotIe9) {
      $css .= " src: url('" . $this->eotIe9->getSrc() . "');";
    }

    $urls = "";

    if ($this->eotIe6Ie8) {
      $urls .= " url('" . $this->eotIe6Ie8->getSrc() . "?#iefix') format('embedded-opentype'),";
    }

    if ($this->otf) {
      $urls .= " url('" . $this->otf->getSrc() . "') format('opentype'),";
    }

    if ($this->woff2) {
      $urls .= " url('" . $this->woff2->getSrc() . "') format('woff2'),";
    }

    if ($this->woff) {
      $urls .= " url('" . $this->woff->getSrc() . "') format('woff'),";
    }

    if ($this->ttf) {
      $urls .= " url('" . $this->ttf->getSrc() . "') format('truetype'),";
    }

    if ($this->svg) {
      $urls .= " url('" . $this->svg->getSrc() . "#svgFontName') format('svg'),";
    }

    if ($urls) {
      $css .= "src: " . $urls;
    }

    $css .= ";}";

    return $css;
  }

  public function isGoogleFont(): bool
  {
    return !!strlen($this->googleFontName) && !!strlen($this->googleFontImportUrl);
  }

  public static function href(): string|false
  {
    $fontsUi = Front::getInstance()->getConfig()['air']['fontsUi'] ?? false;
    if ($fontsUi) {
      return '/' . $fontsUi;
    }
    return false;
  }
}