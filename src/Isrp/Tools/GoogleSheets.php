<?php
namespace Isrp\Tools;

use Isrp\Tools\GoogleSheets\Sheet;

class GoogleSheets {

	var $client;
	
	public function __construct() {
		$this->client = new \Google_Client();
		$this->client->useApplicationDefaultCredentials();
		$this->client->addScope(\Google_Service_Sheets::SPREADSHEETS_READONLY);
		error_log("Initialized Google API");
	}
	
	public function loadSpreadsheet($sheetid, $rows = 500) : Sheet {
		$service = new \Google_Service_Sheets($this->client);
		error_log("Retrieving spreadsheet $sheetid");
		$sdata = $service->spreadsheets->get($sheetid, [
			'includeGridData' => true,
			'ranges' => ['A1:Z'.$rows,"email!A1:C3"]
		]);
		error_log("Loaded data from: " . $sdata->getSpreadsheetUrl());
		return new Sheet($sdata);
	}
}

