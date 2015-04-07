<?php
//--
//-- listAccounts frontend :)
//--

// Ask MyInfo for all the accounts :)
$u = addRequest('sessions', 'userInfo');
$a = addRequest('bank', 'listAccounts');

// Check for MyStocko integration, if its enabled, request a list of total current wealth
global $CONFIG;
if ($CONFIG['mystocko-integration'])
	$s = addRequest('stocks', 'listAccounts');

$out = sendRequest();

// And we now pass the $out we recieve to checkSession2, to make sure the user is really logged in
checkSession2($out);

// we are still here, so start displaying data!
template_Header("Welcome!");
displayTemplate("WelcomeLoggedInBlurb", $out[$u]);

if (isset($msg))
	displayTemplate("Message", Array('message' => $msg, $isOK = $good));

displayTemplate("WelcomeAccountList", $out[$a]);

if ($CONFIG['mystocko-integration'])
	displayTemplate("WelcomeStockAccountList", $out[$u]['stocks']);

displayTemplate("WelcomeLoggedInBlurbBum");
template_Footer();
