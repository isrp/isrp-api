<?php
namespace Isrp\Service;

use React\EventLoop\LoopInterface;
use React\EventLoop\Factory;

use CrazyGoat\SlimReactor\SlimReactorApp;
use CrazyGoat\SlimReactor\SlimReactor;

use Isrp\Api\DragonClub;
use Isrp\Api\BaseApi;

use \DateInterval;
use \DateTime;
use \DateTimeInterface;

class Server extends SlimReactorApp {
	
	private $dailies = [];
	private $loop = null;
	
	public function start($staticContentPath) {
		$this->logger->info("Starting");
		$this->mount("/", new BaseApi($this));
		$this->mount("/club", $dragonClub = new DragonClub($this));
		
		$host = '[::]';
		$port = getenv("WEB_PORT") ?: 1280;

		$this->logger->info("Starting ISRP API server on $host:$port");

		$this->addDailyTask(function() use($dragonClub) {
			$dragonClub->whatYouWant();
		});
		
		
		$this->loop = Factory::create();
		$this->registerTimers();
		$slimReactor  = new SlimReactor(
			$this,
			[
				'socket' => "$host:$port",
				'staticContentPath' => $staticContentPath,
				'loopInterface' => $this->loop,
			]
			);
		$slimReactor->run();
	}
	
	public function __get($key) {
	    return $this->getContainer()->get($key);
	}
	
	public function mount($path, Controller $controller) {
		foreach ($controller->getRoutes() as $route) {
			$method = $route->getMethod();
			$callpath = str_replace("//", "/", $path . $route->getPath());
			$this->logger->info("Setting up $method $callpath");
			$this->$method($callpath, $route->getAction($controller));
		}
	}
	
	public function addDailyTask(Callable $task) {
		$task = new DailyTask("12:53:00", $task);
		$this->dailies[] = $task;
		if (!is_null($this->loop))
			$task->register($this->loop);
	}
	
	public function registerTimers() {
		$this->logger->info("Setting up daily tasks");
		foreach ($this->dailies as &$daily)
			$daily->register($this->loop);
	}
	
}

class DailyTask {

	/**
	 * @var string $timespec	Time in a day to schedule this task on, as can be parsed by DateTime c'tor
	 */
	private $timespec;
	
	/**
	 * @var Callable $task	Task to execute each day
	 */
	private $task;
	
	/**
	 * @var \React\EventLoop\LoopInterface $loop	The ReactPHP loop interface used to schedule tasks
	 */
	private $loop;
	
	/**
	 * @var \React\EventLoop\TimerInterface $timer	Timer instance for the next invocation of the task
	 */
	private $timer;

	public function __construct($timespec, Callable $task) {
		$this->timespec = $timespec;
		$this->task = $task;
	}
	
	public function register(LoopInterface $loop) {
		$this->loop = $loop;
		$this->schedule();
	}
	
	public function unregister() {
		$this->loop->cancelTimer($this->timer);
	}
	
	private function schedule() {
		$this->timer = $this->loop->addTimer($this->secondsUntilTime($this->nextTime($this->timespec)), function() {
			$this->task();
			$this->schedule();
		});
	}
	
	private function nextTime($timespec) {
		$now = new DateTime;
		$speccedTime = new DateTime($timespec);
		if ($speccedTime < $now)
			$speccedTime->add(new DateInterval("P1D"));
		return $speccedTime;
	}
	
	private function secondsUntilTime(DateTimeInterface $time) {
		return $time->getTimestamp() - time();
	}

}
