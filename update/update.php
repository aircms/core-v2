<?php

require_once 'vendor/autoload.php';
require_once __DIR__ . '/UpdateManager.php';

$env = getenv('AIR_ENV') ?: 'dev';

if ($env === 'dev') {
  $config = array_replace_recursive(
    require_once 'config/live.php',
    require_once 'config/dev.php'
  );
} else {
  $config = require_once 'config/live.php';
}

if (!($config['air']['updates'] ?? false)) {
  return;
}

\Air\Core\Front::getInstance($config);

UpdateManager::reflectSchema();

$updateManager = UpdateManager::fetchObject();

foreach (glob($config['air']['updates'] . '/*.php') as $file) {
  $lastUpdate = DateTime::createFromFormat('Y-m-d-H-i-s', basename($file, '.php'))->getTimestamp();

  if ($lastUpdate > $updateManager->lastUpdate) {
    require_once $file;
  }
}

if (isset($lastUpdate)) {
  $updateManager->lastUpdate = $lastUpdate;
  $updateManager->save();
}