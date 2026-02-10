<?php

declare(strict_types=1);

function properties(array $keyValues = []): string
{
  return row(
    class: 'gy-2',
    content: function () use ($keyValues) {
      foreach (array_filter($keyValues) as $key => $value) {
        $collClass = 12;
        if (!is_int($key)) {
          $collClass = 6;
          yield col(col: $collClass, class: 'small', content: content($key));
        }
        yield col(col: $collClass, content: content($value ?? ''));
      }
    }
  );
}