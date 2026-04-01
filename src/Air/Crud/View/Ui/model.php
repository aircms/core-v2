<?php

declare(strict_types=1);

use Air\Model\ModelAbstract;

function modelPreview(ModelAbstract $model): string
{
  $image = null;
  if ($model->getMeta()->hasProperty('image')) {
    $image = image($model->image);
  } else if ($model->getMeta()->hasProperty('images')) {
    $image = image($model->image[0]);
  }

  $title = null;
  if ($model->getMeta()->hasProperty('title')) {
    $title = span($model->title);
  } else if ($model->getMeta()->hasProperty('name')) {
    $title = span($model->name);
  }

  return horizontal(space: 2, align: CENTER, content: [$image, $title]);
}