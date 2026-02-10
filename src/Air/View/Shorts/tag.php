<?php

declare(strict_types=1);

use Air\Type\File;

// require_once "renderer.php";

function tag(string $tagName, $content = null, $class = null, $attr = null, $data = null, $bgImage = null): string
{
  $tagName = trim(strtolower($tagName));

  $class = (array)$class;
  $attr = array_filter((array)$attr);
  $data = array_filter((array)$data);

  if ($bgImage) {
    $class[] = 'bg-image';
    $attr['style'] = (array)($attr['style'] ?? []) ?? [];
    $attr['style'][] = "background-image: url('" . src($bgImage) . "')";
    $attr['style'] = implode('; ', $attr['style']);
  }

  if (count($class)) {
    $class = 'class="' . implode(' ', array_filter($class)) . '"';
  }

  foreach ($data as $key => $value) {
    if (!is_int($key)) {
      $attr[] = 'data-' . $key . '="' . $value . '"';
    } else {
      $attr[] = 'data-' . $value;
    }
  }

  foreach ($attr as $key => $value) {
    if (!is_int($key)) {
      $attr[] = $key . '="' . $value . '"';
      unset($attr[$key]);
    } else {
      $attr[] = $value;
    }
  }

  $attr = implode(' ', array_filter($attr));

  $html = ['<' . implode(' ', array_filter([$tagName, $class, $attr]))];

  if (!isNonClosingTag($tagName)) {
    $html[] = '>';

  } else {
    $html[] = ' />';
    return implode('', array_filter($html, fn($item) => $item !== null));
  }

  try {
    $html[] = content($content);
  } catch (Throwable $e) {
    echo "----Опять проблемы с контентом в TAG----";
    throw $e;
    var_dump($content);
    die();
  }

  $html[] = '</' . $tagName . '>';

  return implode('', array_filter($html, fn($item) => $item !== null));
}

function div($content = null, $class = null, $attr = null, $data = null, $bgImage = null): string
{
  return tag('div', $content, $class, $attr, $data, $bgImage);
}

function pre($content = null, $class = null, $attr = null, $data = null): string
{
  return tag('pre', $content, $class, $attr, $data);
}

function span($content = null, $class = null, $attr = null, $data = null, $bgImage = null): string
{
  return tag('span', $content, $class, $attr, $data, $bgImage);
}

function form(
  $content = null,
  $class = null,
  $attr = null,
  $data = null,
  string $method = 'get',
  string $action = '',
  mixed $bgImage = null
): string
{
  $attr = (array)$attr ?? [];

  if ($method) {
    $attr['method'] = $method;
  }

  if ($action) {
    $attr['action'] = $action;
  }

  return tag('form', $content, $class, $attr, $data, $bgImage);
}

function i($content = null, $class = null, $attributes = null,): string
{
  return tag('i', $content, $class, $attributes);
}

function a(
  $href = null,
  $content = null,
  $class = null,
  $attr = null,
  $data = null,
  $bgImage = null,
  ?string $title = null,
  bool $openInNewWindow = false
): string
{
  $attr = (array)$attr ?? [];
  $data = (array)$data ?? [];

  $attr['href'] = $href ?? "#";

  if (is_string($content) && !$title) {
    $attr['title'] = htmlspecialchars(mb_substr(strip_tags($content), 0, 100));
  } else if ($title) {
    $attr['title'] = htmlspecialchars($title);
  }

  if ($openInNewWindow) {
    $attr['target'] = '_blank';
  }

  return tag('a', $content, $class, $attr, $data, $bgImage);
}

function ul($content = null, $class = null, $attr = null,): string
{
  return tag('ul', $content, $class, $attr);
}

function li($content = null, $class = null, $attr = null, $bgImage = null): string
{
  return tag('li', $content, $class, $attr, $bgImage);
}

function script($content = null, $src = null, $attr = null, $defer = null, $async = null): string
{
  $attr = (array)$attr ?? [];
  $attr['src'] = $src;
  $attr['defer'] = $defer ? true : null;
  $attr['async'] = $async ? true : null;

  return tag('script', $content, null, $attr);
}

function img(
  $src = null,
  $class = null,
  $attr = null,
  $data = null,
  ?string $alt = null,
  ?string $title = null,
  int $width = 0,
  int $height = 0,
): string
{
  $attr = (array)$attr ?? [];
  $attr['width'] = $width;
  $attr['height'] = $height;

  $attr['src'] = $src instanceof File ? $src->getSrc() : $src;

  if ($src instanceof File) {
    $alt = $alt ?? $src->getAlt();
    $title = $title ?? $src->getTitle();
  }

  $attr['alt'] = $alt;
  $attr['title'] = $title;

  return tag('img', null, $class, $attr, $data);
}

function h1($content = null, $class = null, $attr = null): string
{
  return tag('h1', $content, $class, $attr);
}

function h2($content = null, $class = null, $attr = null): string
{
  return tag('h2', $content, $class, $attr);
}

function h3($content = null, $class = null, $attr = null): string
{
  return tag('h3', $content, $class, $attr);
}

function h4($content = null, $class = null, $attr = null): string
{
  return tag('h4', $content, $class, $attr);
}

function h5($content = null, $class = null, $attr = null): string
{
  return tag('h5', $content, $class, $attr);
}

function h6($content = null, $class = null, $attr = null): string
{
  return tag('h6', $content, $class, $attr);
}

function iframe($src = null, $class = null, $attr = null): string
{
  $attr = $attr ?? [];
  $attr['src'] = $src;

  return tag('iframe', null, $class, $attr);
}

function doctype(): string
{
  return '<!DOCTYPE html>';
}

function video($src = null, $class = null, $attributes = null): string
{
  $type = 'video/mp4';

  if ($src instanceof File) {
    $type = $src->getMime();
    $src = $src->getSrc();
  }

  return tag('video', tag('source', attr: ['src' => $src, 'type' => $type]), $class, $attributes);
}

function br(): string
{
  return tag('br');
}

function hr($class = null): string
{
  return tag('hr', class: $class);
}

function p($content = null, $class = null, $attributes = null, $data = null): string
{
  return tag('p', $content, $class, $attributes, $data);
}

function base(string $href = '/'): string
{
  return tag('base', attr: ['href' => $href]);
}

function body($content, $class = null, $attr = null, $data = null): string
{
  return tag('body', $content, $class, $attr, $data);
}

function head($content): string
{
  return tag('head', $content);
}

function main($content, $class = null, $attr = null, $data = null): string
{
  return tag('main', $content, $class, $attr, $data);
}