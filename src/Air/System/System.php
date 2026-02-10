<?php

declare(strict_types=1);

namespace Air\System;

use DateTime;
use Exception;
use Throwable;

class System
{
  public static function uptime(bool $format = false): int|string
  {
    $uptime = explode(' ', self::readProc('uptime'));
    $uptime = intval(trim($uptime[0]));

    if (!$format) {
      return $uptime;
    }

    $createTime = 0;
    $currentTime = $uptime;

    $dtCurrent = DateTime::createFromFormat('U', (string)$currentTime);
    $dtCreate = DateTime::createFromFormat('U', (string)$createTime);
    $diff = $dtCurrent->diff($dtCreate);

    $interval = $diff->format("%y years %m months %d days %h hours %i minutes %s seconds");
    $interval = preg_replace('/(^0| 0) (years|months|days|hours|minutes|seconds)/', '', $interval);

    return trim($interval);
  }

  public static function disk(): array
  {
    return [
      'total' => number_format(disk_total_space('/') / 1024 / 1024 / 1024, 2),
      'free' => number_format(disk_free_space('/') / 1024 / 1024 / 1024, 2),
    ];
  }

  public static function memory(): array
  {
    $memInfo = [];
    $procMemInfo = self::readProc('meminfo');

    foreach (explode("\n", $procMemInfo) as $line) {
      try {
        $line = explode(':', $line);
        $key = trim($line[0]);
        $value = number_format(intval(array_values(array_filter(explode(' ', $line[1])))[0]) / 1024 / 1024, 2);
        $memInfo[$key] = $value;

      } catch (Exception) {
      }
    }
    return $memInfo;
  }

  public static function cpuLoadAverage(): float
  {
    $statData1 = self::getServerLoadLinuxData();
    sleep(1);
    $statData2 = self::getServerLoadLinuxData();

    if ($statData1 && $statData2) {

      $statData2[0] -= $statData1[0];
      $statData2[1] -= $statData1[1];
      $statData2[2] -= $statData1[2];
      $statData2[3] -= $statData1[3];

      $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];
      return 100 - ($statData2[3] * 100 / $cpuTime);
    }
    return 0;
  }

  public static function cpuCoreCount(): int
  {
    return substr_count(self::readProc('cpuinfo'), 'processor');
  }

  public static function cpuName(): string
  {
    $procCpuInfo = self::readProc('cpuinfo');
    $cpuInfo = [];

    foreach (explode("\n", $procCpuInfo) as $line) {
      try {
        $line = explode(':', $line);
        $key = trim($line[0]);
        $cpuInfo[$key] = trim($line[1]);
      } catch (Exception) {
      }
    }

    return implode(' ', [
      $cpuInfo['vendor_id'],
      $cpuInfo['model name'],
    ]);
  }

  public static function version(): string
  {
    return self::readProc('version');
  }

  private static function getServerLoadLinuxData(): ?array
  {
    $stats = preg_replace("/[[:blank:]]+/", " ", self::readProc('stat'));

    $stats = str_replace(["\r\n", "\n\r", "\r"], "\n", $stats);
    $stats = explode("\n", $stats);

    foreach ($stats as $statLine) {
      $statLineData = explode(" ", trim($statLine));
      if ((count($statLineData) >= 5) && ($statLineData[0] == "cpu")) {
        return [
          $statLineData[1],
          $statLineData[2],
          $statLineData[3],
          $statLineData[4],
        ];
      }
    }
    return null;
  }

  private static function readProc(string $proc): string
  {
    try {
      return file_get_contents('/proc/' . $proc);
    } catch (Throwable) {
      throw new SystemProcNotReadable($proc);
    }
  }
}
