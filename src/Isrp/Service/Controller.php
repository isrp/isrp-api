<?php
namespace Isrp\Service;

abstract class Controller {
	
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const DELETE = 'DELETE';
	const PATCH = 'PATCH';
	
	/**
	 * @var \Slim\App
	 */
	protected $app;
	
	public function __construct(\Slim\App $app) {
		$this->app = $app;
	}
	
	protected function get($diType) {
		return $this->app->getContainer()->get($diType);
	}
	
	protected function logger() : \Monolog\Logger {
		return $this->get('logger');
	}
	
	protected function debug($msg, ...$vars) {
		$this->logger()->debug($msg, $vars);
	}
	
	protected function info($msg, ...$vars) {
		$this->logger()->info($msg, $vars);
	}
	
	protected function notice($msg, ...$vars) {
		$this->logger()->notice($msg, $vars);
	}
	
	protected function warn($msg, ...$vars) {
		$this->logger()->warn($msg, $vars);
	}
	
	protected function error($msg, ...$vars) {
		$this->logger()->error($msg, $vars);
	}
	
	protected function alert($msg, ...$vars) {
		$this->logger()->alert($msg, $vars);
	}
	
	protected function emergency($msg, ...$vars) {
		$this->logger()->emergency($msg, $vars);
	}
	
	protected function critical($msg, ...$vars) {
		$this->logger()->critical($msg, $vars);
	}
	
	/**
	 * @return RouteConfiguration[] routes to configure
	 */
	abstract public function getRoutes();
}

