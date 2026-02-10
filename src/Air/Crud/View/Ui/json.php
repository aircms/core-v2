<?php

declare(strict_types=1);

function json($json): string
{
  return textarea(value: json_encode($json), data: ['json-viewer']);
}