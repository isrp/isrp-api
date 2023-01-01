<?php
namespace Isrp\Api;

use Isrp\Service\Controller;
use Isrp\Service\RouteConfiguration;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;

class BaseApi extends Controller {
	
	public function getRoutes() {
		return [
			new RouteConfiguration(static::GET, "/", "keepalive")
		];
	}
	
	public function keepalive(Request $req, Response $res, array $args) {
		$this->info("Hello");
		$res->write("hello\n");
		return $res;
	}
	
}

