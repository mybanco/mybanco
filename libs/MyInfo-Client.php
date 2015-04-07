<?php
/**
  *  MyInfo Client version 0.11 {VERSION}
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
  *
  */

// Check if the cURL extension is loaded.
if (!extension_loaded("curl")) {
	template_Error(999, "Please enable the cURL extension");
}

/**
  * _getURLContents
  * Internal function used by sendRequest for sending a post string to MyInfo
  * and retrieving the data that is sent back to MyInfo
  *
  * @see sendRequest
  * @param string $xmldata data to send to the MyInfo server
  * @param bool $debug if set to true, will print out data to the browser RE: communication
  * @param string $version is the data JSON format (new; version = 2) or
  * the old CARVER INI format (old; version 1). version 1 is now deprecated, and will be removed
  * in a few months.
  *
  * @return array the data recieved from MyInfo
  */
function _getURLContents ($xmldata = "", $debug = false, $version=1) {
	global $SYSTEM, $CONFIG;
	
	$mtime = microtime();
	$mtime = explode(' ',$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
	
	// Find a MyInfo server to use :)
	// TODO: choose a readonly server when no do or add functions are requested
	$x = count($CONFIG['myinfo-servers']);
	$r = rand(0,$x-1);
	
	$curl = curl_init($CONFIG['myinfo-servers'][$r] . '?v='.$version);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, "i=" . urlencode($xmldata));
	curl_setopt($curl, CURLOPT_USERAGENT, 'MyBanco v0.11');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	if ($debug)
		echo "MyInfo took ".$totaltime." seconds to respond";
	
	// TODO: This needs some cleaning :)
	$info = curl_getinfo($curl);
	if (empty($info['http_code'])) {
		template_Error(401);
	} if ($info['http_code'] <> 200) {
		$info['url'] = '{CENSORED}';
		template_Error(402, $info);
	}
	
	if (curl_errno($curl)) {
		print "Error: " . curl_error($ch);
		exit;
	} else {
		// Show me the result
		curl_close($curl);
		
		$OUTPUT = Array(
				'data' => $data,
				'debug' => "<pre>" . htmlspecialchars (print_r($data, true)) . "</pre>"
			);
		if ($debug == true) {
			echo "<!--- $data .......... $xmldata --->\n";
		}
		return $data;
	}
}

/**
  * Create a clean array that can be used for sending data to the MyInfo server
  *
  * @see sendRequest
  * @see addRequest
  *
  */
function newRequest() {
	global $send;
	// This is an "INI Array"
	$send = array();
	$send['CARVER']['packets']=0;
	$send['CARVER']['api']=1;
	$send['CARVER']['appid']=1;
}

/**
  * Add a new command for the MyInfo backend server to do
  *
  * (note: these commands are processed in the order that they are added)
  *
  * @see sendRequest
  * @see newRequest
  *
  * @return string pointer to the data recieved for this command for use with
  * sendRequest
  */
function addRequest($plugin, $action, $data=null) {
	global $send;
	
	$send['CARVER']['packets']++;
	$n = 'packet:' . $send['CARVER']['packets'];
	$send[$n]['plugin'] = $plugin;
	$send[$n]['action'] = $action;
	if (isset($data['error']) AND !isset($data['error-code'])) {
		global $human;
		$data['error-code'] = $data['error'];
		$data['error'] = true;
	}
	if (!isset($data['error-human']) AND isset($data['error-code']) AND isset($human[$data['error-code']])) {
		$data['error-human'] = $human[$data['error-code']];
	}
	
	$send[$n]['data'] = $data;
	return $n;
}

/**
  * Internal function for generating CARVER (version 1 API) responses
  *
  * The carver api is deprecated, this function will be removed in a few months
  *
  * @see sendRequest
  * @deprecated will be removed soon
  * @param $error Define the error value to send
  * @param $andExit exit after creating ini (for server)
  *
  */
function outputINI($error=null, $andExit=true) {
	global $send;
	
	echo ";\n; Generated with MyInfo v0.11\n;\n";
	
	// Was there an error?
	if (is_array($error)) {
		global $human;
		$send['CARVER']['error'] = 1;
		$send['error']['code'] = $error['code'];
		if (!isset($error['human']) AND isset($human[$error['code']]))
			$error['human'] = $human[$error['code']];
		$send['error']['human'] = $error['human'];
	}
	
	foreach ($send as $section => $keys) {
		echo "\n[",$section,"]\n";
		foreach ($keys as $key => $value) {
			echo $key, '=', $value , "\n";
		}
	}
	
	if ($andExit == true)
		exit;
}

/**
  * Send the API queue created by newRequest and addRequest to a random MyBanco server
  * Please note: This has support for version one as well, but this is deprecated!
  *
  * @see newRequest
  * @see addRequest
  *
  * @param bool $debug print out debug messages to the browser
  * @param mixed $error the error that has occured
  * @param int $version version of the MyInfo interface to use
  *
  */
function sendRequest($debug=true, $error=null, $version=2) {
	global $send;
	
	// Was there an error?
	if (is_array($error)) {
		global $human;
		$send['CARVER']['error'] = 1;
		$send['error']['code'] = $error['code'];
		if (!isset($error['human']) AND isset($human[$error['code']]))
			$error['human'] = $human[$error['code']];
		$send['error']['human'] = $error['human'];
	}
	
	// MyInfo Generation (for version 1)
	if ($version == 1) {
		$s = ";\n; Generated with MyInfo v0.05.1\n;\n";
		
		foreach ($send as $section => $keys) {
			$s .= "\n[".$section."]\n";
			foreach ($keys as $key => $value) {
				$s .= $key . '=' . $value . "\n";
			}
		}
		
		// Send the INI to MyInfo
		$ini = _getURLContents($s, false, $version);
		$in = _parse_ini($ini);
	
	// MyInfo Generation (for version 2)
	} else {
		// This is code for the (harder to read) format
		$s = json_encode($send);
		$ini = _getURLContents($s, false, $version);
		$in = json_decode($ini, true);
		
		// This is for a nice debug :P
		if ($debug == true)
			$s = print_r($send, true);
	}
	
	if ($debug == true) {
		echo '<div id="toHide" class="debug"><strong>SENDING:</strong><br /><pre>', $s,
			'</pre><strong>parsed INI</strong><br /><pre>';
		print_r($in);
		echo '</pre></div>';
	}
	
	//
	// Check if there was a MySQL error...
	//
	if (isset($in['error']['code'])) {
		if (isset($in['error']['human']))
			template_Error($in['error']['code'], $in['error']['human']);
		else
			template_Error($in['error']['code']);
	}
	return $in;
}

/**
  * Parse the INI respone that is sent from the MyInfo server
  *
  * @deprecated
  *
  */
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
			$tmp = explode(';;;', $value);
			if (count($tmp) == 2) {
				if ((($value{0} != '"') && ($value{0} != "'")) ||
					preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
					preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value)
				   ){
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
			if ($key == "data") {
				$decode = json_decode($value, true);
				if (is_array($decode))
					$values[$i-1] += $decode;
				else
					$values[$i-1][$key] = $decode;
			} elseif (substr($line, -1, 2) == '[]') {
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
