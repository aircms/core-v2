<?php

if (count($argv) !== 5) {
  die(implode("\n", [
    'How to use Generator:',
    'composer run-script gen {fontawesome icon} {type: "multiple" | "single"} {Section} {Name}',
    '-----',
    'Examples:',
    '- composer run-script gen users multiple Blog Author',
    '- composer run-script gen pages single "Home page settings" "Meta settings"',
    '-----',
  ]));
}

require_once "src/Air/Crud/Generator.php";

$env = getenv('AIR_ENV') ?: 'dev';

if ($env === 'dev') {
  $config = array_replace_recursive(
    require_once 'config/live.php',
    require_once 'config/dev.php'
  );
} else {
  $config = require_once 'config/live.php';
}

$generator = new \Air\Crud\Generator($config, $argv[1], $argv[2], $argv[3], $argv[4]);

echo implode("\n", [
  'Model: ' . ($generator->model() ? 'true' : 'false'),
  'Controller: ' . ($generator->controller() ? 'true' : 'false'),
  'Config: ' . ($generator->config() ? 'true' : 'false'),
  ""
]);

