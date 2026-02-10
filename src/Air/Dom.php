<?php

declare(strict_types=1);

namespace Air;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;

class Dom
{
  private DOMDocument $doc;
  private DOMXPath $xpath;
  private ?DOMNode $node;

  public static function load(string $html, bool $isXml = false): static
  {
    return new static($html, $isXml);
  }

  public function __construct(string|DOMNode $input, bool $isXml = false, ?DOMDocument $doc = null, ?DOMXPath $xpath = null)
  {
    if ($input instanceof DOMNode) {
      if (!$doc || !$xpath) {
        throw new InvalidArgumentException("Internal use requires passing doc and xpath.");
      }
      $this->doc = $doc;
      $this->xpath = $xpath;
      $this->node = $input;

    } else {
      $this->doc = new DOMDocument();
      libxml_use_internal_errors(true);

      if ($isXml) {
        $this->doc->loadXML($input, LIBXML_NOERROR | LIBXML_NOWARNING);

      } else {
        $this->doc->loadHTML('<?xml encoding="utf-8"?>' . $input, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        $this->doc->encoding = 'UTF-8';
      }

      libxml_clear_errors();
      $this->xpath = new DOMXPath($this->doc);
      $this->node = null;
    }
  }

  /**
   * @param string $cssSelector
   * @return static[]
   */
  public function all(string $cssSelector): array
  {
    $xpathExpr = self::cssToXPath($cssSelector);
    $ctx = $this->node ?? $this->doc;
    $nodes = $this->xpath->query($xpathExpr, $ctx);
    $out = [];
    foreach ($nodes as $n) {
      $out[] = new static($n, false, $this->doc, $this->xpath);
    }
    return $out;
  }

  /**
   * @param string $cssSelector
   * @return $this|null
   */
  public function one(string $cssSelector): ?static
  {
    $xpathExpr = self::cssToXPath($cssSelector);
    $ctx = $this->node ?? $this->doc;
    $nodes = $this->xpath->query($xpathExpr, $ctx);
    if ($nodes->length === 0) {
      return null;
    }
    return new static($nodes->item(0), false, $this->doc, $this->xpath);
  }

  public function node(): DOMNode|DOMElement|null
  {
    return $this->node;
  }

  public function text(): string
  {
    $n = $this->node ?? $this->doc->documentElement;
    return trim($n->textContent ?? '');
  }

  public function html(?bool $clean = false): string
  {
    $n = $this->node ?? $this->doc->documentElement;

    if ($clean !== true) {
      $inner = '';
      foreach ($n->childNodes as $child) {
        $inner .= $this->doc->saveHTML($child);
      }
      return trim($inner);
    }

    $tmp = new DOMDocument('1.0', 'UTF-8');
    $tmp->formatOutput = false;

    $root = $tmp->appendChild($tmp->createElement('root'));
    foreach ($n->childNodes as $child) {
      $root->appendChild($tmp->importNode($child, true));
    }

    self::removeEmptyNodes($root);

    $out = '';
    foreach ($root->childNodes as $child) {
      $out .= $tmp->saveHTML($child);
    }
    return trim($out);
  }

  public function outerHtml(): string
  {
    $n = $this->node ?? $this->doc->documentElement;
    return $this->doc->saveHTML($n);
  }

  public function attr(string $name): ?string
  {
    $n = $this->node;
    if ($n instanceof DOMElement && $n->hasAttribute($name)) {
      return trim($n->getAttribute($name));
    }
    return null;
  }

  public function tag(): ?string
  {
    $n = $this->node;
    if ($n instanceof DOMElement) {
      return $n->tagName;
    }
    return null;
  }

  public function parent(?string $cssSelector = null): ?static
  {
    $n = $this->node;
    if (!$n) {
      return null;
    }

    if ($cssSelector === null) {
      $parent = $n->parentNode;
      if ($parent instanceof DOMElement) {
        return new static($parent, false, $this->doc, $this->xpath);
      }
      return null;
    }

    $selectorXPath = self::cssToXPath($cssSelector);
    $cur = $n->parentNode;
    while ($cur && $cur instanceof DOMElement) {
      $res = $this->xpath->query('.', $cur);
      $test = $this->xpath->query('self::' . self::stripContextPrefix($selectorXPath), $cur);
      if ($test && $test->length > 0) {
        return new static($cur, false, $this->doc, $this->xpath);
      }
      $cur = $cur->parentNode;
    }
    return null;
  }

  public function remove(string $cssSelector): static
  {
    $xpathExpr = self::cssToXPath($cssSelector);
    $ctx = $this->node ?? $this->doc;

    $nodes = $this->xpath->query($xpathExpr, $ctx);
    if ($nodes === false) {
      return $this;
    }

    $toRemove = [];
    foreach ($nodes as $n) {
      $toRemove[spl_object_hash($n)] = $n;
    }

    foreach ($toRemove as $n) {
      if ($n->parentNode instanceof DOMNode) {
        $n->parentNode->removeChild($n);
      }
    }

    return $this;
  }

  public static function cssToXPath(string $selector): string
  {
    $parts = preg_split('/\s*,\s*/', trim($selector));
    $xPaths = [];
    foreach ($parts as $part) {
      $xPaths[] = self::compileSelector(trim($part));
    }
    return implode('|', $xPaths);
  }

  private static function compileSelector(string $selector): string
  {
    $pattern = '/(\s*[>+~]\s*|\s+)/';
    $tokens = preg_split($pattern, $selector, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $xpath = '.';
    $pendingRelation = null;

    foreach ($tokens as $tok) {
      if (trim($tok) === '') {
        continue;
      }
      $trimmed = trim($tok);
      if (in_array($trimmed, ['>', '+', '~'])) {
        $pendingRelation = $trimmed;
        continue;
      }
      if (preg_match('/^\s+$/', $tok)) {
        $pendingRelation = ' '; // потомок
        continue;
      }

      $segment = self::simpleTokenToXPath($trimmed);

      if ($pendingRelation === null) {
        $xpath .= '//' . $segment;

      } else {
        if ($pendingRelation === '>') {
          $xpath .= '/' . $segment;

        } elseif ($pendingRelation === ' ') {
          $xpath .= '//' . $segment;

        } elseif ($pendingRelation === '+') {
          $xpath .= '/following-sibling::*[1][self::' . self::stripPredicate($segment) . self::extractPredicate($segment) . ']';

        } elseif ($pendingRelation === '~') {
          $xpath .= '/following-sibling::' . self::stripPredicate($segment) . self::extractPredicate($segment);
        }
      }
      $pendingRelation = null;
    }
    return preg_replace('#//+#', '//', $xpath);
  }

  private static function simpleTokenToXPath(string $token): string
  {
    $tag = '*';
    $predicates = [];

    if (preg_match('/#([A-Za-z0-9\-_]+)/', $token, $m)) {
      $predicates[] = sprintf('@id="%s"', $m[1]);
      $token = str_replace("#{$m[1]}", '', $token);
    }

    if (preg_match_all('/\.([A-Za-z0-9\-_]+)/', $token, $classMatches)) {
      foreach ($classMatches[1] as $cls) {
        $predicates[] = sprintf('contains(concat(" ", normalize-space(@class), " "), " %s ")', $cls);
        $token = str_replace(".{$cls}", '', $token);
      }
    }

    if (preg_match_all('/\[\s*([^\~\|\^\$\*\]=]+)\s*(?:([~\|\^\$\*]?=)\s*(?:"([^"]*)"|\'([^\']*)\'|([^\]\s]+)))?\s*\]/', $token, $attrMatches, PREG_SET_ORDER)) {
      foreach ($attrMatches as $am) {
        $attr = trim($am[1]);
        $operator = $am[2] ?? '';
        $value = $am[3] !== '' ? $am[3] : ($am[4] !== '' ? $am[4] : ($am[5] ?? null));

        if (!$operator) {
          $predicates[] = sprintf('@%s', $attr);
        } else {
          switch ($operator) {
            case '=':
              $predicates[] = sprintf('@%s="%s"', $attr, $value);
              break;
            case '~=':
              $predicates[] = sprintf('contains(concat(" ", normalize-space(@%s), " "), " %s ")', $attr, $value);
              break;
            case '^=':
              $predicates[] = sprintf('starts-with(@%s, "%s")', $attr, $value);
              break;
            case '$=':
              $predicates[] = sprintf('substring(@%s, string-length(@%s) - string-length("%s") + 1) = "%s"', $attr, $attr, $value, $value);
              break;
            case '*=':
              $predicates[] = sprintf('contains(@%s, "%s")', $attr, $value);
              break;
            case '|=':
              $predicates[] = sprintf('(@%s="%s" or starts-with(@%s, "%s-"))', $attr, $value, $attr, $value);
              break;
            default:
              $predicates[] = sprintf('@%s="%s"', $attr, $value);
          }
        }
        $token = str_replace($am[0], '', $token);
      }
    }

    $remaining = trim(preg_replace(['/#[A-Za-z0-9\-_]+/', '/\.[A-Za-z0-9\-_]+/'], '', $token));
    if ($remaining !== '') {
      $tag = $remaining;
    }

    $xpath = $tag;
    if ($predicates) {
      $xpath .= '[' . implode(' and ', $predicates) . ']';
    }
    return $xpath;
  }

  private static function stripPredicate(string $segment): string
  {
    return preg_replace('/\[[^\]]+\]/', '', $segment);
  }

  private static function extractPredicate(string $segment): string
  {
    if (preg_match('/(\[.*\])/', $segment, $m)) {
      return $m[1];
    }
    return '';
  }

  private static function stripContextPrefix(string $xpath): string
  {
    $part = explode('|', $xpath)[0];
    return preg_replace('#^\.\/*#', '', $part);
  }

  private static function removeEmptyNodes(DOMNode $node): void
  {
    for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
      /** @var DOMNode $child */
      $child = $node->childNodes->item($i);

      if ($child->hasChildNodes()) {
        self::removeEmptyNodes($child);
      }

      if ($child->nodeType === XML_COMMENT_NODE) {
        $node->removeChild($child);
        continue;
      }

      if ($child->nodeType === XML_TEXT_NODE) {
        if (self::isWhitespace($child->nodeValue ?? '')) {
          $node->removeChild($child);
        }
        continue;
      }

      if ($child instanceof DOMElement) {
        if (self::isElementEmpty($child)) {
          $node->removeChild($child);
        }
      }
    }
  }

  private static function isElementEmpty(DOMElement $el): bool
  {
    if (self::isVoidElement($el->tagName)) {
      return false;
    }

    if (!$el->hasChildNodes()) {
      return true;
    }

    foreach ($el->childNodes as $c) {
      if ($c->nodeType === XML_TEXT_NODE) {
        if (!self::isWhitespace($c->nodeValue ?? '')) {
          return false;
        }
      } elseif ($c->nodeType === XML_COMMENT_NODE) {
        continue;
      } elseif ($c instanceof DOMElement) {
        if (!self::isElementEmpty($c)) {
          return false;
        }
      } else {
        return false;
      }
    }

    return true;
  }

  private static function isWhitespace(string $s): bool
  {
    $s = preg_replace('/[\x{00A0}\x{2007}\x{202F}]/u', ' ', $s);
    return trim($s) === '';
  }

  private static function isVoidElement(string $tag): bool
  {
    static $void = [
      'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
      'link', 'meta', 'param', 'source', 'track', 'wbr'
    ];
    return in_array(strtolower($tag), $void, true);
  }
}