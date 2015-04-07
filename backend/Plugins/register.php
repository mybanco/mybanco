<?php
//---
//---               [ MyInfo 'register' plugin ]
//---
//---  This is a plugin for the MyInfo server that allows new accounts
//---  to be created without the need for an administrator
//---

global $PLUGIN, $_session, $user;
$PLUGIN['register']['actions'] = Array(
	'canRegister', 'getRegistrationFields', 'getCountries', 'addUserLogin',
	'addUserContactInfo', 'addUser'
);

//
// canRegister: Check if a user can register or not...
//
function __register_canRegister($in) {
	global $PLUGINS;
	
	if ($PLUGINS['register']['canRegister'] == true)
		return 1;
	return 0;
}

global $steps;
$steps = array();
$steps[] = array(
	'sections' => array(
		'1' => array(
			'section' => 'New user data',
			'fields'  => array (
				'title' => array(
					'display' => 'Title',
					'type'    => 'text',
					'length'  => 10
					),
				'fname' => array (
					'display' => 'First Name',
					'type'    => 'text',
					'length'  => 25
					),
				'lname' => array (
					'display' => 'Last Name',
					'type'    => 'text',
					'length'  => 35
					),
			),
		'2' => array(
			'section' => 'Address',
			'fields'  => array (
				'line1' => array(
					'display' => 'Line 1',
					'type'    => 'text',
					'length'  => 40
					),
				'line2' => array (
					'display' => 'Line 2',
					'type'    => 'text',
					'length'  => 40
					),
				'city' => array (
					'display' => 'City',
					'type'    => 'text',
					'length'  => 20
					),
				'state' => array (
					'display' => 'State',
					'type'    => 'text',
					'length'  => 15
					),
				'zip' => array (
					'display' => 'Zip / Post Code',
					'type'    => 'text',
					'length'  => 10
					),
				)
			),
		)
	)
);


function __register_getRegistrationFields() {
	global $steps;
	return $steps;
}

function __register_getCountries ($data) {
	$SQL = 'SELECT `country` as `cid`, `countryName` as `country` FROM `countries` WHERE `used` =1';
	$res = mysql_query($SQL);
	while ( $row = mysql_fetch_assoc($res) ) {
		$country[] = $row;
	}
	
	return Array('countries' => $country);
}

// NOTE: We should really check if registration is enabled or not.
function __register_addUserLogin ($data) {
	if (!is_array($data))
		return array('error'=>981);
	
	// Try and add the new user information
	$u = mysql_real_escape_string($data['username']);
	$p = mysql_real_escape_string($data['password']);
	
	$SQL = 'INSERT INTO `users` (
				`username`, `password`
			) VALUES (
				\'' . $u . '\', \'' . $p . '\'
			);';
	$res = mysql_query($SQL);
	if (mysql_error())
		return array('error' => 'existsalready');
	elseif (mysql_insert_id())
		return array('userid' => mysql_insert_id());
}

function __register_addUserContactInfo ($data) {
	if (!is_array($data))
		return array('error'=>981);
	
	if (!isset($data['userid']))
		return array('error'=>351);
	
	// Try and add the new user information
	foreach ($data as $name=>&$x) {
		$x = mysql_real_escape_string($x);
	}
	
	$SQL = 'INSERT INTO `user_info` (
			`uid`, `isPrimrary`, `title`, `firstName`,
			`lastName`, `addressLine1`, `addressLine2`,
			`city`, `state`, `zip`, `country`
		) VALUES (
			' . $data['userid'] . ', 1, \'' . $data['title'] . '\', \'' . $data['firstName'] . '\',
			\'' . $data['lastName'] . '\', \'' . $data['addressLine1'] . '\', \'' .$data['addressLine2'] . '\',
			\'' . $data['city'] . '\', \'' . $data['state'] . '\', \'' . $data['zip'] . '\', 1, 
		);';
	$res = mysql_query($SQL);
	if (mysql_error())
		return array('error' => 'existsalready');
	elseif (mysql_insert_id())
		return array('userid' => mysql_insert_id());
}

function __register_addNewAccount ($data) {
	if (!is_array($data))
		return array('error'=>981);
	if (!isset($data['userid']))
		return array('error'=>351);
	
	$SQL = 'INSERT INTO `accounts` (`description`, `currency`) VALUES (\'Initial account\', 1)';
	$res = mysql_query($SQL);
	if (mysql_error())
		return array('error' => 'existsalready');
	elseif (mysql_insert_id()) {
		$aid = mysql_insert_id();
		
		$SQL = 'INSERT INTO `account_links` (
				`uid`, `aid`, `canView`, `canEdit`
			) VALUES (
				' . $data['userid'] . ', ' . $aid . ', 1, 1
			);';
		$res = mysql_query($SQL);
		if (mysql_error())
			return array('error' => 'existsalready');
	} else return array('error' => 981);
}

function __register_addUser ($data) {
	if (!is_array($data))
		return array('error'=>981);
	
	// First, run __register_addUserLogin ... if error, don't continue
	$return = __register_addUserLogin($data);
	if (isset($return['error'])) return $return;
	
	// Next, add the contact info
	$return2= __register_addUserContactInfo( $data['contact']+array('userid' => $return['userid']) );
	if (isset($return['error'])) return $return2;
	
	// Next, add the account and account link
	$return2= __register_addNewAccount( array('userid' => $return['userid']) );
	if (isset($return['error'])) return $return2;
	
	// Mmmkay, it worked \o/
	return array('userid' => $return['userid']);
}
