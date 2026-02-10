<?php

declare(strict_types=1);

function hidden(
  $name = null,
  $value = null,
  $attributes = [],
): string
{
  return tag(
    tagName: 'input',
    attr: [
      ...$attributes,
      ...[
        'name' => $name,
        'value' => $value,
        'type' => 'hidden',
      ]
    ]
  );
}

function checkbox(
  $name = null,
  $value = null,
  $checked = false,
  $attributes = [],
  $data = [],
  $class = [],
): string
{
  return tag(
    tagName: 'input',
    class: $class,
    data: $data,
    attr: [
      ...$attributes,
      ...[
        'name' => $name,
        'value' => $value,
        'type' => 'checkbox',
        $checked ? 'checked' : null
      ]
    ]
  );
}

function button(
  $content = null,
  $attributes = [],
  $data = [],
  $class = [],
  bool $isSubmit = false
): string
{
  $attributes = (array)$attributes;
  $attributes['type'] = $isSubmit ? 'submit' : 'button';

  return tag(
    tagName: 'button',
    class: $class,
    attr: $attributes,
    data: $data,
    content: $content
  );
}

function text(
  $name = null,
  $value = null,
  $class = null,
  $attributes = [],
  $data = [],
  $placeholder = null,
): string
{
  return tag(
    tagName: 'input',
    class: $class,
    data: $data,
    attr: [
      ...$attributes,
      ...[
        'value' => $value,
        'name' => $name,
        'placeholder' => $placeholder
      ]
    ]
  );
}

function textarea(
  $name = null,
  $value = null,
  $class = null,
  $attributes = [],
  $data = [],
  $placeholder = null,
): string
{
  return tag(
    tagName: 'textarea',
    class: $class,
    data: $data,
    content: $value,
    attr: [
      ...$attributes,
      ...[
        'name' => $name,
        'placeholder' => $placeholder
      ]
    ]
  );
}

function tel(
  $name = null,
  $value = null,
  $class = null,
  $attributes = [],
  $placeholder = null,
): string
{
  return tag(
    tagName: 'input',
    class: $class,
    attr: [
      ...$attributes,
      ...[
        'value' => $value,
        'name' => $name,
        'type' => 'tel',
        'placeholder' => $placeholder
      ]
    ]
  );
}

function number(
  $name = null,
  $value = null,
  $class = null,
  $attributes = [],
  $placeholder = null,
): string
{
  return tag(
    tagName: 'input',
    class: $class,
    attr: [
      ...$attributes,
      'value' => $value,
      'name' => $name,
      'type' => 'number',
      'placeholder' => $placeholder
    ]
  );
}

function email(
  $name = null,
  $value = null,
  $class = null,
  $attributes = [],
  $placeholder = null,
): string
{
  return tag(
    tagName: 'input',
    class: $class,
    attr: [
      ...$attributes,
      ...[
        'value' => $value,
        'name' => $name,
        'type' => 'email',
        'placeholder' => $placeholder
      ]
    ]
  );
}

function select(
  $name = null,
  $value = null,
  $options = null,
  $class = null,
  $attributes = [],
): string
{
  $opts = [];
  $assoc = !!empty($options[0]);

  foreach ($options as $optionValue => $optionTitle) {
    if ($assoc) {
      $selected = $value === $optionValue;
    } else {
      $selected = $value === $optionTitle;
    }

    $opts[] = tag(
      tagName: 'option',
      attr: $selected ? ['selected' => 'selected'] : null,
      content: $optionTitle
    );
  }

  return div(
    class: 'select',
    content: tag(
      tagName: 'select',
      content: $opts,
      class: $class,
      attr: [
        ...$attributes,
        ...['name' => $name,]
      ],
    )
  );
}