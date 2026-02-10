<?php

declare(strict_types=1);

namespace Air\Crud\Nav;

use Air\Core\Front;
use Air\Crud\Controller\Admin;
use Air\Crud\Controller\Billing;
use Air\Crud\Controller\Codes;
use Air\Crud\Controller\Deepl;
use Air\Crud\Controller\DeepSeek;
use Air\Crud\Controller\DeepSeekLog;
use Air\Crud\Controller\EmailQueue;
use Air\Crud\Controller\EmailSettings;
use Air\Crud\Controller\EmailTemplate;
use Air\Crud\Controller\FontsUi;
use Air\Crud\Controller\GoogleTranslate;
use Air\Crud\Controller\History;
use Air\Crud\Controller\Language;
use Air\Crud\Controller\Log;
use Air\Crud\Controller\OpenAi;
use Air\Crud\Controller\OpenAiLog;
use Air\Crud\Controller\Phrase;
use Air\Crud\Controller\RobotsTxt;
use Air\Crud\Controller\SmsQueue;
use Air\Crud\Controller\SmsSettings;
use Air\Crud\Controller\SmsTemplate;
use Air\Crud\Controller\Storage;
use Air\Crud\Controller\System;
use Air\Type\FaIcon;

class Nav
{
  const string SETTINGS_STORAGE = 'storage';
  const string SETTINGS_FONTS = 'fonts';
  const string SETTINGS_SYSTEM = 'system';
  const string SETTINGS_LOGS = 'logs';
  const string SETTINGS_LANGUAGES = 'languages';
  const string SETTINGS_PHRASES = 'Phrases';
  const string SETTINGS_GOOGLE_TRANSLATE = 'googleTranslate';
  const string SETTINGS_DEEPL = 'deepl';
  const string SETTINGS_DEEPSEEK = 'deepSeek';
  const string SETTINGS_DEEPSEEK_LOG = 'deepSeekLog';
  const string SETTINGS_OPENAI = 'openai';
  const string SETTINGS_OPENAI_LOG = 'openaiLog';
  const string SETTINGS_BILLING = 'billing';
  const string SETTINGS_ADMINISTRATORS = 'administrators';
  const string SETTINGS_ADMINISTRATORS_HISTORY = 'administratorsHistory';
  const string SETTINGS_CODES = 'codes';
  const string SETTINGS_ROBOTSTXT = 'robotsTxt';
  const string SETTINGS_EMAIL_SETTINGS = 'emailSettings';
  const string SETTINGS_EMAIL_TEMPLATES = 'emailTemplates';
  const string SETTINGS_EMAIL_QUEUE = 'emailQueue';
  const string SETTINGS_SMS_SETTINGS = 'SMSSettings';
  const string SETTINGS_SMS_TEMPLATES = 'SMSTemplates';
  const string SETTINGS_SMS_QUEUE = 'SMSQueue';
  const string SETTINGS_FAICON = 'faIcon';

  const array SETTINGS = [
    self::SETTINGS_STORAGE => [
      'controller' => Storage::class,
      'icon' => FaIcon::ICON_FOLDER,
      'title' => 'File storage'
    ],
    self::SETTINGS_FONTS => [
      'controller' => FontsUi::class,
      'icon' => FaIcon::ICON_FONT,
      'title' => 'Fonts'
    ],
    self::SETTINGS_SYSTEM => [
      'controller' => System::class,
      'icon' => FaIcon::ICON_PIE_CHART,
      'title' => 'System'
    ],
    self::SETTINGS_LOGS => [
      'controller' => Log::class,
      'icon' => FaIcon::ICON_LIST,
      'title' => 'Logs'
    ], self::SETTINGS_LANGUAGES => [
      'controller' => Language::class,
      'icon' => FaIcon::ICON_GLOBE,
      'title' => 'Languages'
    ],
    self::SETTINGS_PHRASES => [
      'controller' => Phrase::class,
      'icon' => FaIcon::ICON_LANGUAGE,
      'title' => 'Phrases'
    ],
    self::SETTINGS_GOOGLE_TRANSLATE => [
      'controller' => GoogleTranslate::class,
      'icon' => FaIcon::ICON_LANGUAGE,
      'title' => 'Google Translate'
    ],
    self::SETTINGS_DEEPL => [
      'controller' => Deepl::class,
      'icon' => FaIcon::ICON_LANGUAGE,
      'title' => 'Deepl'
    ],
    self::SETTINGS_DEEPSEEK => [
      'controller' => DeepSeek::class,
      'icon' => FaIcon::ICON_ROBOT,
      'title' => 'Deep seek'
    ],
    self::SETTINGS_DEEPSEEK_LOG => [
      'controller' => DeepSeekLog::class,
      'icon' => FaIcon::ICON_LIST,
      'title' => 'Deep seek log'
    ],
    self::SETTINGS_OPENAI => [
      'controller' => OpenAi::class,
      'icon' => FaIcon::ICON_ROBOT,
      'title' => 'Open AI'
    ],
    self::SETTINGS_OPENAI_LOG => [
      'controller' => OpenAiLog::class,
      'icon' => FaIcon::ICON_LIST,
      'title' => 'Open AI log'
    ],
    self::SETTINGS_BILLING => [
      'controller' => Billing::class,
      'icon' => FaIcon::ICON_MONEY_BILL,
      'title' => 'Billing'
    ],
    self::SETTINGS_ADMINISTRATORS => [
      'controller' => Admin::class,
      'icon' => FaIcon::ICON_USERS,
      'title' => 'Administrators'
    ],
    self::SETTINGS_ADMINISTRATORS_HISTORY => [
      'controller' => History::class,
      'icon' => FaIcon::ICON_CLOCK,
      'title' => 'Administrator history'
    ],
    self::SETTINGS_CODES => [
      'controller' => Codes::class,
      'icon' => FaIcon::ICON_CODE,
      'title' => 'Codes'
    ],
    self::SETTINGS_ROBOTSTXT => [
      'controller' => RobotsTxt::class,
      'icon' => FaIcon::ICON_USER_ROBOT,
      'title' => 'Robots.txt'
    ],
    self::SETTINGS_EMAIL_SETTINGS => [
      'controller' => EmailSettings::class,
      'icon' => FaIcon::ICON_COGS,
      'title' => 'Email / Settings'
    ],
    self::SETTINGS_EMAIL_TEMPLATES => [
      'controller' => EmailTemplate::class,
      'icon' => FaIcon::ICON_FILES,
      'title' => 'Email / Templates'
    ],
    self::SETTINGS_EMAIL_QUEUE => [
      'controller' => EmailQueue::class,
      'icon' => FaIcon::ICON_DATABASE,
      'title' => 'Email / Queue'
    ],
    self::SETTINGS_SMS_SETTINGS => [
      'controller' => SmsSettings::class,
      'icon' => FaIcon::ICON_COGS,
      'title' => 'SMS / Settings'
    ],
    self::SETTINGS_SMS_TEMPLATES => [
      'controller' => SmsTemplate::class,
      'icon' => FaIcon::ICON_FILES,
      'title' => 'SMS / Templates'
    ],
    self::SETTINGS_SMS_QUEUE => [
      'controller' => SmsQueue::class,
      'icon' => FaIcon::ICON_DATABASE,
      'title' => 'SMS / Queue'
    ],
    self::SETTINGS_FAICON => [
      'controller' => \Air\Crud\Controller\FaIcon::class,
      'icon' => FaIcon::ICON_ICONS,
      'title' => 'Font awesome icons'
    ],
  ];

  private static ?array $settingsMenuItems = null;

  public static function getSettingItems(): array
  {
    if (self::$settingsMenuItems) {
      return self::$settingsMenuItems;
    }

    $settings = Front::getInstance()->getConfig()['air']['admin']['settings'] ?? [];
    self::$settingsMenuItems = [];

    foreach ($settings as $settingGroup) {
      if (is_array($settingGroup)) {
        $item = [];
        foreach ($settingGroup as $setting) {
          if (is_string($setting)) {
            if (isset(self::SETTINGS[$setting])) {
              $item[] = [
                ...self::SETTINGS[$setting],
                'alias' => '_' . $setting,
                'name' => $setting,
              ];
            }
          } else if (is_array($setting)) {
            $item[] = $setting;
          }
        }
        self::$settingsMenuItems[] = $item;
      }
    }
    return self::$settingsMenuItems;
  }

  public static function getSettingsItem(string $setting): ?array
  {
    foreach (self::getSettingItems() as $group) {
      foreach ($group as $item) {
        if ($item['name'] === $setting) {
          return $item;
        }
      }
    }
    return null;
  }

  public static function getSettingsItemWithAlias(string $alias): ?array
  {
    foreach (self::getSettingItems() as $group) {
      foreach ($group as $item) {
        if ($item['alias'] === $alias) {
          return $item;
        }
      }
    }
    return null;
  }

  public static function getAllSettings(): array
  {
    return [
      [
        self::SETTINGS_STORAGE,
        self::SETTINGS_FONTS,
        self::SETTINGS_SYSTEM,
        self::SETTINGS_LOGS,
        self::SETTINGS_FAICON,
      ],
      [
        self::SETTINGS_LANGUAGES,
        self::SETTINGS_PHRASES,
      ],
      [
        self::SETTINGS_DEEPSEEK,
        self::SETTINGS_DEEPSEEK_LOG,
      ],
      [
        self::SETTINGS_OPENAI,
        self::SETTINGS_OPENAI_LOG,
      ],
      [
        self::SETTINGS_GOOGLE_TRANSLATE,
        self::SETTINGS_DEEPL,
        self::SETTINGS_BILLING,
      ],
      [
        self::SETTINGS_ADMINISTRATORS,
        self::SETTINGS_ADMINISTRATORS_HISTORY,
      ],
      [
        self::SETTINGS_CODES,
        self::SETTINGS_ROBOTSTXT,
      ],
      [
        self::SETTINGS_EMAIL_SETTINGS,
        self::SETTINGS_EMAIL_TEMPLATES,
        self::SETTINGS_EMAIL_QUEUE,
      ],
      [
        self::SETTINGS_SMS_SETTINGS,
        self::SETTINGS_SMS_TEMPLATES,
        self::SETTINGS_SMS_QUEUE,
      ],
    ];
  }

  public static function item(
    string  $title,
    string  $icon = FaIcon::ICON_COGS,
    ?string $controller = null,
    ?array  $items = null,
    ?string $action = null,
    ?array  $params = null,
  ): array
  {
    $url = ['controller' => $controller];

    if ($action) {
      $url['action'] = $action;
    }

    if ($params) {
      $url['params'] = $params;
    }

    $nav = [
      'icon' => $icon,
      'title' => $title,
      'url' => $url
    ];

    if ($items) {
      $nav['items'] = $items;
    }

    return $nav;
  }

  public static function divider(): string
  {
    return 'divider';
  }
}