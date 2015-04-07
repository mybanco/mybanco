<?php
/*
    MyInfo version 0.11

    Copyright (C) 2008, Tim Groeneveld

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

*/


date_default_timezone_set('UTC');

/*
include 'Roadsend/xmlrpc.inc';
include 'Roadsend/xmlrpcs.inc';
include 'Roadsend/json_extension_api.inc';
include 'Roadsend/jsonrpc.inc';
*/

// Let's load our own error handler
$old_error_handler = set_error_handler("myErrorHandler");

// We always output TXT
header("Content-type: text/plain");
header("X-Powered-By: MyInfo v0.11");
header("Server: MyInfo v0.11");

global $human, $PLUGIN;
$human[100] = 'No input. Please read the MyInfo development documentation';
$human[101] = 'Cannot connect to Database';
$human[102] = 'Database error';
$human[201] = 'Application access denied [missing appid]';
$human[350] = 'The requested action requires a session - and a getSession packet was not sent.';
$human[355] = 'Session storage names should only contain letters and numbers, and be max 8 chrs long.';
$human[360] = 'Session data passed onto MyInfo is invalid or non existant';
$human[361] = 'The requested action required session data, but it is not set';
$human[403] = 'The requested plugin is not availible on this server';
$human[425] = 'Login failed';
$human[980] = 'MySQL query error';
$human[981] = 'Invalid arguments were sent to this function. Please read the bloody manual! THIS IS BAD!';
$human[982] = 'Invalid data sent to an internal function. This is a fatal error.';
$human['existsalready'] = 'The data to be inserted already exists.';

// Make sure wacko servers don't have slashes in the inputted data
//   (thanks dr-spangle for helping to hunt this one down!)
if (get_magic_quotes_gpc()) {
	$_POST = array_map('stripslashes',$_POST);
	$_GET  = array_map('stripslashes',$_GET);
}


// Load the config
if (!file_exists("./config.php"))
	die("No config file exists in this location!");
require "./config.php";

// Empty ini file :) [for output]
$INI = createINI();

// Read in our inputted INI file :)
if (!isset($_POST['i'])) {
	outputINI($INI, array('code' => 100));
} else {
	if ($_GET['v'] <> '1')
		$i = json_decode($_POST['i'], true);
	else
		$i = _parse_ini($_POST['i']);
	
	if (!isset($i['CARVER']['api']))
		outputINI($INI, array('code' => 100));
	if (!isset($i['CARVER']['appid']))
		outputINI($INI, array('code' => 201));
}

// Die if no MySQL
if (!extension_loaded("mysql")) {
	outputINI($INI, array('code' => 999));
}

//
// OK, lets go connect to MySQL
//
$link = @mysql_connect('127.0.0.1', $CONFIG['mysql-user'], $CONFIG['mysql-pass']);
if (empty($link)) {
	outputINI($INI, array('code' => 101));
	exit;
}
//
// Select the database
//
$sel = @mysql_select_db($CONFIG['mysql-data']) or die('Could not select database');
if (!$sel) {
	outputINI($INI, array('code' => 102));
	exit;
}
//
// Check if the application is allowed to access MyInfo
//
if (isset($_GET['appid']) and mysql_real_escape_string ($_GET['appid']) <> $_GET['appid']) {
	outputINI($INI, array('code' => 201));
	exit;
}

//==
//== THE FUN STUFF!
//==
// Loop through the packets :)
$x = 1;
while ($x <= $i['CARVER']['packets']) {
	$n = 'packet:' . $x;
	if (!is_array($i[$n]))
		outputINI($INI, array('code' => 401, 'human' => "The packet $n was asked to be processed, but the packet contained no data"));
	
	if (in_array($i[$n]['plugin'], $CONFIG['plugins'])) {
		$action = $i[$n]['action'];
		
		if (!file_exists('./Plugins/'.$i[$n]['plugin'].'.php'))
			outputINI($INI, array('code' => 402, 'human' => "The packet $n asked for a plugin not availible on this server"));
		require_once './Plugins/'.$i[$n]['plugin'].'.php';
		
		if (!is_array($PLUGIN[$i[$n]['plugin']]))
			outputINI($INI, array('code' => 403, 'human' => "The action packet $n asked for is not availible on this server"));
		
		if (!in_array($action, $PLUGIN[$i[$n]['plugin']]['actions']))
			outputINI($INI, array('code' => 404, 'human' => "The action packet $n asked for is not availible on this server"));
		
		$function = '__'.$i[$n]['plugin'].'_'.$action;
		if (!function_exists($function))
			outputINI($INI, array('code' => 405, 'human' => "The action packet $n asked for is not availible on this server"));
		
		if ($_GET['v'] == '1')
			$return = call_user_func($function, json_decode($i[$n]['data'], true));
		else
			$return = call_user_func($function, $i[$n]['data']);
		
		$INI = createINI_Packet($INI, $i[$n]['plugin'], $action, $return);
	} else {
		// The plugin does not exist, error please!
		outputINI($INI, array('code' => 402, 'human' => "The packet $n asked for a plugin not availible on this server"));
	}
	
	$x++;
}

// Make an empty INI file :)
outputINI($INI);
exit;


// The error Handler
function myErrorHandler($errno, $errstr, $errfile, $errline) {
	if ($errno < 9) return false;
	global $INI;
	outputINI($INI, Array('code' => 500, 'human' => json_encode(
		    array('errno' => $errno, 'errstr' =>$errstr, 'errfile'=>$errfile, 'errline'=>$errline) ))
	);
	exit;
	return true;
}


// INI Functions
function createINI() {
	// This is an "INI Array"
	$INI['CARVER']['packets']=0;
	return $INI;
}

function createINI_Packet($INI, $plugin, $action, $data) {
	$INI['CARVER']['packets']++;
	$n = 'packet:' . $INI['CARVER']['packets'];
	$INI[$n]['plugin'] = $plugin;
	$INI[$n]['action'] = $action;
	if (isset($data['error']) AND !isset($data['error-code'])) {
		global $human;
		$data['error-code'] = $data['error'];
		$data['error'] = true;
	}
	if (!isset($data['error-human']) AND isset($data['error-code']) AND isset($human[$data['error-code']])) {
		$data['error-human'] = $human[$data['error-code']];
	}
	
	// Do magic encoding?
	if (function_exists("mb_internal_encoding"))
		mb_internal_encoding("UTF-8");
	
	if ($_GET['v'] == '1')
		$INI[$n]['data'] = json_encode($data);
	else
		if (is_array($data))
			$INI[$n] += $data;
		else
			$INI[$n]['data'] = $data;
	
	return $INI;
}

function outputINI($INI, $error=null, $andExit=true) {
	// Was there an error?
	if (is_array($error)) {
		global $human;
		$INI['CARVER']['error'] = 1;
		$INI['error']['code'] = $error['code'];
		if (!isset($error['human']) AND isset($human[$error['code']]))
			$error['human'] = $human[$error['code']];
		$INI['error']['human'] = $error['human'];
	}

	if (isset($_GET['v']) AND $_GET['v'] == '1') {
		echo ";\n; Generated with MyInfo v0.11\n;\n";
		
		foreach ($INI as $section => $keys) {
			echo "\n[",$section,"]\n";
			foreach ($keys as $key => $value) {
				echo $key, '=', $value , "\n";
			}
		}
	} else {
		echo json_encode($INI);
	}
	
	if ($andExit == true)
		exit;
}

//PARSE INI
function _parse_ini($input, $process_sections = false) {
	$process_sections = ($process_sections !== true) ? false : true;
	
	$ini = split("\n", $input);
	
	$process_sections = true;
	
	if (count($ini) == 0) {
		return array();
	}
	
	$sections = array();
	$values = array();
	$result = array();
	$globals = array();
	$i = 0;
	foreach ($ini as $line) {
		$line = trim($line);
		$line = str_replace("\t", " ", $line);
		
		// Comments
		if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}
		
		// Sections
		if ($line{0} == '[') {
			$tmp = explode(']', $line);
			$sections[] = trim(substr($tmp[0], 1));
			$i++;
			continue;
		}
		
		// Key-value pair
		list($key, $value) = explode('=', $line, 2);
		$key = trim($key);
		$value = trim($value);
		if (strstr($value, ";")) {
			$tmp = explode(';', $value);
			if (count($tmp) == 2) {
				if ((($value{0} != '"') && ($value{0} != "'")) ||
				preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
				preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
				$value = $tmp[0];
				}
			} else {
				if ($value{0} == '"') {
				$value = preg_replace('/^"(.*)".*/', '$1', $value);
				} elseif ($value{0} == "'") {
				$value = preg_replace("/^'(.*)'.*/", '$1', $value);
				} else {
				$value = $tmp[0];
				}
			}
		}
		$value = trim($value);
		$value = trim($value, "'\"");
		
		if ($i == 0) {
			if (substr($line, -1, 2) == '[]') {
				$globals[$key][] = $value;
			} else {
				$globals[$key] = $value;
			}
		} else {
			if (substr($line, -1, 2) == '[]') {
				$values[$i-1][$key][] = $value;
			} else {
				$values[$i-1][$key] = $value;
			}
		}
	}
	
	for($j = 0; $j < $i; $j++) {
		if ($process_sections === true) {
			$result[$sections[$j]] = $values[$j];
		} else {
			$result[] = $values[$j];
		}
	}
	
	return $result + $globals;
}
