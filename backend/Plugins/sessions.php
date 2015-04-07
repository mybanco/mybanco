<?php
//---
//---               [ MyInfo 'sessions' plugin ]
//---
//---  This is a basic plugin for the MyInfo server to provide a simple
//---  example on how to create MyInfo plugins :)
//---

global $PLUGIN, $_session, $user;
$PLUGIN['sessions']['actions'] = Array(
	'getSession', 'addSessionData', 'rmSessionData', 'logoutSession', 'login',
	'userInfo', 'isAdmin'
);

function __sessions_getSession($data) {
	global $sid, $_session, $user; //s=session
	// Do we have the right data?
	if (!isset($data['user']) OR !isset($data['session']))
		return array('error'=>360); // ERROR!
	
	if (mysql_real_escape_string($data['user']) <> $data['user'])
		return array('error'=>360); // ERROR!
	
	if (mysql_real_escape_string($data['session']) <> $data['session'])
		return array('error'=>360); // ERROR!
	
	if (strlen($data['session']) > 32)
		return array('error'=>360); // ERROR!
	
	// Does the session exist?
	$res = @mysql_query("SELECT `users`.*, `sessions`.`sid`, `sessions`.`uid`
				FROM `sessions`, `users`
				WHERE `session`='{$data['session']}'
					AND `sessions`.`uid`=`users`.`uid`
				AND `timeout` > UNIX_TIMESTAMP()");
	if (!$res)
		return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
	if (mysql_num_rows($res) == 0)
		return false;
	$u = mysql_fetch_assoc($res);
		
	//
	// Grab the session data from MySQL {SLOW!}
	//
	/*
	$res = @mysql_query("SELECT * FROM `session_data` WHERE `sid`={$session['sid']}");
	if (!$res)
		return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
	$sessionData = Array();
	while($r = mysql_fetch_assoc($res)) {
		$sessionData[$r['name']] = $r['value'];
	}*/
	
	// We have session data, now let's fetch user data :)
	/*
	if (isset($session['uid'])) {
		$res = @mysql_query("SELECT * FROM `users` WHERE `uid`={$session['uid']}");
		if (!$res)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		$u = mysql_fetch_assoc($res);
	}
	*/
	
	if ($data['user'] <> $u['username'])
		return false;
	
	$sid = $u['sid'];
	$user = $u;
	return array('session' => Array());
}

// Save temp. data into sessions :)
function __sessions_addSessionData($data) {
	global $sid, $_session, $user;
	
	if (!isset($sid)) return array('error' => 350);
	
	foreach ($data as $name=>$value) {
		if (mysql_real_escape_string($name) <> $name)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		if (strlen($name) > 8)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		if (strlen($name) < 2)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		
		$value = mysql_real_escape_string($value);
		
		$d[] = "DELETE FROM `session_data` WHERE `sid` = {$sid} AND `name` = '$name';";
		$i[] = "INSERT INTO `session_data` (`sid`, `name`, `value`) VALUES ('{$sid}', '$name', '$value');";
	}
	
	// First, delete all the rows :)
	foreach ($d as $SQL) {
		$res = mysql_query($SQL);
		$d_af += mysql_affected_rows();
	}
	foreach ($i as $SQL) {
		$res = mysql_query($SQL);
		$i_af += mysql_affected_rows();
	}
	
	return Array('deleted' => $d_af, 'inserted' => $i_af);
}

function __sessions_rmSessionData($data) {
	global $sid, $_session, $user;
	
	if (!isset($sid)) return array('error' => 350);
	
	foreach ($data as $name) {
		if (mysql_real_escape_string($name) <> $name)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		if (strlen($name) > 8)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		if (strlen($name) < 2)
			return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
		
		$d[] = "DELETE FROM `session_data` WHERE `sid` = {$sid} AND `name` = '$name';";
	}
	
	// First, delete all the rows :)
	foreach ($d as $SQL) {
		$res = mysql_query($SQL);
		$d_af += mysql_affected_rows();
	}
	return Array('deleted' => $d_af, 'inserted' => $i_af);
}

function __sessions_logoutSession($data) {
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	// Delete all temp. session data :)
	@mysql_query("DELETE FROM `session_data` WHERE `sid` = '$sid';");
	@mysql_query("DELETE FROM `sessions` WHERE `sid` = '$sid';");
	
	$sid = null;
	$_session = null;
	$user = null;
	
	return true;
}

function __sessions_login($data) {
	if (mysql_real_escape_string($data['user']) <> $data['user'])
		return array('error'=>360); // ERROR!
	
	$pass = mysql_real_escape_string($data['pass']);
	$SQL = "SELECT * FROM `users` 
		WHERE `username` = '{$data['user']}' AND
			`password` = '{$pass}';";
	$res = @mysql_query($SQL);
	if (!$res) return array('error' => 980);
	if (mysql_num_rows($res) < 1) return array('error' => 425);
	
	$row = mysql_fetch_assoc($res);
	$sid = md5("myInfo" . sha1(time()) . microtime() . $row['uid'] . $row['username']);
	
	// Delete any old sessions that may have existed
	mysql_query("DELETE FROM `session_data` WHERE `sid` IN (
			SELECT `sid` FROM `sessions` WHERE `uid` = '{$row['uid']}');");
	mysql_query("DELETE FROM `sessions` WHERE `uid` = '{$row['uid']}';");
	
	@mysql_query("INSERT INTO `sessions` (
				`session`, `uid`, `timeout`
			) VALUES (
				'$sid', '{$row['uid']}', '" . strtotime('+2 days') . "'
			);");
	$id = mysql_insert_id();
	@mysql_query("UPDATE `users` SET
			`lastLoginTime` = `loginTime`,
			`loginTime` = '" . time() . "' WHERE `uid`={$row['uid']} LIMIT 1;");
	
	if ($id > 0)
		return $sid;
	return array('error' => 425);
	$row['uid'];
	
}

// userInfo get's the user's details. If no data= ... then just grab what the user has marked as his/her default
function __sessions_userInfo($data) {
	global $sid, $_session, $user;
	
	if (!isset($sid)) return array('error' => 350);
	
	$SQL = "SELECT `user_info`.*, `countries`.* FROM `user_info`, `countries`
		WHERE `user_info`.`uid` ={$user['uid']} AND `user_info`.`isPrimrary`=1
		AND `countries`.`country`=`user_info`.`country` ";
	
	$res = @mysql_query($SQL);
	if (!$res)
		return array('error' => true, 'error-code' => 1, 'error-human'=>mysql_error());
	
	$row = mysql_fetch_assoc($res);
	return $row+$user;
}


// Check if said user is an administrator or not!
function __sessions_isAdmin ($data) {
	global $user, $sid;
	if (!is_array($data))
		return array('error'=>981);
	
	if (!isset($sid)) return array('error' => 350);
	return array('admin' => isAdmin());
}

// Cache version of __sessions_isAdmin
function isAdmin($data = array()) {
	global $isAdminCache, $user;
	if (isset($isAdminCache)) return $isAdminCache;
	
	$SQL = "SELECT * FROM `admins`
		WHERE `uid` ={$user['uid']} LIMIT 1;";
	
	$res = mysql_query($SQL);
	if (mysql_num_rows($res) > 0)	$isAdminCache = true;
	else				$isAdminCache = false;
	
	return $isAdminCache;
}
