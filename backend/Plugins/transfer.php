<?php
//---==
//---== Transfers!!!
//---==
//
// This is part of the bank plugin!
//

// Hack in bank support!
require_once './Plugins/bank.php';

global $PLUGIN, $_session, $user;
$PLUGIN['transfer']['actions'] = Array(
	'listValidTransferMethods', 'isValidTransferMethod', 'doPreviewTransfer',
	'doTransfer'
);

global $methods;
$methods = Array ( 
		'between' => Array(
			'title' => 'Inter-account Transfer',
			'description' => 'Transfer between my accounts',
			'icon' => 'between'
		),
		'another' => Array(
			'title' => 'Another bank account',
			'description' => 'Transfer to another {bank} account',
			'icon' => 'trans'
		)
	);

//
// listValidTransferMethods is for external AND internal use.
// Maybe make it use plugins one day?
function __transfer_listValidTransferMethods ($aid) {
	global $methods, $sid, $_session, $user;
	// check if we have a session
	if (!isset($sid)) return array('error' => 350);
	// Check if we have data sent to us
	if (!is_numeric($aid['account'])) return array('error' => 982);
	
	// check if this account belongs to this user!
	$SQL = "SELECT * FROM `account_links`
		WHERE `uid`={$user['uid']}
			AND `aid`={$aid['account']}
			AND `canView`=1
			AND `canEdit`=1";
	$res = @mysql_query($SQL);
	if (!isset($res)) return array('error' => 980);
	if (mysql_num_rows($res) < 1) return array('error' => 981);
	
	// TODO: Different methods for different account types?
	return Array('methods' => $methods);
	// the 'default' type is for between bank accounts
}

// isValidTransferMethod checks if the method we want to use to 
// transfer money with is valid, and if it is, it will return an array
// with the information that we need to know from the user \o/
function __transfer_isValidTransferMethod ($data) {
	global $methods, $sid, $_session, $user;
	// check if we have a session
	if (!isset($sid)) return array('error' => 350);
	// check if the data value we were sent is correct or not
	if (!is_numeric($data['account'])) return array('error' => 982);
	
	// check if this account belongs to this user!
	$SQL = "SELECT * FROM `account_links`
		WHERE `uid`={$user['uid']}
			AND `aid`={$data['account']}
			AND `canView`=1
			AND `canEdit`=1";
	$res = @mysql_query($SQL);
	if (!isset($sid)) return array('error' => 980);
	if (mysql_num_rows($res) < 1) return array('error' => 981);
	
	if (is_array($methods[$data['method']])) {
		if (function_exists("_t_" . $data['method'] . "_required")) {
			$return = Array('method' => $methods[$data['method']]);
			return $return + Array(
					'fields' => call_user_func("_t_" . $data['method'] . "_required", $data['account'])
				);
		}
		else return array('error' => 982);
	}
	
	else return array('error' => 982);
}

// previewTransaction
//    preview a transaction
function __transfer_doPreviewTransfer($data) {
	// Step 1: grab a list of all this users accounts
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	// 1: Make sure the account to is a decent value
	if (! is_numeric($data['account']) )
		return array('error' => 982);
	
	if (function_exists("_t_" . $data['method'] . "_check_data")) {
		$return = call_user_func("_t_" . $data['method'] . "_check_data", $data);
		if ($return == false)
			return array('error' => 981);
	} else return array('error' => 982);
	
	// Step one: Save all the data we were sent with JSON into a database :D
	//   -- Make sure there are no old rows in the database, to save
	//   -- the user getting hacked \o/
	$SQL = 'DELETE FROM `temp_data` WHERE `uid`=\'' . $user['uid'] . '\' AND `extraID`=\'' . 97 . '\'';
	mysql_query($SQL);
	
	
	// Try three times to save the data. If it fails, send an error
	$save= json_encode($data);
	$md5 = md5(time()."__B" . microtime() . "ANKO");
	$sec = substr( md5($md5 . "secret") , -5);
	$SQL = 'INSERT INTO `temp_data` (
			`uid`, `extraID`, `tid`,`expire`, `special`, `data`
		) VALUES (
			\'' . $user['uid'] . '\', \'' . 97 . '\',
			\'' . $md5 . '\', \'' .
				strtotime('+5 minutes') . '\', \'' . $sec . '\', \'' .
				mysql_real_escape_string($save) . '\'
		);';
	$res = mysql_query($SQL);
	
	return Array(
			'secret' => $md5,
			'from_account' => $data['account'],
			'amount' => $data['amount']) +
			call_user_func("_t_" . $data['method'] . "_preview", $data);
}

function __transfer_doTransfer($data) {
	// Step 1: grab a list of all this users accounts
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	if (mysql_real_escape_string($data['secret']) <> $data['secret'] OR
	    mysql_real_escape_string($data['code'])   <> $data['code'])
		return array('error' => 982);
	
	$SQL = 'SELECT * FROM `temp_data`
		WHERE `tid` = \''. $data['secret'] . '\'
			AND `special` = \''. $data['code'] . '\'';
	$res = mysql_query($SQL);
	if (!isset($res)) return array('error' => 980);
	$row = mysql_fetch_assoc($res);
	
	$trans_info = json_decode($row['data'], true);
	
	if (function_exists("_t_" . $trans_info['method'] . "_transfer")) {
		$return = call_user_func("_t_" . $trans_info['method'] . "_transfer", $trans_info);
		if ($return == false)
			return array('error' => 981);
	} else return array('error' => 982);
	
	// Rightio, now lets remove the temp ID for the action we just completed.
	$SQL = 'DELETE FROM `temp_data` WHERE `tid` = \''. $data['secret'] . '\';';
	mysql_query($SQL);
	
	return Array('ok' => true);
}



// ___ METHOD PRIVATES ____

//##
//## FOR BETWEEN ACCOUNTS
//##
//#  Transfer money between [user] accounts
function _t_between_required($aid) {
	// Step 1: grab a list of all this users accounts
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	// Find what accounts this user is linked to :)
	$SQL = "SELECT `accounts`.*
		FROM `accounts` WHERE `accounts`.`aid` IN (
				SELECT `aid` FROM `account_links`
				WHERE `uid`='{$user['uid']}' AND `canView`=1
			)
		AND `accounts`.`aid`<>$aid;";
	$res = @mysql_query($SQL);
	if (!isset($sid)) return array('error' => 980);
	$choices = array();
	while ($row = @mysql_fetch_assoc($res)) {
		$choices["{$row['aid']}"] = "{$row['description']} (Account #{$row['aid']})";
	}
	
	return Array (
		'account' => Array (
			'type' => 'select',
			'desc' => 'To account',
			'choices' => $choices
			)
		);
}

function _t_between_check_data($data) {
	// Step 1: grab a list of all this users accounts
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	// Mmkay, we should have an amount value that is numeric!
	if (!is_numeric($data['amount']))
		return false;
	if (!is_numeric($data['account']))
		return false;
	if ($data['account'] < 0.01)
		return false;
		
	// Now check if we actually have write access to the account!
	$SQL = "SELECT * FROM `account_links`
		WHERE `uid`='{$user['uid']}'
			AND `aid`={$data['account']}
			AND `canEdit`=1
		LIMIT 1;";
	$res = mysql_query($SQL);
	if (mysql_num_rows($res) < 1)
		return false;
	
	// TODO: MAKE SURE THE ACCOUNTS ARE IN THE SAME CURRENCY!
	
	// Mmmkay, one last test: does the account have enough money
	// to widthdraw this ammount, or will the account be over it's
	// withdraw limit?
	$money = __bank_amountForAccount2($data['account'], time(), false);
	if (!isset($money))
		return false;
	
	if ( ($money-$data['amount']) < -500 )
		return false;
	else
		return true;
}

function _t_between_preview($data) {
	return Array('amount_after_transfer' =>
			__bank_amountForAccount2($data['account'], time(), false) - $data['amount'],
		     'to_account' => $data['data']['account']
		);
}

function _t_between_transfer($data) {
	$return = _t_between_check_data($data);
	if ($return == false)
		return false; // thats' a mouth full
	
	// do the transaction \o/
	$SQL = 'INSERT INTO `transactions` (
			`transactionTime`, `from_aid`, `to_aid`, `amount`, `description`
		) VALUES (
			\'' . time() . '\', ' . $data['account'] . ',' . $data['data']['account'] . ', ' .
			$data['amount'] . ', \'Transfer between accounts.\');';
	$res = mysql_query($SQL);
	
	if (mysql_affected_rows() > 0) return true;
	else return false;
}

//##
//## FOR BETWEEN ACCOUNTS
//##
//#  Trnasfer money between another account \o/
function _t_another_required($aid) {
	// Step 1: grab a list of all this users accounts
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	// Find what accounts this user is linked to :)
	$SQL = "SELECT `accounts`.*
		FROM `accounts` WHERE `accounts`.`aid` IN (
				SELECT `aid` FROM `account_links`
				WHERE `uid`='{$user['uid']}' AND `canView`=1
			)
		AND `accounts`.`aid`<>$aid;";
	$res = @mysql_query($SQL);
	if (!isset($sid)) return array('error' => 980);
	$choices = array();
	while ($row = @mysql_fetch_assoc($res)) {
		$choices["{$row['aid']}"] = "{$row['description']} (Account #{$row['aid']})";
	}
	
	return Array (
		'account' => Array (
			'type' => 'text',
			'desc' => 'To account'
			)
		);
}

function _t_another_check_data($data) {
	// Step 1: grab a list of all this users accounts
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);

	
	// Mmkay, we should have an amount value that is numeric!
	if (!is_numeric($data['amount']))
		return false;
	if (!is_numeric($data['account']))
		return false;
	
	// Now check if we actually have write access to the account!
	$SQL = "SELECT * FROM `account_links`
		WHERE `uid`='{$user['uid']}'
			AND `aid`={$data['account']}
			AND `canEdit`=1
		LIMIT 1;";
	$res = mysql_query($SQL);
	if (mysql_num_rows($res) < 1)
		return false;
	
	// TODO: MAKE SURE THE ACCOUNTS ARE IN THE SAME CURRENCY!
	
	// Mmmkay, one last test: does the account have enough money
	// to widthdraw this ammount, or will the account be over it's
	// withdraw limit?
	$money = __bank_amountForAccount2($data['account'], time(), false);
	if (!isset($money))
		return false;
	
	if ( ($money-$data['amount']) < -500 )
		return false;
	else
		return true;
}

function _t_another_preview($data) {
	return Array('amount_after_transfer' =>
			__bank_amountForAccount2($data['account'], time(), false) - $data['amount'],
		     'to_account' => $data['data']['account']
		);
}

function _t_another_transfer($data) {
	$return = _t_another_check_data($data);
	if ($return == false)
		return false; // thats' a mouth full
	
	// do the transaction \o/
	$SQL = 'INSERT INTO `transactions` (
			`transactionTime`, `from_aid`, `to_aid`, `amount`, `description`
		) VALUES (
			\'' . time() . '\', ' . $data['account'] . ',' . $data['data']['account'] . ', ' .
			$data['amount'] . ', \'Transfer between accounts.\');';
	$res = mysql_query($SQL);
	
	if (mysql_affected_rows() > 0) return true;
	else return false;
}
