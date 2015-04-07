<?php
/* MyBanco Administration Frontend v0.11
 * +------------------------------------+
 * | Copyright 2007 (c) Tim Groeneveld  |
 * +------------------------------------+
 */

header('X-Powered-By: MyBanco Adinistration Frontend v0.11');
define('_START', microtime());
global $userData;

if (file_exists('../config.php'))
	require '../config.php';
else
	die("Missing configuration.");

if (file_exists('../libs/Common.php'))
	require '../libs/Common.php';
else
	die("No common library found.");

if (file_exists('../libs/MyInfo-Client.php'))
	require '../libs/MyInfo-Client.php';
else
	die("No MyInfo client library found.");

if (file_exists('../Skins/'.$CONFIG['skin'].'/'.$CONFIG['skin'].'.php'))
	require '../Skins/'.$CONFIG['skin'].'/'.$CONFIG['skin'].'.php';
else
	die("Invalid skin setting.");

//
// Rightio, lets split the URL, and do the funky shizzle.
//
global $pathInfo, $INI;
$CONFIG['url'] = "/admin";
$path = str_replace($CONFIG['url'] . '/', '/', $_SERVER['REDIRECT_URL']);
$pathInfo = explode('/', substr($path, 1));

newRequest();

$u = checkSession();
if ($u == true) /* we *MAY* be logged in */
	protected_routes();
else
	anyone_routes();

function protected_routes() {
	global $pathInfo;
	$action = $pathInfo[0];
	if ($action == "")			load('Pages/AdminWelcome.php');
	if ($action == "packages")		load('Pages/packagesHome.php');
	elseif ($action == "newUser") 		load('Pages/newUser.php');
	elseif ($action == "users")     {
		if ($pathInfo[1] == "")		load('Pages/AdminUser.php');
		if ($pathInfo[1] == "-info")	load('Pages/userInfo.php');
	}
	elseif ($action == "+user" or $action == " user")
						load('Pages/newUser.php');
	elseif ($action == "-user")		load('Pages/deleteUser.php');
	elseif ($action == "user")		load('Pages/manageUser.php');
	elseif ($action == "system")		load('Pages/System.php');
	
	// And now pages anyone can access
	anyone_routes();
}
function anyone_routes() {
	// These are "anyone" pages
	global $pathInfo, $INI;
	$action = $pathInfo[0];
	if ($action == "")			load('../Pages/Anyone/Welcome.php');
	else					load('../Pages/Anyone/404.php');
	exit;
}



// User login :)
function checkSession() {
	global $userData, $INI;
	$user = strtolower($_COOKIE['user']);
	$id   = strtolower($_COOKIE['id']);
	
	if ($user == "") return false;
	if ($id   == "") return false;
	
	addRequest('sessions', 'getSession', Array('user' => $user, 'session' => $id));
	addRequest('sessions', 'isAdmin', Array());
	
	return true;
}

// Pages should call this function with the sendINI() response :) ... just another check!
function checkSession2($input) {
	// First packet should be the session packet
	if ($input['packet:1']['plugin'] == "sessions" AND $input['packet:1']['action'] == "getSession") {
		if ( !is_array($input['packet:1']['session']) ) {
			anyone_routes();
		}
	}
}

function load($file) {
	if (!file_exists($file))
		die ("File that was to be loaded does not exist.");
	else
		require $file;
	exit;
} 
