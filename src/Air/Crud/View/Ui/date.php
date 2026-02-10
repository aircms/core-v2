<?php

declare(strict_types=1);

function duration(
  int  $seconds,
  bool $showDays = true,
  bool $showHours = true,
  bool $showMinutes = true,
  bool $showSeconds = true
): string
{
  $parts = [];

  $days = intdiv($seconds, 86400);
  $seconds %= 86400;
  $hours = intdiv($seconds, 3600);
  $seconds %= 3600;
  $minutes = intdiv($seconds, 60);
  $seconds %= 60;

  if ($days > 0 && $showDays) {
    $parts[] = "$days day";
  }
  if ($hours > 0 && $showHours) {
    $parts[] = "$hours hour";
  }
  if ($minutes > 0 && $showMinutes) {
    $parts[] = "$minutes minute";
  }
  if (($seconds > 0 || empty($parts)) && $showSeconds) {
    $parts[] = "$seconds second";
  }

  return implode(' ', $parts);
}

function isToday(int $timestamp): bool
{
  return date("Y-m-d") === date("Y-m-d", $timestamp);
}

function isTomorrow(int $timestamp): bool
{
  return date("Y-m-d", strtotime('tomorrow')) === date("Y-m-d", $timestamp);
}

function format(?int $timestamp = null, string $format = 'Y/m/d H:i'): string
{
  return date($format, $timestamp ?? 0);
}