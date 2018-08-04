<?php
namespace Isrp\Api;

use Isrp\Service\Controller;
use Isrp\Service\RouteConfiguration;
use Slim\Http\Request;
use Slim\Http\Response;

require_once VENDOR_DIR . '/blockspring/blockspring.php';

class DragonClub extends Controller {
	
	const CACHE_TIME = 900;
	
	private $clubMembers = null;
	private $loadTimestamp = 0;
	private $dragonURL;
	
	public function __construct($app) {
		parent::__construct($app);
		$this->dragonURL = getenv('DRAGON_CLUB_URL');
	}
	
	public function getRoutes() {
		return [
			new RouteConfiguration(static::GET, '/email/{email}', 'getByEmail'),
			new RouteConfiguration(static::GET, '/token/{token}', 'getByToken'),
		];
	}

	public function getByEmail(Request $req, Response $res, array $args) {
		$card = $this->getDragonId($args['email']);
		if ($card === false)
			return $res->withJson([ 'status' => false ], 404);
		
		return $res->withJson([ 'status' => true, 'token' => $card], 200,  JSON_UNESCAPED_UNICODE);
	}
	
	public function getByToken(Request $req, Response $res, array $args) {
		$card = $this->getDragonCard($args['token']);
		if ($card === false)
			return $res->withJson([ 'status' => false ], 404);
		
		return $res->withJson($card, 200,  JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * Retrieve a list of dragon members from the database specified under the dragon-members-url
	 * setting. The resulting array contains a list of records, for each dragon card record. Each
	 * record is an array with the following keys: 'email', 'firstname', 'lastname', 'member_number'
	 * @return array list of dragon members
	 */
	private function dragonMembers() {
		//putenv('BLOCKSPRING_URL=http://sender.blockspring.com');
		if (!is_null($this->clubMembers) and ($this->loadTimestamp + static::CACHE_TIME) > time())
			return $this->clubMembers;
		
		$this->info("Loading dragon memebers from " . $this->dragonURL);
		$resp = \Blockspring::runParsed("query-public-google-spreadsheet", [
			"query" => "SELECT E, C, D, M",
			"url" => $this->dragonURL,
		], [
			"api_key" => "br_91525_bc9d6103a36fc70c5101fddea3d35bc3b23f3242"
		])->params;
		
		$list = $resp['data'];
		$this->info("Loaded " . count($list) . " member records");
		$this->loadTimestamp = time();
		return $this->clubMembers = $list;
	}

	
	/**
	 * Generate a unique ID for each dragon member
	 * @param string $email
	 * @return string|boolean
	 */
	private function getDragonId($email) {
		$email = trim($email);
		foreach ($this->dragonMembers() as $record) {
			$recemail = trim($record['email']);
			if (!empty($recemail) and $email == $recemail) {
				$this->info("Found dragon user $email at ".print_r($record, true));
				$salt = bin2hex(random_bytes(2));
				return $salt . substr(md5($salt . $email.$record['member_number']), 0, 6);
			}
		}
		return false;
	}
	
	/**
	 * Call to verify the unique dragon ID received from a browser
	 * @param string $id
	 * @return boolean
	 */
	private function verifyDragonId($id) {
		$id = trim($id);
		foreach ($this->dragonMembers() as $record) {
			$email = trim($record['email']);
			$salt = substr($id, 0, 4);
			$calcid = $salt . substr($salt . md5($email.$record['member_number']), 0, 6);
			if ($id == $calcid)
				return true;
		}
		return false;
	}
	
	/**
	 * Retrieve the dragon card for an authenticated dragon memeber
	 * @param string $id unique dragon id code
	 * @return array|boolean
	 */
	private function getDragonCard($id) {
		$id = trim($id);
		foreach ($this->dragonMembers() as $record) {
			$email = trim($record['email']);
			$salt = substr($id, 0, 4);
			$calcid = $salt . substr(md5($salt . $email . $record['member_number']), 0, 6);
			if ($id == $calcid)
				return $record;
		}
		return false;
	}
	
}

