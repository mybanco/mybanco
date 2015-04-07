<?php
//--
//-- System administration...
//--

// Ask MyInfo for all the accounts :)
$f = addRequest('admin', 'getFunctionality');

$functionalities = array(
	'core'  => 'MyInfo Core Functionality',
	'register'  => 'MyInfo User self-registration',
	'bank'  => 'MyBanco Account management',
	'admin' => 'MyBanco Distributed Administration Software',
	'transfer' => 'MyBanco Programable Transfer Software',
	'stocks'=> 'MyBanco Stock exchange',
	'sessions' => 'MyBanco Distributed Session Management System',
	'loans' => 'MyBanco Loan Management Software',
	'xbank' => 'MyInfo xbank communication protocol',
	'swift' => '[CONTRIB] SWIFT Messaging',
	'chess' => '[CONTRIB] ASX Electronic Sub-register System',
);

$integrations = array (
	'stocks', 'loans'
);

$out = sendRequest();

// And we now pass the $out we recieve to checkSession2, to make sure the user is really logged in
checkSession2($out);

//
// Now we need to loop through $out[$f]['plugins'] and report those we have and those we do not...
//
$have = $out[$f]['plugins'];
foreach ( $functionalities as $func => $desc) {
	$nothave[] = $func;
}

$missingFunctionality = array_diff( $nothave, $have );

// we are still here, so start displaying data!
template_Header("System Administration");
displayTemplate("Admin/ListFunctionalities",
	array(
		'functionalities' => &$functionalities,
		'have' => &$have,
		'missing' => &$missingFunctionality,
	)
);

template_Footer();
