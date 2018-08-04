<?php
namespace Isrp\Service;

use CrazyGoat\SlimReactor\SlimReactorApp;

use Isrp\Api\DragonClub;
use Isrp\Api\BaseApi;

class Server extends SlimReactorApp {
	
	function __construct($settings) {
		parent::__construct($settings);
		$this->mount("/", new BaseApi($this));
		$this->mount("/club", new DragonClub($this));
	}
	
	public function mount($path, Controller $controller) {
		foreach ($controller->getRoutes() as $route) {
			$method = $route->getMethod();
			$callpath = str_replace("//", "/", $path . $route->getPath());
			echo "Seting up $method $callpath\n";
			$this->$method($callpath, $route->getAction($controller));
		}
	}
}

