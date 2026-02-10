<?php

declare(strict_types=1);

namespace Air;

class Csv
{
  /**
   * Преобразует CSV-текст в массив.
   * Опции:
   *   delimiter (string) — разделитель, по умолчанию ','
   *   enclosure (string) — обёртка, по умолчанию '"'
   *   escapeChar (string) — символ экранирования, по умолчанию '\'
   *   hasHeader (bool) — есть ли заголовок, по умолчанию true
   *
   * @param string $csvText
   * @param array $opts
   * @return array
   */
  public static function toArray(string $csvText, array $opts = []): array
  {
    $delimiter   = $opts['delimiter']   ?? ',';
    $enclosure   = $opts['enclosure']   ?? '"';
    $escapeChar  = $opts['escapeChar']  ?? '\\';
    $hasHeader   = $opts['hasHeader']   ?? true;

    $result = [];
    $fp = fopen('php://memory', 'r+');
    // Убираем BOM, если есть, и незначащие начальные пустые строки не ломают заголовок
    $cleaned = preg_replace('/^\xEF\xBB\xBF/', '', $csvText); // BOM
    fwrite($fp, $cleaned);
    rewind($fp);

    $header = null;
    if ($hasHeader) {
      // Пропускаем пустые/пустые по содержанию строки, чтобы найти реальный заголовок
      while (($possible = fgetcsv($fp, 0, $delimiter, $enclosure, $escapeChar)) !== false) {
        // считать строку заголовка, только если она не пустая (не все элементы пусты)
        $allEmpty = true;
        foreach ($possible as $cell) {
          if (strlen(trim((string)$cell)) > 0) {
            $allEmpty = false;
            break;
          }
        }
        if ($allEmpty) {
          continue; // пропустить
        }
        $header = $possible;
        break;
      }
      if ($header === null) {
        fclose($fp);
        return [];
      }
    }

    while (($row = fgetcsv($fp, 0, $delimiter, $enclosure, $escapeChar)) !== false) {
      // Пропускаем полностью пустые строки
      $allEmpty = true;
      foreach ($row as $cell) {
        if (strlen(trim((string)$cell)) > 0) {
          $allEmpty = false;
          break;
        }
      }
      if ($allEmpty) {
        continue;
      }

      if ($hasHeader) {
        $assoc = [];
        foreach ($header as $i => $colName) {
          $assoc[$colName] = $row[$i] ?? '';
        }
        $result[] = $assoc;
      } else {
        $result[] = $row;
      }
    }

    fclose($fp);
    return $result;
  }

  /**
   * Преобразует массив обратно в CSV-текст.
   * Опции:
   *   delimiter (string) — разделитель, по умолчанию ','
   *   enclosure (string) — обёртка, по умолчанию '"'
   *   escapeChar (string) — символ экранирования, по умолчанию '\'
   *   lineEnding (string) — конец строки, по умолчанию "\n"
   *   includeHeader (bool) — включать заголовок если ассоциативный массив, по умолчанию true
   *
   * @param array $array
   * @param array $opts
   * @return string
   */
  public static function fromArray(array $array, array $opts = []): string
  {
    $delimiter      = $opts['delimiter']      ?? ',';
    $enclosure      = $opts['enclosure']      ?? '"';
    $escapeChar     = $opts['escapeChar']     ?? '\\';
    $lineEnding     = $opts['lineEnding']     ?? "\n";
    $includeHeader  = $opts['includeHeader']  ?? true;

    if (empty($array)) {
      return '';
    }

    $fp = fopen('php://memory', 'r+');
    $first = $array[0];
    $isAssoc = is_array($first) && array_keys($first) !== range(0, count($first) - 1);

    if ($isAssoc) {
      if ($includeHeader) {
        fputcsv($fp, array_keys($first), $delimiter, $enclosure, $escapeChar);
      }
      foreach ($array as $row) {
        $ordered = [];
        if ($includeHeader) {
          foreach (array_keys($first) as $k) {
            $ordered[] = $row[$k] ?? '';
          }
        } else {
          $ordered = array_values($row);
        }
        fputcsv($fp, $ordered, $delimiter, $enclosure, $escapeChar);
      }
    } else {
      foreach ($array as $row) {
        fputcsv($fp, $row, $delimiter, $enclosure, $escapeChar);
      }
    }

    rewind($fp);
    $csv = stream_get_contents($fp);
    fclose($fp);

    if ($lineEnding !== "\n") {
      $csv = str_replace("\n", $lineEnding, $csv);
    }

    return $csv;
  }

  /**
   * Ищет строки по критериям.
   * Опции:
   *   delimiter, enclosure, escapeChar, hasHeader — как в toArray
   *
   * @param string $csvText
   * @param array $criteria ассоциативно: поле => значение
   * @param array $opts
   * @return array
   */
  public static function search(string $csvText, array $criteria = [], array $opts = []): array
  {
    // если все ключи в criteria — числовые строки, то подразумеваем no header
    $allKeysNumeric = $criteria !== [] && array_reduce(array_keys($criteria), fn($carry, $k) => $carry && ctype_digit((string)$k), true);
    if ($allKeysNumeric) {
      $opts['hasHeader'] = false;
    }
    $hasHeader = $opts['hasHeader'] ?? true;

    $all = self::toArray($csvText, $opts);
    if (empty($criteria)) {
      return $all;
    }

    // нормализованные критерии: сохраняем информацию о типе (exact vs contains)
    $normCriteria = [];
    foreach ($criteria as $k => $v) {
      $key = $hasHeader ? $k : (int)$k;
      $raw = trim((string)$v);
      $lower = mb_strtolower($raw);
      $isContains = false;
      $pattern = $lower;

      if (preg_match('/^\$\{(.+)\}$/u', $lower, $m)) {
        $isContains = true;
        $pattern = $m[1]; // внутренность для substring
      }

      $normCriteria[$key] = [
        'value' => $pattern,
        'contains' => $isContains,
      ];
    }

    $filtered = array_filter($all, function ($row) use ($normCriteria, $hasHeader) {
      foreach ($normCriteria as $key => $info) {
        $expected = $info['value'];
        $isContains = $info['contains'];

        if ($hasHeader) {
          if (!array_key_exists($key, $row)) {
            return false;
          }
          $cell = mb_strtolower(trim((string)$row[$key]));
        } else {
          if (!isset($row[$key])) {
            return false;
          }
          $cell = mb_strtolower(trim((string)$row[$key]));
        }

        if ($isContains) {
          if (mb_stripos($cell, $expected) === false) {
            return false;
          }
        } else {
          if ($cell !== $expected) {
            return false;
          }
        }
      }
      return true;
    });

    return array_values($filtered);
  }
}
