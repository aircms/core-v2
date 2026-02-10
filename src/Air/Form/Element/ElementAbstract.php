<?php

declare(strict_types=1);

namespace Air\Form\Element;

use Air\Crud\Locale;
use Air\Exception\InvalidInput;
use Air\Filter\FilterAbstract;
use Air\Form\Exception\FilterClassWasNotFound;
use Air\Form\Exception\ValidatorClassWasNotFound;
use Air\Validator\ValidatorAbstract;
use Air\View\View;
use Exception;
use Throwable;

abstract class ElementAbstract
{
  public ?string $name = null;
  public mixed $value = null;
  public ?string $label = null;
  public ?string $description = null;
  public ?string $hint = null;
  public array $filters = [];
  public array $validators = [];
  public bool $allowNull = false;
  public array $errorMessages = [];
  public string $containerTemplate = 'form/element/partial/container';
  public string $errorTemplate = 'form/element/partial/error';
  public string $labelTemplate = 'form/element/partial/label';
  public ?string $elementTemplate = null;
  public ?View $view = null;
  public ?string $placeholder = null;
  public array $userOptions = [];

  public static function convertNameToLabel(string $name): string
  {
    return ucfirst(strtolower(implode(' ', preg_split('/(?=[A-Z])/', $name))));
  }

  public function __construct(string $name, array $userOptions = [])
  {
    $this->setName($name);
    foreach ($userOptions as $name => $value) {
      if (is_callable([$this, 'set' . ucfirst($name)])) {
        try {
          call_user_func_array([$this, 'set' . ucfirst($name)], [$value]);
        } catch (Throwable) {
        }
      }
    }

    if (!$this->getLabel() &&
      $this->getElementType() !== 'hidden' &&
      $this->getElementType() !== 'tab') {
      try {
        $this->setLabel(self::convertNameToLabel($this->getName()));
      } catch (Throwable) {
      }
    }

    $this->userOptions = $userOptions;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function setName(string $name): void
  {
    $this->name = $name;
  }

  public function getValue(): mixed
  {
    return $this->value;
  }

  public function getCleanValue(): mixed
  {
    return $this->getValue();
  }

  public function setValue(mixed $value): void
  {
    $this->value = $value;
  }

  public function getLabel(): ?string
  {
    return $this->label;
  }

  public function setLabel(string $label): void
  {
    $this->label = $label;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(string $description): void
  {
    $this->description = $description;
  }

  public function getHint(): ?string
  {
    return $this->hint;
  }

  public function setHint(string $hint): void
  {
    $this->hint = $hint;
  }

  public function addValidator(array $validator): void
  {
    $this->validators[] = $validator;
  }

  public function hasError(): bool
  {
    return (bool)count($this->getErrorMessages());
  }

  public function getErrorMessages(): array
  {
    return $this->errorMessages;
  }

  public function setErrorMessages(array $errorMessages): void
  {
    $this->errorMessages = $errorMessages;
  }

  public function getView(): View
  {
    return $this->view;
  }

  public function setView(View $view): void
  {
    $this->view = $view;
  }

  public function getUserOptions(): array
  {
    return $this->userOptions;
  }

  public function setUserOptions(array $userOptions): void
  {
    $this->userOptions = $userOptions;
  }

  public function getElementType(): string
  {
    $template = explode('\\', get_called_class());
    return strtolower($template[count($template) - 1]);
  }

  public function isValid($value): bool
  {
    $this->value = $value;

    if ($this->isAllowNull() && is_null($value)) {
      return true;
    }

    $this->errorMessages = [];

    foreach ($this->getValidators() as $validatorClassName => $settings) {

      if (is_int($validatorClassName)) {
        $validatorClassName = $settings;
      }

      if ($validatorClassName instanceof ValidatorAbstract) {
        if (!$validatorClassName->isValid($value)) {
          $this->errorMessages[] = $validatorClassName->getErrorMessage();
        }
        continue;
      }

      try {
        if (!class_exists($validatorClassName)) {
          throw new ValidatorClassWasNotFound($validatorClassName);
        }
      } catch (Throwable $exception) {

        if (is_array($settings) && isset($settings['isValid'])) {

          if (!$settings['isValid']($value)) {
            $this->errorMessages[] = $settings['message'] ?? '';
          }
          continue;

        } else if (is_callable($settings)) {
          $result = $settings($value);
          if ($result !== true) {
            $this->errorMessages[] = $result;
          }
          continue;
        }

        throw $exception;
      }

      $options = [];

      if (isset($settings['options'])) {
        $options = $settings['options'];

      } else if (is_array($settings)) {
        $options = $settings;
      }

      /** @var ValidatorAbstract $validator */
      $validator = new $validatorClassName($options ?? []);

      $validator->setAllowNull($this->isAllowNull());

      if (!$validator->isValid($value)) {
        $this->errorMessages[] = $settings['message'] ?? '';
      }
    }

    if (empty($value) && !$this->isAllowNull()) {
      $this->errorMessages[] = Locale::t('Could not be empty');
    }

    if (!count($this->errorMessages)) {

      foreach ($this->getFilters() as $filterClassName => $settings) {

        if (is_numeric($filterClassName)) {
          $filterClassName = $settings;
        }

        try {

          /** @var FilterAbstract $filter */

          if (is_string($filterClassName)) {
            $filter = new $filterClassName($settings['options'] ?? []);
            $this->value = $filter->filter($this->value);

          } else if ($filter instanceof FilterAbstract) {
            $this->value = $filter->filter($this->value);
          }

        } catch (Throwable $e) {
          try {
            $this->value = $filterClassName($this->value);

          } catch (Exception) {
            throw new FilterClassWasNotFound($filterClassName);
          }
        }
      }
      return true;
    }
    return false;
  }

  public function getValidators(): array
  {
    return $this->validators;
  }

  public function setValidators(array $validators): void
  {
    $this->validators = $validators;
  }

  public function isAllowNull(): bool
  {
    return $this->allowNull;
  }

  public function setAllowNull(bool $allowNull): void
  {
    $this->allowNull = $allowNull;
  }

  public function getFilters(): array
  {
    return $this->filters;
  }

  public function setFilters(array $filters): void
  {
    $this->filters = $filters;
  }

  public function getErrorTemplate(): string
  {
    return $this->errorTemplate;
  }

  public function setErrorTemplate(string $errorTemplate): void
  {
    $this->errorTemplate = $errorTemplate;
  }

  public function getLabelTemplate(): string
  {
    return $this->labelTemplate;
  }

  public function setLabelTemplate(string $labelTemplate): void
  {
    $this->labelTemplate = $labelTemplate;
  }

  public function getElementTemplate(): string
  {
    return $this->elementTemplate;
  }

  public function setElementTemplate(string $elementTemplate): void
  {
    $this->elementTemplate = $elementTemplate;
  }

  public function getPlaceholder(): ?string
  {
    return $this->placeholder;
  }

  public function setPlaceholder(string $placeholder): void
  {
    $this->placeholder = $placeholder;
  }

  public function __toString(): string
  {
    if (!$this->view) {
      $this->view = new View();
      $this->view->setPath(realpath(__DIR__ . '/../../Crud/View'));
      $this->view->setScript($this->getContainerTemplate());
    }

    $this->view->assign('element', $this);
    return $this->view->render();
  }

  public function getContainerTemplate(): string
  {
    return $this->containerTemplate;
  }

  public function setContainerTemplate(string $containerTemplate): void
  {
    $this->containerTemplate = $containerTemplate;
  }

  public function validateOfFail(mixed $value = null): mixed
  {
    if (!$this->isValid($value)) {
      throw new InvalidInput();
    }
    return $this->getValue();
  }

  public function init()
  {
  }
}
