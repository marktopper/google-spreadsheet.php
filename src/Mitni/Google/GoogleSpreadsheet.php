<?php namespace Mitni\Google;

/**
 * Google_Spreadsheet
 *
 * @class Process Google Spreadsheet
 */

use Mitni\Google\GoogleSpreadsheetClient;


class GoogleSpreadsheet {

	/**
	 * Get Google_Spreadsheet_Client instance
	 * @param {String|Array} $keys ... Path to json file or array
	 */
	static public function getClient($keys = null){
		return new GoogleSpreadsheetClient($keys);
	}
}