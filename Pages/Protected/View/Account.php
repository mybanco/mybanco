<?php
//--
//-- The accounts view.
//-- NOTE: This should be improved in some way, eg
//--       show things such as intrest made in a particular period?
//--


global $pathInfo;


//-
//- Ask MyInfo for the info for the requested account :)
//-
$data = Array(
	'account' => $pathInfo[2],
	'startTime' => '-28 days',
);
addRequest('bank', 'listAccountTransactions', $data);
$out = sendRequest();

//-
//- And we now pass the $out we recieve to checkSession2, to make sure the user is really logged in
//-
checkSession2($out);

//-
//- And check to make sure we didn't attempt to view account data
//- that does not belong to us
//-
if (isset($out['packet:'.$out['CARVER']['packets']]['error'])) {
	load("Pages/Anyone/404.php");
}

// we are still here, so start displaying data!
template_Header("Account view");
displayTemplate("Accounts/View", $out['packet:'.$out['CARVER']['packets']]);
template_Footer();
