<?php
// DIC configuration
return [
	// monolog
	\Psr\Log\LoggerInterface::class => DI\factory(function (Psr\Container\ContainerInterface $c) {
		$logger = new Monolog\Logger('isrp-api');
		$logger->pushProcessor(new Monolog\Processor\UidProcessor());
		$settings = $c->get('logger');
		switch ($settings['type']) {
			case 'syslog':
				$logger->pushHandler(new Monolog\Handler\SyslogHandler($settings['name'] ?? 'isrp-api'));
				break;
			case 'file':
				$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level'] ?? \Monolog\Logger::DEBUG));
				break;
			default:
				$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::DEBUG));
		}
		return $logger;
	}),
];
