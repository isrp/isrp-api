#!/usr/bin/env php
<?php
use CrazyGoat\SlimReactor\{SlimReactor, SlimReactorApp};
use Isrp\Service\Server;

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

$host = '[::]';
$port = 1280;

echo "Starting ISRP API server on $host:$port\n";

$slimReactor  = new SlimReactor(
	$app,
	[
		'socket' => "$host:$port",
		'staticContentPath' => __DIR__.'/../public'
	]
	);
$slimReactor->run();
