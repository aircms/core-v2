<?php

declare(strict_types=1);

namespace Air\Crud\Controller\MultipleHelper;

use Air\Crud\Locale;
use Air\Model\Driver\CursorAbstract;
use Air\Model\ModelAbstract;
use Air\Crud\Controller\MultipleHelper\Accessor\Header;

trait Export
{
  protected function getExportable(): bool
  {
    return !!$this->getExportHeader();
  }

  protected function getExportFileName(): string
  {
    return $this->getEntity();
  }

  protected function getExportData(): array|CursorAbstract
  {
    /** @var ModelAbstract $modelClassName */
    $modelClassName = $this->getModelClassName();

    /** @var ModelAbstract $table */
    return $modelClassName::fetchAll(
      $this->getConditions(),
      $this->getSorting()
    );
  }

  protected function getExportHeader(): ?array
  {
    return null;
  }

  public function export(): string
  {
    $this->getView()->setLayoutEnabled(false);

    ini_set('memory_limit', '-1');
    set_time_limit(0);

    $response = [];
    $header = $this->getExportHeader();

    $headers = [];
    foreach ($header as $h) {
      $headers[] = $h['title'];
    }

    $response[] = '"' . implode('","', $headers) . '"';

    foreach ($this->getExportData() as $row) {
      $cols = [];

      foreach ($header as $h) {

        $cols[] = match ($h['type'] ?? false) {
          Header::TEXT => $row->{$h['by']},
          Header::LONGTEXT => mb_substr($row->{$h['by']}, 0, 150),
          Header::BOOL => $row->{$h['by']} ? Locale::t('Yes') : Locale::t('No'),
          Header::DATETIME => date('Y-m-d H:i', $row->{$h['by']}),
          Header::DATE => date('Y-m-d', $row->{$h['by']}),
          Header::MODEL => $row->{$h['by']}->title,
          Header::SOURCE => $h['source']($row),
        };
      }

      $response[] = '"' . implode('","', $cols) . '"';
    }

    $response = implode("\n", $response);

    $fileName = $this->getExportFileName() . '_' . date('c') . '.csv';
    $this->getResponse()->setHeader('Content-Disposition', 'attachment;filename=' . $fileName);
    $this->getResponse()->setHeader('Content-Size', (string)mb_strlen($response));

    return $response;
  }
}