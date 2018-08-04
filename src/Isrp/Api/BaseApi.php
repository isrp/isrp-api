<?php
namespace Isrp\Api;

use Isrp\Service\Controller;
use Isrp\Service\RouteConfiguration;
use Slim\Http\Request;
use Slim\Http\Response;

class BaseApi extends Controller {

	
	public function getRoutes() {
		return [
			new RouteConfiguration(static::GET, "/", "keepalive")
		];
	}
	
	public function keepalive(Request $req, Response $res) {
		$this->info("Hello");
		return $res->write("hello\n");
	}
	
}

