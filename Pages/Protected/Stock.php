<?php
//--
//-- User stock frontend
//--

global $CONFIG, $pathInfo;

if (count($pathInfo) == 1)
	redirectTo('/stocks/');

if (count($pathInfo) == 2) {
	//
	// Send the q to MyInfo for info about the stock ...
	//
	$ticker = strtoupper($pathInfo[1]);
	$stockOptions = array(
		'ticker' => $ticker
	);
	$i   = addRequest('stocks', 'getStockInfo', $stockOptions);
	$in  = sendRequest();
	
	if ($in[$i]['found']) {
		template_Header($CONFIG['mystocko-name'] . " &raquo; Viewing " . $ticker);
		displayTemplate('Stocks/Stylesheet');
		displayTemplate('Stocks/ShowStockInfo', $in[$i]['info']);
		template_Footer();
	}
}
