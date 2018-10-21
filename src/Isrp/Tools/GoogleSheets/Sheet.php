<?php
namespace Isrp\Tools\GoogleSheets;

class Sheet {
	
	/**
	 * spreadsheet ID, example: "12qjFOuUDSjncEBbA5ZAtz1H3igFGDizZokrvTj-ZQgI"
	 * @var string
	 */
	var $id;
	
	/**
	 * Spreadsheet data
	 * @var \Google_Service_Sheets_Spreadsheet
	 */
	var $data;
	/**
	 * Sheets
	 * @var \Google_Service_Sheets_Sheet
	 */
	var $sheets;
	
	public function __construct(\Google_Service_Sheets_Spreadsheet $sheetsResp) {
		error_log("parsing spreadsheet: " . $sheetsResp->getSpreadsheetId());
		$this->id = $sheetsResp->getSpreadsheetId();
		$this->data = $sheetsResp;
		$this->sheets = $sheetsResp->getSheets();
	}
	
	public function getSheet($sheet = 0) {
		if (!is_numeric($sheet))
			$sheet = $this->findSheetByName($sheet);
		$sobj = @$this->sheets[$sheet];
		if (!$sobj)
			throw new \Exception("No sheet with index $sheet");
		$rows = $sobj->getData()[0]->getRowData();
		$headers = $this->parseHeaders(array_shift($rows));
		return array_map(function($row) use($headers) {
			$rowdata = [];
			$vals = $row->getValues();
			for ($i = 0; $i < count($headers); $i++) {
				if (is_null($vals[$i]))
					continue;
				$rowdata[$headers[$i]] = $this->readCell($vals[$i]);
			}
			return $rowdata;
		}, $rows);
	}
	
	private function readCell(\Google_Service_Sheets_CellData $cell) {
		if ($this->isDate($cell))
			return $this->toDate($cell);
		$val = $cell->getEffectiveValue();
		if (is_null($val))
			return null;
		if ($this->isNumeric($cell))
			return $val->getNumberValue();
		return $val->getStringValue();
	}
	
	private function isDate(\Google_Service_Sheets_CellData $cell) {
		if (is_null($cell)) return false;
		$fmt = $cell->getEffectiveFormat();
		if (is_null($fmt)) return false;
		$num = $fmt->getNumberFormat();
		if (is_null($num)) return false;
		return $num->getType() == "DATE";
	}
	
	private function isNumeric(\Google_Service_Sheets_CellData $cell) {
		if (is_null($cell) || is_null($cell->getEffectiveValue())) return false;
		return is_numeric($cell->getEffectiveValue()->getNumberValue());
	}
	
	private function toDate(\Google_Service_Sheets_CellData $cell) {
		$dt = new \DateTime();
		return $dt->setTimestamp(86400 * (floor($cell->getEffectiveValue()->getNumberValue()) - 25569));
	}
	
	private function parseHeaders(\Google_Service_Sheets_RowData $headerRow) : array {
		return array_map(function($value){
			if (is_null($value) or is_null($value->getEffectiveValue()))
				return null;
			return $value->getEffectiveValue()->getStringValue();
		}, $headerRow->getValues());
	}
	
	public function findSheetByName($name) : int {
		foreach ($this->sheets as $sheet) {
			if (strcasecmp($name, $sheet->getProperties()->getTitle()) == 0)
				return $sheet->getProperties()->getIndex();
		}
		throw new \Exception("Failed to find sheet named $name");
	}
}

