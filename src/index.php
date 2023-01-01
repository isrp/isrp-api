#!/usr/bin/env php
<?php
use Isrp\Service\Server;

use Psr\Http\Message\ServerRequestInterface;

use React\EventLoop\Loop;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;

if (PHP_SAPI != 'cli')
	throw new RuntimeException('Can only run in cli');

define('VENDOR_DIR', __DIR__ . '/../vendor');
require VENDOR_DIR . '/autoload.php';

// Instantiate the app
$settings = require __DIR__ . '/settings.php';
$dependencies = require __DIR__ . '/dependencies.php';
$app = Server::create(array_merge($settings, $dependencies));
require __DIR__ . '/middleware.php';

$host = '[::]';
$port = getenv("WEB_PORT") ?: 1280;

$loop = Loop::get();
$log = $app->getContainer()->get(Psr\Log\LoggerInterface::class);
$server = new HttpServer(function (ServerRequestInterface $request) use ($app, $log) {
	try {
		$time = (new DateTime)->format(DATE_ATOM);
		$ua = @($request->getHeader("User-Agent"))[0] ?? "-";
		$ref = @($request->getHeader("Referer"))[0] ?? "-";
		
		$start = microtime(true);
		$res = $app->handle($request);
		$duration = round(microtime(true) - $start, 3);
		
		$req = "{$request->getMethod()} {$request->getUri()} HTTP/{$request->getProtocolVersion()}";
		$resdata = "{$res->getStatusCode()} {$res->getBody()->getSize()}";
		$log->info("- - - $time \"$req\" $resdata \"{$ref}\" \"{$ua}\" {$duration}");
		return $res;
	} catch (Throwable $e) {
		$log->error("Error in server: {$e->getMessage()}:".PHP_EOL.$e->getTraceAsString());
		die;
	}
});

$log->info("Starting ISRP API server on $host:$port");
$server->listen(new SocketServer("$host:$port"));

$loop->run();
