<?php
//---
//---               [ MyInfo 'bank' plugin ]
//---
//---  Contains core functions for doing banking.
//---

global $PLUGIN, $_session, $user, $CONFIG;
$PLUGIN['bank']['actions'] = Array(
	'listAccounts', 'listAccountTransactions', 'listValidTransferMethods',
	'isValidTransferMethod'
);

function __bank_listAccounts($data) {
	global $sid, $_session, $user, $CONFIG;
	if (!isset($sid)) return array('error' => 350);
	
	// Find what accounts this user is linked to :)
	$SQL = "SELECT `accounts`.*, `currencies`.*, `account_links`.`canEdit`
		FROM `accounts`, `currencies`, `account_links`
		WHERE `accounts`.`aid` IN (
				SELECT `aid` FROM `account_links`
				WHERE `uid`='{$user['uid']}' AND `canView`=1
			)
		AND `currencies`.`cid`=`accounts`.`currency`
			AND `account_links`.`aid`=`accounts`.`aid`
			AND `account_links`.`uid`='{$user['uid']}'";
	$res = @mysql_query($SQL);
	if (!isset($sid)) return array('error' => 980);
	$r['accounts'] = Array();
	while ($row = @mysql_fetch_assoc($res)) {
		$row['balance'] = __bank_amountForAccount($row['aid']);
		$r['accounts'][] = $row;
	}
	
	if (!isset($CONFIG['self-creation']))
		$r['self-creation'] = false;
	else
		$r['self-creation'] = $CONFIG['self-creation'];
	
	return $r;
}

function __bank_listAccount($data) {
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	if (!is_array($data)) {
		if ($data == null) {
			// NOTE: We really should not do this
			return __bank_listAccounts();
		} else {
			$data = Array($data);
		}
	}
	
	foreach ($data as $aid) {
		// Find what accounts this user is linked to :)
		$SQL = "SELECT `accounts`.*, `currencies`.*
			FROM `accounts`, `currencies` WHERE `accounts`.`aid` IN (
					SELECT `aid` FROM `account_links`
					WHERE `uid`='{$user['uid']}' AND `canView`=1
				)
			AND `currencies`.`cid`=`accounts`.`currency`";
		$res = @mysql_query($SQL);
		if (!isset($sid)) return array('error' => 980);
		$r['accounts'] = Array();
		while ($row = @mysql_fetch_assoc($res)) {
			$row['balance'] = __bank_amountForAccount($row['aid']);
			$r['accounts'][] = $row;
		}
	}
	
	return $r;
}

//
// list the transactions done on a bank account within a
// specified period of time.
//
function __bank_listAccountTransactions ($data) {
	global $sid, $_session, $user;
	if (!isset($sid)) return array('error' => 350);
	
	// we need to check if all the correct values are sent
	// to us by the MyInfo client. This basically means
	// sanitising all our values.
	
	if (!is_array($data))
		return array('error' => 981);
	
	if (!is_numeric($data['account']))
		return array('error' => 981);
	
	if (isset($data['limit']) AND !is_numeric($data['limit'])) {
		$data['limit'] = 100;
	}
	
	if (!isset($data['limit'])) $data['limit'] = 500;
	
	// Make sure we have ... "decent" values for the end and start
	// arguments passed to us
	if (isset($data['endTime']) AND !is_numeric($data['endTime'])) {
		// NOTE: We really should not do this
		$data['endTime'] = strtotime($data['endTime']);
	}
	
	if (isset($data['startTime']) AND !is_numeric($data['startTime'])) {
		// NOTE: We really should not do this
		$data['startTime'] = strtotime($data['startTime']);
	}
	
	// If we don't have a start time (ie, our FIRST transaction
	// that we are to list) set it to 2 weeks ago (-2 weeks)
	if (!isset($data['startTime'])) $data['startTime'] = time()-1209600;
	if (!isset($data['endTime']))   $data['endTime']   = time();
	
	// OK, we can begin! First thing we must do is find if our user
	// actually owns this account or not.
	$SQL = "SELECT `accounts`.*, `currencies`.*
		FROM `accounts`, `currencies` WHERE `accounts`.`aid` IN (
				SELECT `aid` FROM `account_links`
				WHERE `uid`='{$user['uid']}'
					AND `canView`=1
					AND `aid`='{$data['account']}'
			)
		AND `currencies`.`cid`=`accounts`.`currency`";
	$res = @mysql_query($SQL);
	if (!$res) return Array('error' => 980); // MySQL Error
	
	// You need to have a row for you to be able to view this account.
	if (mysql_num_rows($res) < 1) return Array('error' => 360);
	
	// Right, we are still here, so lets find the starting balance from startTime
	$r['accountInfo'] = mysql_fetch_assoc($res);
	$r['accountInfo']['chr'] = ord($r['accountInfo']['sign']); //hack much :D
	$r['open'] = __bank_amountForAccount2($data['account'], $data['startTime'], false);
	$r['openingBalance'] = __bank_amountForAccount2($data['account'], $data['startTime']);
	$r['closingBalance'] = __bank_amountForAccount2($data['account'], $data['endTime']);
	
	// And last but not least, lets fetch $limit amount of rows
	// from the transactions table :)
	$SQL = 'SELECT *
		FROM `transactions`
		WHERE `transactionTime` >' . $data['startTime'] . '
			AND `transactionTime` <' . $data['endTime'] . '
			AND (
				`to_aid`="' . $data['account'] . '"
				OR `from_aid`="' . $data['account'] . '")
			ORDER BY `transactionTime` ASC;';

	$res = @mysql_query($SQL);
	if (!$res) return Array('error' => 980); // MySQL Error
	
	while ($row = @mysql_fetch_assoc($res)) {
		if ($row['to_aid'] == $r['accountInfo']['aid'])
			$row['credit'] = 1;
		else    $row['credit'] = -1;
		$r['transactions'][] = $row;
	}
	
	return $data+$r;
}












//
// Get the amount for an account :)
//
function __bank_amountForAccount ($aid) {
	if (!$aid) return array('error' => 982);
	
	$SQL = 'SELECT SUM(`amount`) as `positive`
		FROM `transactions`
		WHERE (`to_aid`="' . $aid . '")';
	
	$mr1 = mysql_fetch_array(mysql_query($SQL)); // The first (positive) money row :)
	$money_pos = 0;
	if($mr1) {
		$money_pos = (is_numeric($mr1["positive"])) ? $mr1["positive"]:0;
	}
	
	$SQL = 'SELECT SUM(`amount`) as `negative`
		FROM `transactions`
		WHERE `from_aid`="' . $aid . '"
			AND `from_aid`<>`to_aid`';
	
	$mr2 = mysql_fetch_array(mysql_query($SQL)); // The second (negative) money row :)
	$money_neg=0;
	if($mr2) {
		$money_neg=(is_numeric($mr2["negative"])) ? $mr2["negative"]:0;
	}
	
	return number_format($money_pos - $money_neg, 2);
}

//
// Get the amount for an account :)
//    this one actually does the ammount till a particular date \o/
//
function __bank_amountForAccount2 ($aid, $startTime = 1200000000, $doNumberFormat=true) {
	if (!$aid) return array('error' => 982);
	
	$SQL = 'SELECT SUM(`amount`) as `positive`
		FROM `transactions`
		WHERE `to_aid`="' . $aid . '"
			AND `transactionTime` < ' . $startTime . ';';
	
	$mr1 = mysql_fetch_array(mysql_query($SQL)); // The first (positive) money row :)
	$money_pos = 0;
	if($mr1) {
		$money_pos = (is_numeric($mr1["positive"])) ? $mr1["positive"]:0;
	}
	
	$SQL = 'SELECT SUM(`amount`) as `negative`
		FROM `transactions`
		WHERE `from_aid`="' . $aid . '"
			AND `transactionTime` < ' . $startTime . '
			AND `from_aid`<>`to_aid`';
	
	$mr2 = mysql_fetch_array(mysql_query($SQL)); // The second (negative) money row :)
	$money_neg=0;
	if($mr2) {
		$money_neg=(is_numeric($mr2["negative"])) ? $mr2["negative"]:0;
	}
	
	if ($doNumberFormat == true)
		return number_format($money_pos - $money_neg, 2);
	else
		return $money_pos - $money_neg;
}
