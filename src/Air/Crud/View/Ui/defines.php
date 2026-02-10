<?php

declare(strict_types=1);

const PRIMARY = 'primary';
const SECONDARY = 'secondary';
const TERTIARY = 'tertiary';
const SUCCESS = 'success';
const INFO = 'info';
const WARNING = 'warning';
const DANGER = 'danger';
const LIGHT = 'light';
const DARK = 'dark';
const SURFACE = 'surface';

const SM = 'sm';
const MD = 'md';
const LG = 'lg';

const SPACE1 = '1';
const SPACE2 = '2';
const SPACE3 = '3';
const SPACE4 = '4';
const SPACE5 = '5';

const START = 'start';
const END = 'end';
const BETWEEN = 'between';
const CENTER = 'center';

function getFontSize(string $size = MD): ?string
{
  return match ($size) {
    SM => 'fs-12',
    MD => 'fs-16',
    LG => 'fs-20',
    default => null,
  };
}

function getTextColor(?string $color = null): ?string
{
  return $color ? 'text-' . $color : null;
}

function getBgColor(?string $color = null): ?string
{
  return $color ? 'body-bg-' . $color : null;
}