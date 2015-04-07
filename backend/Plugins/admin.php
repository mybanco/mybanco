<?php
//---
//---               [ MyInfo 'admin' plugin ]
//---
//---  We can add users, remove users, add accounts etc \o/
//---

global $PLUGIN, $_session, $user;
$PLUGIN['admin']['actions'] = Array(
	'getCountries', 'addUserLogin', 'addUser', 'statistics', 'userList', 'userInfo',
	'getFunctionality'
);


function __admin_getCountries ($data) {
	$SQL = 'SELECT `country` as `cid`, `countryName` as `country` FROM `countries` WHERE `used` =1';
	$res = mysql_query($SQL);
	while ( $row = mysql_fetch_assoc($res) ) {
		$country[] = $row;
	}
	
	return Array('countries' => $country);
}

function __admin_addUserLogin ($data) {
	if (!is_array($data))
		return array('error'=>981);
	
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
		return array('error'=>351);
	
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

function __admin_addUserContactInfo ($data) {
	if (!is_array($data))
		return array('error'=>981);
	
	if (!isset($data['userid']))
		return array('error'=>351);
	
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
		return array('error'=>351);
	
	// Try and add the new user information
	foreach ($data as $name=>&$x) {
		$x = mysql_real_escape_string($x);
	}
	
	$SQL = 'INSERT INTO `user_info` (
			`uid`, `isPrimrary`, `title`, `firstName`,
			`lastName`, `addressLine1`, `addressLine2`,
			`city`, `state`, `zip`, `country`, `homePhone`,
			`mobilePhone`
		) VALUES (
			' . $data['userid'] . ', 1, \'' . $data['title'] . '\', \'' . $data['firstName'] . '\',
			\'' . $data['lastName'] . '\', \'' . $data['addressLine1'] . '\', \'' .$data['addressLine2'] . '\',
			\'' . $data['city'] . '\', \'' . $data['state'] . '\', \'' . $data['zip'] . '\', 1, \'' . $data['homePhone'] . '\',
			\'' . $data['mobilePhone'] .'\');';
	$res = mysql_query($SQL);
	if (mysql_error())
		return array('error' => 'existsalready');
	elseif (mysql_insert_id())
		return array('userid' => mysql_insert_id());
}

function __admin_addNewAccount ($data) {
	if (!is_array($data))
		return array('error'=>981);
	if (!isset($data['userid']))
		return array('error'=>351);
	
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
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

function __admin_addUser ($data) {
	if (!is_array($data))
		return array('error'=>981);
	
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
		return array('error'=>351);	
	
	// First, run __admin_addUserLogin ... if error, don't continue
	$return = __admin_addUserLogin($data);
	if (isset($return['error'])) return $return;
	
	// Next, add the contact info
	$return2= __admin_addUserContactInfo( $data['contact']+array('userid' => $return['userid']) );
	if (isset($return['error'])) return $return2;
	
	// Next, add the account and account link
	$return2= __admin_addNewAccount( array('userid' => $return['userid']) );
	if (isset($return['error'])) return $return2;
	
	// Mmmkay, it worked \o/
	return array('userid' => $return['userid']);
}

function __admin_statistics ($data) {
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
		return array('error'=>351);
	
	global $CONFIG;
	$SQL = "
	SELECT table_schema 'table',
		sum( data_length + index_length )/1024 'size',
		sum( data_free )/1024 'free'
	FROM information_schema.TABLES
	WHERE `table_schema` = '{$CONFIG['mysql-data']}'
	GROUP BY table_schema";
	
	$res = mysql_query($SQL);
	if (!$res)
		return array();
	$row = mysql_fetch_assoc($res);
	return $row;
}

function __admin_userList($data) {
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
		return array('error'=>351);
	
	$SQL = "
	SELECT `users`.`username`, COUNT(`account_links`.`aid`) as `accounts`
	FROM `users`,`account_links`
	WHERE `account_links`.`uid`=`users`.`uid`
	GROUP BY `account_links`.`uid`
	";
	
	$res = mysql_query($SQL);
	while ($row = mysql_fetch_assoc($res))
		$return[] = $row;
	
	return array('users' => $return);
}

function __admin_userInfo($data) {
	// make sure users who are not admins can't add contact info
	if (!isAdmin())
		return array('error'=>351);
	
	$SQL = "
	SELECT `accounts`. * , `currencies`. * , `account_links`.`canEdit`, (
		SELECT SUM(`amount`)
		FROM `transactions`
		WHERE `to_aid`=`accounts`.`aid`
	) as `positive`, (
		SELECT SUM(`amount`)
		FROM `transactions`
		WHERE `from_aid`=`accounts`.`aid`
			AND `from_aid`<>`to_aid`
	) as `negative`
	
	FROM `accounts` , `currencies` , `account_links`
	WHERE `currencies`.`cid` = `accounts`.`currency`
	AND `account_links`.`aid` = `accounts`.`aid`
	AND `account_links`.`uid` = (
		SELECT `uid` FROM `users` WHERE `username`='{$data['user']}'
	)
	LIMIT 0 , 30 
	";
	
	$res = mysql_query($SQL);
	while ($row = mysql_fetch_assoc($res)) {
		if (!is_numeric($row['positive']))
			$row['positive'] = 0;
		if (!is_numeric($row['negative']))
			$row['negative'] = 0;
		
		$row['amount'] = $row['positive'] - $row['negative'];
		$return[] = $row;
	}
	
	return array('accounts' => $return);
}

function __admin_getFunctionality($in) {
	global $CONFIG, $SYSTEM;
	
	// Admin's only...
	if (!isAdmin()) return array('error'=>351);
	
	$return = array (
		'plugins' => $CONFIG['plugins'],
		'integrations' => $CONFIG['integrations']
	);
	return $return;
}

