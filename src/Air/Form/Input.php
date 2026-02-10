<?php

declare(strict_types=1);

namespace Air\Form;

use Air\Crud\Locale;
use Air\Filter\Email;
use Air\Filter\HtmlSpecialChars;
use Air\Filter\Phone;
use Air\Filter\StripTags;
use Air\Filter\Trim;
use Air\Form\Element\Checkbox;
use Air\Form\Element\Date;
use Air\Form\Element\DateTime;
use Air\Form\Element\ElementAbstract;
use Air\Form\Element\Embed;
use Air\Form\Element\FaIcon;
use Air\Form\Element\Group;
use Air\Form\Element\Hidden;
use Air\Form\Element\Icon;
use Air\Form\Element\KeyValue;
use Air\Form\Element\Meta;
use Air\Form\Element\Model;
use Air\Form\Element\MultipleGroup;
use Air\Form\Element\MultipleKeyValue;
use Air\Form\Element\MultipleModel;
use Air\Form\Element\MultiplePage;
use Air\Form\Element\MultipleText;
use Air\Form\Element\Page;
use Air\Form\Element\Permissions;
use Air\Form\Element\Quote;
use Air\Form\Element\RichContent;
use Air\Form\Element\Select;
use Air\Form\Element\SingleModel;
use Air\Form\Element\Social;
use Air\Form\Element\Storage;
use Air\Form\Element\Tab;
use Air\Form\Element\Text;
use Air\Form\Element\Textarea;
use Air\Form\Element\Time;
use Air\Form\Element\Tiny;
use Air\Form\Element\TreeModel;
use Air\Form\Element\Url;
use Air\Validator\StringLength;

/**
 * @method static Checkbox checkbox(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null, array $deactivate = [], bool $deactivateWhen = false): Checkbox
 * @method static Date date(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Time time(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static DateTime dateTime(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Embed embed(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static FaIcon faIcon(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Group group(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Hidden hidden(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Icon icon(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static KeyValue keyValue(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Meta meta(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Model model(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null, string $parent = null)
 * @method static SingleModel singleModel(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null, string $parent = null)
 * @method static MultipleGroup multipleGroup(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static MultipleKeyValue multipleKeyValue(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static MultipleModel multipleModel(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static MultiplePage multiplePage(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static MultipleText multipleText(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Page page(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Permissions permissions(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Quote quote(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static RichContent richContent(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Select select(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Storage storage(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Tab tab(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Text text(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Text number(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Url url(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Textarea textarea(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Tiny tiny(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static TreeModel treeModel(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Text phone(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Text email(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Text safe(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Text password(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 * @method static Social social(string $name = null, mixed $value = null, string $label = null, string $description = null, string $hint = null, array $filters = null, array $validators = null, bool $allowNull = null, string $placeholder = null, string $format = null, string $phpFormat = null, array $elements = null, string $keyLabel = 'Key', string $valueLabel = 'Value', string $keyPropertyName = 'key', string $valuePropertyName = 'value', string $model = null, string $field = null, array $options = null, string $type = null, bool $multiple = null)
 */
final class Input
{
  public static function __callStatic(string $name, array $arguments)
  {
    if ($name === 'number') {
      $name = 'text';
      $arguments['type'] = 'number';
    }

    if ($name === 'safe') {
      $name = 'text';
      $arguments['filters'] = [
        ...$arguments['filters'] ?? [],
        ...[Trim::class, HtmlSpecialChars::class, StripTags::class]
      ];
    }

    if ($name === 'password') {
      $name = 'text';
      $arguments['filters'] = [
        ...$arguments['filters'] ?? [],
        ...[Trim::class]
      ];
      $arguments['validators'] = [
        ...$arguments['validators'] ?? [],
        ...[
          StringLength::class => [
            'min' => 6,
            'max' => 255
          ],
        ],
      ];
    }

    if ($name === 'phone') {
      $name = 'text';
      $arguments['filters'] = [
        ...$arguments['filters'] ?? [],
        ...[Phone::class]
      ];
      $arguments['validators'] = [
        ...$arguments['validators'] ?? [],
        ...[\Air\Validator\Phone::class]
      ];
    }

    if ($name === 'email') {
      $name = 'text';
      $arguments['filters'] = [
        ...$arguments['filters'] ?? [],
        ...[Email::class]
      ];
      $arguments['validators'] = [
        ...$arguments['validators'] ?? [],
        ...[\Air\Validator\Email::class]
      ];
    }

    $inputClassName = 'Air\Form\Element\\' . ucfirst($name);
    $inputName = $arguments[0] ?? $arguments['name'] ?? '';
    unset($arguments[0]);
    unset($arguments['name']);

    if (!isset($arguments['label'])) {
      if (str_ends_with($inputName, 'ubTitle')) {
        $arguments['label'] = Locale::t('Sub title');

      } else if (str_ends_with($inputName, 'itle')) {
        $arguments['label'] = Locale::t('Title');

      } else if (str_ends_with($inputName, 'escription')) {
        $arguments['label'] = Locale::t('Description');

      } else {
        $arguments['label'] = Locale::t(ElementAbstract::convertNameToLabel($inputName));
      }
    }

    return new $inputClassName($inputName, $arguments);
  }
}
