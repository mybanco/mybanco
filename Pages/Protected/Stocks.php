<?php
//--
//-- User stocks frontend
//--

global $pathInfo;
if (count($pathInfo) == 1) {
	// "The stock homepage"
	load('Pages/Anyone/Stocks.php');
} elseif (count($pathInfo) == 2 and $pathInfo[1] == 'search') {
	// Stock search
	load('Pages/Anyone/Stocks.php');
}
