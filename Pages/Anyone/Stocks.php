<?php
//--
//-- Guest stocks frontend
//--

global $CONFIG, $pathInfo;

//echo count($pathInfo) . $pathInfo[count($pathInfo)-1];

if (count($pathInfo) == 1) {
	// "The stock homepage"
	//
	// Ask MyInfo for all the stock info
	//
	$stockOptions = array(
		'limit' => 10
	);
	$top = addRequest('stocks', 'getTopTradedStocks', $stockOptions);
	$in  = sendRequest();
	
	template_Header($CONFIG['mystocko-name'] . " &raquo; Home");
	displayTemplate("Stocks/Search");
	template_Footer();
	
} elseif (count($pathInfo) == 2 and $pathInfo[1] == 'search' and $_SERVER["REQUEST_METHOD"] == "GET") {
	//
	// Stock search engine [ GET ]
	//
	template_Header($CONFIG['mystocko-name'] . " &raquo; Search");
	displayTemplate("Stocks/Search");
	template_Footer();
	
} elseif (count($pathInfo) == 2 and $pathInfo[1] == 'search' and $_SERVER["REQUEST_METHOD"] == "POST") {
	//
	// Stock search engine [ POST ]
	//
	
	$options = array(
		'search' => $_POST['ticker'],
		'limit'  => 20
	);
	
	$s   = addRequest('stocks', 'getSearchResults', $options);
	$in  = sendRequest(true);
	
	if (!$in[$s]['valid'] OR $in[$s]['error']) {
		template_Header($CONFIG['mystocko-name'] . " &raquo; Search results [{$_POST['ticker']}]");
		displayTemplate("Stocks/Search",
			array(
				'message' => 'The search query was not valid',
				'search'  => $_POST['ticker']
			)
		);
		template_Footer();
		exit;
	} elseif ($in[$s]['definite-match']) {
		// Redirect the user to the ticker, because this is what the user searched for...
		if (!headers_sent())
			header("Location: /stock/{$_POST['ticker']}/");
		else {
			//
			// The headers have already been sent, make a template that
			// will nicely redirect the user...
			//
			template_Header($CONFIG['mystocko-name'] . " &raquo Redirecting ...");
			displayTemplate("RedirectTo", "/stock/{$_POST['ticker']}/");
			template_Footer();
		}
		exit;
	}
	
	// Show the search results handed down to us...
	if ($in[$s]['results'] == 0) {
		template_Header($CONFIG['mystocko-name'] . " &raquo; Search results [{$_POST['ticker']}]");
		displayTemplate("Stocks/Search",
			array(
				'message' => 'No search results were returned',
				'search'  => $_POST['ticker']
			)
		);
		template_Footer();
		exit;
	} else {
		template_Header($CONFIG['mystocko-name'] . " &raquo; Search results [{$_POST['ticker']}]");
		$in[$s]['search'] = $_POST['ticker'];
		displayTemplate("Stocks/ShowResults", $in[$s]);
		template_Footer();
		exit;
	}
}







