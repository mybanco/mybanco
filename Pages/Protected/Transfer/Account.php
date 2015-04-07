<?php
//--
//-- Transfer money!
//--

// Have we selected a method by which we will transfer our money?
global $pathInfo;
if ($_SERVER['REQUEST_METHOD']=="POST" AND is_numeric($pathInfo[2]) AND isset($pathInfo[4])) {
	// RIGHT! Now we want to complete the transaction!
	if ($_POST['code'] <> substr( md5($_POST['transactionID'] . "secret") , -5 ) )
		$codeError = true;
	else {
		$codeError = false;
		$data = Array(
			'secret' => $_POST['transactionID'],
			'code'   => $_POST['code']
		);
		$transfer = addRequest('transfer', 'doTransfer', $data);
	}
	
	$stage = 4;
} elseif ($_SERVER['REQUEST_METHOD']=="POST" AND is_numeric($pathInfo[2]) AND isset($pathInfo[3])) {
	// This is to *PREVIEW* the transaction
	// We need to send the fields in $_POST['fields']
	//    ... plus $amount and $account
	$data = Array(
		'account' => $pathInfo[2],
		'method'  => $pathInfo[3],
		'amount' => $_POST['amount']
	);
	
	// find the extra fields we need to send :D
	$fields = explode(";", $_POST['fields']);
	
	
	foreach ($fields as $field) {
		if ($field == "") continue;
		if (!preg_match("/^[a-z0-9]*$/", $field))
			continue;
		
		$data['data'][$field] = $_POST[$field];
	}
	
	addRequest('transfer', 'doPreviewTransfer', $data);
	$stage = 3;
} elseif (isset($pathInfo[3])) {
	$data = Array(
		'account' => $pathInfo[2],
		'method'  => $pathInfo[3],
	);
	addRequest('transfer', 'isValidTransferMethod', $data);
	$stage = 2;
} else {
	addRequest('transfer', 'listValidTransferMethods',
			array('account' => $pathInfo[2])
		);
	$stage = 1;
}
$out = sendRequest();
$last = $out['packet:'.($out['CARVER']['packets'])];

// And we now pass the $out we recieve to checkSession2, to make sure the user is really logged in
checkSession2($out);

// CHECK what stage of the process we are in
if ($stage == 1) {
	// Stage 1 (Selecting the transfer method)
	//
	if (!isset($last['error']) or $last['error'] == 0) {
		template_Header("Select the transfer method!");
		displayTemplate("Accounts/TransferMoneyMethod", Array(
					'methods' => $last,
					'account' => $pathInfo[2]));
		template_Footer();
	} else {
		template_Header("Account not found!");
		displayTemplate("Accounts/AccountNotFound");
		template_Footer();
	}
} elseif ($stage == 2) {
	if (!isset($last['error']) or $last['error'] == 1) {
		template_Header("Transfer details ...");
		displayTemplate("Accounts/TransferMoneyDetails", Array(
					'methods' => $last,
					'account' => $pathInfo[2]));
		template_Footer();
	} else {
		template_Header("Account not found!");
		displayTemplate("Accounts/AccountNotFound");
		template_Footer();
	}
} elseif ($stage == 3) {
	if (isset($last['error'])) {
		template_Header("Account not found!");
		echo "Trying to withdraw more money then what is in the account. BAD";
		template_Footer();
	} else {
		template_Header("Transfer preview");
		displayTemplate("Accounts/TransferMoneyPreview", Array(
					'methods' => $last,
					  'data'  => $out['packet:2'],
					'account' => $pathInfo[2]));
		template_Footer();
	}
} elseif ($stage == 4) {
	if (isset($last['error'])) {
		$msg = "Incorrect validation code!";
		$good = true;
		require 'Pages/Protected/Accounts.php';
	} else {
		// Rightio. Now, just so our money does not get transfered
		// twice, lets clear the MyInfo buffer!
		newRequest();
		checkSession();
		
		if ($codeError == false) {
			if ($out[$transfer]['ok'] == 1) {
				$msg = "Money was transfered!";
				$good = true;
			} else {
				$msg = "The money could not be transfered!";
				$good = false;
			}
		} else {
			$msg = "Incorrect validation code!";
			$good = false;
		}
		require 'Pages/Protected/Accounts.php';
	}
}

exit;

