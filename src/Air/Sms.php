<?php

declare(strict_types=1);

namespace Air;

use Air\Crud\Model\SmsQueue;
use Air\Crud\Model\SmsSettings;
use Air\Crud\Model\SmsTemplate;
use Air\Model\Driver\Mysql\Driver;
use Air\ThirdParty\Gatewayapi;
use Air\ThirdParty\Smsto;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

class Sms
{
  public static function add(
    string      $toAddress,
    SmsTemplate $template,
    array       $vars = [],
    bool        $force = false,
    int         $when = 0
  ): void
  {
    $sms = new SmsQueue([
      'when' => $when > 0 ? $when : time(),
      'status' => SmsQueue::STATUS_NEW,
      'toAddress' => $toAddress,
      'message' => self::render($template->message, $vars),
    ]);
    $sms->save();
    if ($force) {
      self::send($sms);
    }
  }

  protected static function render(string $text, array $vars = []): string
  {
    foreach ($vars as $k => $v) {
      $text = str_replace('{' . $k . '}', $v, $text);
    }
    return $text;
  }

  public static function send(SmsQueue $message): bool
  {
    $settings = SmsSettings::one();

    $gateWay = null;

    if ($settings->gateway === SmsSettings::GATEWAY_SMSTO) {
      $gateWay = new Smsto(
        $settings->smstoApiKey,
        $settings->smstoSenderId
      );
    }

    if ($settings->gateway === SmsSettings::GATEWAY_GATEWAYAPI) {
      $gateWay = new Gatewayapi(
        $settings->gatewayapiApiKey,
        $settings->gatewayapiSender
      );
    }

    if ($gateWay) {
      try {
        $response = $gateWay->send($message->toAddress, $message->message);
        $message->debugOutput = json_encode($response);
        $message->status = SmsQueue::STATUS_SUCCESS;
        $message->save();
        return true;

      } catch (Throwable $e) {
        $message->debugOutput = var_export($e, true);
        $message->status = SmsQueue::STATUS_FAIL;
        $message->save();
        return false;
      }
    }

    return false;
  }

  public static function addPlain(
    string $toAddress,
    string $message,
    array  $vars = [],
    int    $when = 0,
    bool   $force = false,
  ): void
  {
    $sms = new SmsQueue([
      'when' => $when > 0 ? $when : time(),
      'status' => SmsQueue::STATUS_NEW,
      'toAddress' => $toAddress,
      'message' => self::render($message, $vars),
    ]);
    $sms->save();
    if ($force) {
      self::send($sms);
    }
  }

  public static function consume(int $chunk = 50): bool
  {
    if (!SmsSettings::one()?->smsQueueEnabled) {
      return false;
    }

    $smsQueue = SmsQueue::fetchAll(
      ['when' => ['$lte' => time()], 'status' => SmsQueue::STATUS_NEW],
      ['when' => -1],
      $chunk
    );

    foreach ($smsQueue as $queue) {
      self::send($queue);
      unset($queue);
    }
    unset($smsQueue);
    return true;
  }
}