<?php
// DIC configuration
$container = $app->getContainer();

// // view renderer
// $container['renderer'] = function ($c) {
//     $settings = $c->get('settings')['renderer'];
//     return new Slim\Views\PhpRenderer($settings['template_path']);
// };

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    switch ($settings['type']) {
    	case 'syslog':
    		$logger->pushHandler(new Monolog\Handler\SyslogHandler($settings['name']));
    		break;
    	case 'file':
    		$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    		break;
    	default:
    		$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stderr', \Monolog\Logger::DEBUG));
    }
    
    return $logger;
};
