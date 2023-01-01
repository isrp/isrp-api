<?php
namespace Isrp\Service;

use Psr\Http\Message\{ResponseFactoryInterface,StreamFactoryInterface,RequestInterface,ResponseInterface};

use DI\ContainerBuilder;

use Slim\App;
use Slim\Factory\Psr17\Psr17FactoryProvider;
use Slim\Factory\Psr17\SlimHttpPsr17Factory;
use Slim\Handlers\ErrorHandler;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Middleware\ContentLengthMiddleware;

use Monolog\Logger;

use Isrp\Api\DragonClub;
use Isrp\Api\BaseApi;

class Server extends App {
	
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $log;
	
	public static function create(array $settings) : App {
		$psr17FactoryProvider = new Psr17FactoryProvider();
		foreach ($psr17FactoryProvider->getFactories() as $psr17factory) {
			if (!$psr17factory::isResponseFactoryAvailable())
				continue;
			$responseFactory = $psr17factory::getResponseFactory();
			
			if ($psr17factory::isStreamFactoryAvailable()) {
				$streamFactory = $psr17factory::getStreamFactory();
				$responseFactory = static::attemptResponseFactoryDecoration($responseFactory, $streamFactory);
			}
			
			return new Server($responseFactory, (new ContainerBuilder)->addDefinitions($settings)->build());
		}
		
		throw new RuntimeException(
			"Could not detect any PSR-17 ResponseFactory implementations. " .
			"Please install a supported implementation in order to use `AppFactory::create()`. " .
			"See https://github.com/slimphp/Slim/blob/4.x/README.md for a list of supported implementations."
		);
	}
	
	protected static function attemptResponseFactoryDecoration(ResponseFactoryInterface $responseFactory,
			StreamFactoryInterface $streamFactory) : ResponseFactoryInterface {
		error_log("Creating decorated response factory");
		return new \Slim\Http\Factory\DecoratedResponseFactory($responseFactory, $streamFactory);
	}
	
	function __construct() {
		parent::__construct(...func_get_args());
		
		// $router = $this->addRoutingMiddleware();
		$errHandler = $this->addErrorMiddleware(true, true, true);
		$errHandler->setDefaultErrorHandler([$this, 'errorHandler']);
		$this->add(new ContentLengthMiddleware());
		
		$container = $this->getContainer();
		$this->log = $container->get(\Psr\Log\LoggerInterface::class);
		
		$container->set(\Slim\App::class, $this);
		$this->mount("/", $container->get(BaseApi::class));
		$this->mount("/club", $container->get(DragonClub::class));
	}
	
	public function mount($path, Controller $controller) {
		foreach ($controller->getRoutes() as $route) {
			$method = $route->getMethod();
			$callpath = str_replace("//", "/", $path . $route->getPath());
			$this->log->debug("Seting up $method $callpath");
			$this->map([$method], $callpath, $route->getAction($controller));
		}
	}
	
	public function errorHandler(RequestInterface $request, \Throwable $exception,
			bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails,
			?LoggerInterface $logger = null) {
		$this->log->error("Server error: {$exception->getMessage()}:".PHP_EOL.$exception->getTraceAsString());
		$logger?->error($exception->getMessage());
		return $this->getResponseFactory()->createResponse()->withJson(['error' => $exception->getMessage()], 500, JSON_UNESCAPED_UNICODE);
	}
	
	// copied from (apparently abandoned) https://github.com/crazy-goat/slim-reactor, licensed under MIT
	
	protected function dispatchRouterAndPrepareRoute(ServerRequestInterface $request, RouterInterface $router) {
		$routeInfo = $router->dispatch($request);
		if ($routeInfo[0] === Dispatcher::FOUND) {
			$routeArguments = [];
			foreach ($routeInfo[2] as $k => $v) {
				$routeArguments[$k] = urldecode($v);
			}
			
			$route = $router->lookupRoute($routeInfo[1]);
			// ---------------- hack begin -------------
			// Cleanup between requests
			$route->setArguments([]);
			// ---------------- hack end ---------------
			$route->prepare($request, $routeArguments);
			// add route to the request's attributes in case a middleware or handler needs access to the route
			$request = $request->withAttribute('route', $route);
		}
		
		$routeInfo['request'] = [$request->getMethod(), (string) $request->getUri()];
		return $request->withAttribute('routeInfo', $routeInfo);
	}
}

