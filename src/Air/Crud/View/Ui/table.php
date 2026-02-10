<?php

declare(strict_types=1);

function tbl(array $body = [], ?array $data = null, $footer = null): string
{
  $table = table(
    class: 'table table-hover align-middle mb-0 rounded-4 overflow-hidden',
    content: function () use ($data, $body) {

      yield thead(tr(
        class: 'rounded-4 overflow-hidden',
        content: function () use ($body) {
          foreach (array_keys($body) as $key) {
            yield th($key);
          }
        }));

      yield tbody(function () use ($data, $body) {
        if ($data) {
          foreach ($data as $row) {
            yield tr(function () use ($row, $body) {
              foreach ($body as $value) {
                if (is_callable($value)) {
                  $value = $value($row);
                }
                yield td($value);
              }
            });
          }
        } else {
          yield tr(function () use ($body) {
            foreach ($body as $value) {
              if (is_callable($value)) {
                $value = $value();
              }
              yield td($value);
            }
          });
        }
      });
    });

  if ($footer) {
    $table .= div(class: 'bg-body p-4 py-3 rounded-bottom-3', content: $footer);
  }

  return $table;
}