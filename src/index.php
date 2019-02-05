#!/usr/bin/env php
<?php
use CrazyGoat\SlimReactor\{SlimReactor, SlimReactorApp};
use Isrp\Service\Server;
use React\EventLoop\Factory;

if (PHP_SAPI != 'cli') {
	throw new RuntimeException('Can only run in cli');
}

define('VENDOR_DIR', __DIR__ . '/../vendor');
require VENDOR_DIR . '/autoload.php';

// Instantiate the app

$settings = require __DIR__ . '/settings.php';

$app = new Server($settings);

require __DIR__ . '/dependencies.php';
require __DIR__ . '/middleware.php';

$app->start(__DIR__.'/../public');

$app->addDailyTask(function(){
	echo "Running daily";
});
