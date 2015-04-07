<?php
/**
  *  MyBanco version 0.11 {VERSION}
  *
  *  Copyright (C) 2008/2009, Tim Groeneveld
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU Affero General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU Affero General Public License for more details.
  *
  * @author Tim Groeneveld <tim.nospam@inzource.com>
  * @package MyBanco_WWW
  * @version 0.11
  */

// Send out the bank version {VERSION}
header('X-Powered-By: MyBanco Customer Frontend v0.11');

/**
  * constant which defines the time that the page generation started, so
  * that total time can later be calculated.
  */
define('_START', microtime());

/**
  * $userData is used with the function FetchLogin()
  * @see checkSession
  * @see checkSession2
  * @global mixed $userData
  */
global $userData;

if (file_exists('./config.php'))
	require './config.php';
else
	die("Missing configuration.");

if (file_exists('./libs/Common.php'))
	require './libs/Common.php';
else
	die("No common library found.");

if (!isset($CONFIG) or !is_array($CONFIG)) {
	echo 'Please install MyBanco first before running it!';
	exit;
}

if (file_exists('./Skins/'.$CONFIG['skin'].'/'.$CONFIG['skin'].'.php'))
	require './Skins/'.$CONFIG['skin'].'/'.$CONFIG['skin'].'.php';
else
	die("Invalid skin setting.");

if (file_exists('./libs/MyInfo-Client.php'))
	require './libs/MyInfo-Client.php';
else
	die("No MyInfo client library found.");

//
// Rightio, lets split the URL, and do the funky shizzle.
//
global $pathInfo, $INI;
$path = str_replace($CONFIG['url'] . '/', '/', $_SERVER['REDIRECT_URL']);
if (substr($path, strlen($path)-1, strlen($path)) == "/")
	$path = substr($path, 0, strlen($path)-1);
$pathInfo = explode('/', substr($path, 1));

$INI = newRequest();

$u = checkSession();
if ($u == true) /* we *MAY* be logged in */
	protected_routes();
else
	anyone_routes();

/**
  * Routes that only logged in users can access
  *
  * NOTE: Users that have the right cookies will still be here, so make sure you run
  * checkSession2 on your return from MyInfo
  *
  * @see checkSession
  * @see checkSession2
  **/
function protected_routes() {
	global $pathInfo, $INI;
	$action = $pathInfo[0];
	if ($action == "")			load('Pages/Protected/Accounts.php');
	elseif ($action == "view") {
		if ($pathInfo[1] == "account")	load('Pages/Protected/View/Account.php');
	}
	elseif ($action == "transfer") {
		if ($pathInfo[1] == "account")	load('Pages/Protected/Transfer/Account.php');
	}
	elseif ($action == "stocks")
						load('Pages/Protected/Stocks.php');
	elseif ($action == "stock")
						load('Pages/Protected/Stock.php');
	// And now pages anyone can access
	anyone_routes();
}

/**
  * Routes that any person can see
  *
  * NOTE: When no default route from protected_routes() can be found, users will get the route
  * that is found here. Users getting pages from here //may not// have any cookies set yet.
  *
  * @see checkSession2
  * @see protected_routes
  **/
function anyone_routes() {
	// These are "anyone" pages
	global $pathInfo, $INI;
	$action = $pathInfo[0];
	if ($action == "")				load('Pages/Anyone/Welcome.php');
	elseif ($action == "whyus")			load('Pages/Anyone/WhyUs.php');
	elseif ($action == "stocks")			load('Pages/Anyone/Stocks.php');
	elseif ($action == "stocks")			load('Pages/Anyone/Stock.php');
	elseif ($action == "about")			load('Pages/Anyone/About.php');
	elseif ($action == "register")			load('Pages/Anyone/Register.php');
	elseif ($action == "logout")			load('Pages/Anyone/Logout.php');
	elseif ($action == "login")			load('Pages/Anyone/Login.php');
	load('Pages/Anyone/404.php');
}


/**
  * Do preliminary tests to make sure that the user has been logged on.
  *
  * NOTE: Just because this function returns true *DOES NOT* mean that the user is actually
  * signed on via MyInfo. Make sure that you call this function *BEOFRE* sending data to MyInfo,
  * and then after the request has been sent to a MyInfo server, you should run checkSession2()
  * on the returned array.
  *
  * @see checkSession2
  * @see protected_routes
  **/
function checkSession() {
	global $userData, $INI, $loggedIn;
	if (isset($_COOKIE['user']) and isset($_COOKIE['id'])) {
		$user = strtolower($_COOKIE['user']);
		$id   = strtolower($_COOKIE['id']);
		
		addRequest('sessions', 'getSession', Array('user' => $user, 'session' => $id));
		$loggedIn = true;
		return true;
	}
	
	$loggedIn = false;
	return false;
}

/**
  * Actually check and see if the current MyInfo server thinks that the user is logged on
  *
  * Unlike checkSession(), this function will actually check and ensure the user is actually logged on.
  * This is because MyBanco does not store any session data (as they should not), because MyBanco/MyInfo
  * is designed in such a way that a user could use ten different servers in a cluster, and it would all
  * be fully transparent to the user.
  *
  * @see checkSession
  * @see __sessions_getSession
  **/
function checkSession2($input) {
	global $loggedIn;
	// First packet should be the session packet
	if ($input['packet:1']['plugin'] == "sessions" AND $input['packet:1']['action'] == "getSession") {
		if ( !isset($input['packet:1']['session']) ) {
			anyone_routes();
			$loggedIn = false;
			return false;
		}
		
		// Client thinks we are logged in. The backend does any further validation from here on in :)
		$loggedIn = true;
		return true;
	}
	
	$loggedIn = false;
	return false;
}

/**
  * Load a file, and die if the file can not be loaded for any reason.
  *
  * This function is usually used in routes to load a "Application"
  *
  */
function load($file) {
	if (!file_exists($file))
		die ("File that was to be loaded does not exist.");
	else
		require $file;
	exit;
}

/**
  * Redirect a user to a new/different location.
  *
  * This function is good to use in login pages, status pages and notification pages
  * where the user needs to be redirected to a new/different page to where the user is
  * at the moment
  *
  */
function redirectTo($location) {
	if (!headers_sent())
		header("Location: $location");
	else {
		//
		// The headers have already been sent, make a template that
		// will nicely redirect the user...
		//
		template_Header("Redirecting ...");
		displayTemplate("RedirectTo", $location);
		template_Footer();
	}
	exit;
}
