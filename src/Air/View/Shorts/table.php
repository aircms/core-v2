<?php

declare(strict_types=1);

function table($content = null, $class = null, $attributes = [], $data = []): string
{
  return tag('table', $content, $class, $attributes, $data);
}

function thead($content = null, $class = null, $attributes = null, $data = null): string
{
  return tag('thead', $content, $class, $attributes, $data);
}

function th($content = null, $class = null, $attributes = null, $data = null): string
{
  return tag('th', $content, $class, $attributes, $data);
}

function tbody($content = null, $class = null, $attributes = null, $data = null): string
{
  return tag('tbody', $content, $class, $attributes, $data);
}

function tr($content = null, $class = null, $attributes = null, $data = null): string
{
  return tag('tr', $content, $class, $attributes, $data);
}

function td($content = null, $class = null, $attributes = null, $data = null,): string
{
  return tag('td', $content, $class, $attributes, $data);
}