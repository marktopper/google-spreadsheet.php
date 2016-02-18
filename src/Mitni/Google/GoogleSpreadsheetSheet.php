<?php namespace Mitni\Google;

/**
 * GoogleSpreadsheetSheet
 * ------------------------
 * @class Instance represents Google Spreadsheet's sheet
 */

class GoogleSpreadsheetSheet {

	private $meta = null; // Meta info of the sheet
	private $client = null; // GoogleSpreadsheetClient instance
	private $link = array(); // Collection of links

	public $fields = null; // Fields of table
	public $items = null; // Data of table


	private $num2alpha = [
		1 => 'A' ,
		2 => 'B' ,
		3 => 'C' ,
		4 => 'D' ,
		5 => 'E' ,
		6 => 'F' ,
		7 => 'G' ,
		8 => 'H' ,
		9 => 'I' ,
		10 => 'J' ,
		11 => 'K' ,
		12 => 'L' ,
		13 => 'M' ,
		14 => 'N' ,
		15 => 'O' ,
		16 => 'P' ,
		17 => 'Q' ,
		18 => 'R' ,
		19 => 'S' ,
		20 => 'T' ,
		21 => 'U' ,
		22 => 'V' ,
		23 => 'W' ,
		24 => 'X' ,
		25 => 'Y' ,
		26 => 'Z',
		27 => 'AA' ,
		28 => 'AB' ,
		29 => 'AC' ,
		30 => 'AD' ,
		31 => 'AE' ,
		32 => 'AF' ,
		33 => 'AG' ,
		34 => 'AH' ,
		35 => 'AI' ,
		36 => 'AJ' ,
		37 => 'AK' ,
		38 => 'AL' ,
		39 => 'AM' ,
		40 => 'AN' ,
		41 => 'AO' ,
		42 => 'AP' ,
		43 => 'AQ' ,
		44 => 'AR' ,
		45 => 'AS' ,
		46 => 'AT' ,
		47 => 'AU' ,
		48 => 'AV' ,
		49 => 'AW' ,
		50 => 'AX' ,
		51 => 'AY' ,
		52 => 'AZ',
	];


	/**
	 * Constructor
	 *
	 * @param {Array} $meta
	 * @param {Google_Spreadsheet_Client} $client
	 */
	public function __construct($meta, $client){
		$this->meta = $meta;
		$this->client = $client;

		foreach($this->meta["link"] as $link){
			switch(true){
				case strstr($link["rel"], "#cellsfeed"):
					$this->link["cellsfeed"] = $link["href"] . "?alt=json"; break;
				case strstr($link["rel"], "#listfeed"):
					$this->link["listfeed"] = $link["href"] . "?alt=json"; break;
				default: break;
			}
		}

		$this->fetch();
	}

	/**
	 * Fetch the table data
	 *
	 * @param {Boolean} $force ... Ignore cache data or not
	 * @return {GoogleSpreadsheetSheet} ... This
	 */
	public function fetch($force = false){
		$data = $this->client->request($this->link["cellsfeed"], "GET", array(), null, $force);
		$this->process($data["feed"]["entry"]);
		return $this;
	}

	/**
	 * Select rows by condition
	 *
	 * @param {Closure|Array} $condition
	 * @return {Array}
	 */
	public function select($condition = null){
		if(is_callable($condition)){
			return array_filter($this->items, $condition);
		}
		if(is_array($condition)){
			$result = array();
			foreach($this->items as $row){
				$invalid = false;
				foreach($condition as $key => $value){
					if($row[$key] !== $value){ $invalid = true; }
				}
				if($invalid){ continue; }
				array_push($result, $row);
			}
			return $result;
		}
		return $this->items;
	}

	/**
	 * Update the value of column
	 * @param {Integer} $row
	 * @param {Integer|String} $col ... Column number or field's name
	 * @param {String} $value
	 * @return {Google_Spreadsheet_Sheet} ... This
	 */
	public function update($row, $col, $value){
		$col = is_string($col) ? array_search($col, array_values($this->fields), true) + 1 : $col;
		$body = sprintf(
			'<entry xmlns="http://www.w3.org/2005/Atom" xmlns:gs="http://schemas.google.com/spreadsheets/2006">
            <gs:cell row="%u" col="%u" inputValue="%s"/>
			</entry>',
			$row, $col, htmlspecialchars($value)
		);
		$this->client->request(
			$this->link["cellsfeed"],
			"POST",
			array("Content-Type" => "application/atom+xml"),
			$body
		);
		return $this;
	}

	/**
	 * Insert a row to the table
	 * @param {Array} $vars
	 * @return {Google_Spreadsheet_Sheet} ... This
	 */
	public function insert($vars){
		$body = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:gsx="http://schemas.google.com/spreadsheets/2006/extended">';
		foreach($this->fields as $c => $key){
			if(! array_key_exists($key, $vars)){ continue; }
			$value = htmlspecialchars($vars[$key]);
			$body .= "<gsx:{$key}>{$value}</gsx:{$key}>";
		}
		$body .= "</entry>";
		$this->client->request(
			$this->link["listfeed"],
			"POST",
			array("Content-Type" => "application/atom+xml"),
			$body
		);
		return $this;
	}

	/**
	 * Process the entry data fetched from cellfeed API
	 * Update its `items` property
	 *
	 * @param {Array} $entry
	 */
	private function process($entry){
		$this->fields = array();
		$this->items = array();

		foreach($entry as $col){
			preg_match("/^([A-Z]+)(\d+)$/", $col["title"]["\$t"], $m);
			$content = $col["content"]["\$t"];
			$r = (int) $m[2];
			$c = $m[1];
			if($r === 1){
				$this->fields[$c] = $content;
				continue;
			}
			


			/**
			* Stopped working
			*/
			/*
			if($this->fields[$c]){
				$this->items[$r] = is_array($this->items[$r]) ? $this->items[$r] : array();
				$this->items[$r][$this->fields[$c]] = $content;
			}
			*/


			/**
			*
			* New items array
			*/
			$cell = $col['gs$cell'];
			$row = $cell['row'];
			$colNum 	= $cell['col'];


			// --- Grab the id
			$id = $row - 1; 	// ---> set the row number as a default starting at the second row
			if($row === 1 && $colNum === 1){
				$id = $cell['$t']; 
			}

			// --- If new row, enter the row number
			if($col === 1){
				$this->items[$id]['row'] = $row;	
			}

			// --- Fill out the item row
			if($row !== 1){
				$colLetter 	= $this->num2alpha[$colNum]; 
				$colName 	= $this->fields[$colLetter]; 
				$val = $cell['$t'];
				
				// ---> $this->items[$row][$colName] = $val;	---> instead of row as the key, let's use ID
				$this->items[$id][$colName] = $val;	
			}


		}
	}

}

