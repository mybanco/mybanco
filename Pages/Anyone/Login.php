<?php
// Always appear to not be logged in if at this page
global $loggedIn;
$loggedIn = false;

// Here, we need to check if it's post ...
// if it is, send the data via MyInfo to log the user in!
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	newRequest();
	$l = addRequest('sessions', 'login', Array('user' => $_POST['user'], 'pass' => $_POST['pass']));
	$in = sendRequest(false);
	
	// First, could we contact the MyInfo server (ie, is there a response?)
	if (!is_array($in))
		renderLoginError('no_response');
	
	// Second, was there a general MyInfo error?
	if (isset($in['CARVER']['error']) and $in['CARVER']['error'] == 1)
		renderLoginError('bad_response');
	
	// Thridly, did the login work?
	if (isset($in[$l]['error']) AND $in[$l]['error'] == true AND $in[$l]['error-code'] == 425)
		renderLoginError('check_login');
	
	// Login worked! --> Set the cookie \o/
	if (!headers_sent()) {
		setcookie("user", $_POST['user'],	time()+1036800, '/');
		setcookie("id",   $in[$l]['data'],	time()+1036800, '/');
		redirectTo('/?login=true');
	} else {
		renderLoginError("Cannot log you in, headers have already been sent");
	}
	exit;
	
}

template_Header('Login');
displayTemplate('Login');
template_Footer();

function renderLoginError($errorType) {
	template_Header('Login');
	displayTemplate('Login', $errorType);
	template_Footer();
	exit;
}
