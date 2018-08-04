<?php
namespace Isrp\Service;

class RouteConfiguration {
	
	private $method;
	private $path;
	private $action;
	
	/**
	 * @param string $method HTTP method to implement (case insensitive)
	 * @param string $path Path to mount the action
	 * @param string $action Method name to call on the controller
	 */
	public function __construct(string $method, string $path, string $action) {
		$this->method = $method;
		$this->path = $path;
		$this->action = $action;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getAction($object) {
		return [ $object, $this->action ];
	}
}

