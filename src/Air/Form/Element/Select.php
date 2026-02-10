<?php

declare(strict_types=1);

namespace Air\Form\Element;

class Select extends ElementAbstract
{
  public ?string $elementTemplate = 'form/element/select';
  public array $options = [];

  public function getOptions(): array
  {
    return $this->options;
  }

  public function setOptions(array $options): void
  {
    $formattedOptions = [];

    if (count($options)) {
      if (isset($options[0]['title'])) {
        $formattedOptions = $options;

      } else if (isset($options[0])) {
        foreach ($options as $option) {
          $formattedOptions[] = [
            'title' => ucfirst($option),
            'value' => $option
          ];
        }
      } else {
        foreach ($options as $title => $value) {
          $formattedOptions[] = [
            'title' => $title,
            'value' => $value
          ];
        }
      }
    }

    $this->options = $formattedOptions;
  }
}
