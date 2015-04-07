<?php
//---
//---           [ MyInfo 'stocks' plugin ]
//---
//---  Contains core functions for doing stock exhange trading.
//---

global $PLUGIN;
$PLUGIN['stocks']['actions'] = Array(
	'listAccounts', 'getTopTradedStocks', 'getSearchResults', 'getStockInfo'
);

function __stocks_listAccounts($in) {
	
}

function __stocks_getTopTradedStocks($in) {
	
}

function __stocks_getSearchResults($in) {
	// Check and make sure there is a search query set
	if (!isset($in['search']))
		return array('error' => 981);
	
	if (!isset($in['limit']) OR !is_numeric($in['limit']) OR $in['limit'] > 50)
		$in['limit'] = 50;
	
	// Make sure the search query does not contain any gay characters
	if ($in['search'] <> mysql_real_escape_string($in['search']))
		return array('error' => 981);
	
	$return = array('valid' => true);
	
	// Do the MySQL search...
	$SQL = "SELECT * 
		FROM `stock_tickers` 
		WHERE `ticker` = '{$in['search']}'
		LIMIT 1;";
	$res = mysql_query($SQL);
	if (mysql_num_rows($res)) {
		$return['definite-match'] = true;
	} else {
		// Do a deeper search on the company name ...
		$SQL = "SELECT * 
			FROM `stock_tickers` 
			WHERE `compayName` LIKE '%{$in['search']}%'
			LIMIT {$in['limit']};";
		$res = mysql_query($SQL);
		$return['results'] = mysql_num_rows($res);
		while ($row = mysql_fetch_assoc($res)) {
			$return['result'][] = $row;
		}
	}
	
	return $return;
}

function __stocks_getStockInfo($in) {
	// Check and make sure the ticker is valid :D
	if (!isset($in['ticker']))
		return array('error' => 981);
	
	$ticker = strtoupper($in['ticker']);
	
	$SQL = "SELECT * 
		FROM `stock_tickers` 
		WHERE `ticker` = '{$ticker}'
		LIMIT 1;";
	$res = mysql_query($SQL);
	if (!mysql_num_rows($res))
		return array('found' => false);
	
	//
	// Get the stock info from the tickers table, and calculate the going price...
	//
	$return['found'] = true;
	$return['info']  = mysql_fetch_assoc($res);
	
	
	
	return $return;
}


















