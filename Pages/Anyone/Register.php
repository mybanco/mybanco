<?php
//
// Make a clean request
//
newRequest();
$loggedIn = checkSession();
$r        = addRequest('register', 'canRegister');
$out      = sendRequest();

// Check if we are logged in
if ($loggedIn == true) {
	if ($input['packet:1']['plugin'] == "sessions" AND $input['packet:1']['action'] == "getSession") {
		if ( !is_array($input['packet:1']['session']) ) {
			$loggedIn == false;
		}
	}
}

// Now check if registration is enabled on the server
if ($out[$r]['data'] == false) {
	template_Header("Registration Disabled");
	displayTemplate("Register/Disabled");
	template_footer();
	exit;
}

// Now we will know for certain or not if we are actually logged in...
if ($loggedIn) {
	template_Header("WHAT!?");
	displayTemplate("Register/YourLoggedIn");
	template_Footer();
	exit;
}

// Now we shall send another request 
newRequest();
$r        = addRequest('register', 'getRegistrationFields');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
	showForm();
} elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
	
	// First, try to add the user/password for internet (main uid)
	$data = array (
		'username' => $_POST['iUser'],
		'password' => $_POST['iPass'], 
		'contact' => array (
			'title' => $_POST['title'],
			'firstName' => $_POST['firstName'],
			'lastName' => $_POST['lastName'],
			'addressLine1' => $_POST['addressLine1'],
			'addressLine2' => $_POST['addressLine2'],
			'city' => $_POST['city'],
			'state' => $_POST['state'],
			'zip' => $_POST['zip'],
			'country' => $_POST['cid']
		)
	);
	$u = addRequest('register', 'addUser', $data);
	
	$out = sendRequest();
	checkSession2($out);
	
	if (isset($out[$u]['userid'])) {
		template_header("Welcome to new user!!");
		echo 'new user account created!';
		template_footer();
	} else {
		template_header("Welcome to new user!!");
		echo 'user already exists';
		template_footer();
	}
}



function showForm() {
	global $INI;
	
	// Get a list of countries
	$c = addRequest('admin', 'getCountries' );
	$out = sendRequest();
	checkSession2($out);

	template_Header('Create new user account');
	echo '
	
	<h2>Please enter user data :)</h2>
	
	<form action="./" method="POST">
	<center>
	<table width="80%" style="width: 80%;">
	
	<tr class="rowH">
		<th colspan="2">
			New user data
		</th>
	</tr>
	
	<tr class="rowB">
		<td width="20%"><strong>Title</strong></th>
		<td><input name="title" style="width: 60px;"></td>
	</tr>
	<tr class="rowA">
		<td><strong>First name</strong></th>
		<td><input name="firstName" style="width: 150px;"></td>
	</tr>
	<tr class="rowB">
		<td><strong>Last name</strong></th>
		<td><input name="lastName" style="width: 200px;"></td>
	</tr>
	
	<tr class="rowH">
		<th colspan="2">
			Address
		</th>
	</tr>
	<tr class="rowA">
		<td width="20%" rowspan="5"><strong>Address</strong></th>
		<td><input name="addressLine1" style="width: 250px;"> [ line 1 ]</td>
	</tr>
	<tr class="rowB">
		<td><input name="addressLine2" style="width: 250px;"> [ line 2 ]</td>
	</tr>
	<tr class="rowA">
		<td><input name="city" style="width: 150px;"> [ city ]</td>
	</tr>
	<tr class="rowB">
		<td><input name="state" style="width: 100px;"> [ state ]</td>
	</tr>
	<tr class="rowA">
		<td><input name="zip" style="width: 75px;"> [ zip ]</td>
	</tr>
	
	<tr class="rowB">
		<td><strong>Country</strong></th>
		<td><select name="cid" style="width: 150px;">
		';
		
		foreach ($out[$c]['countries'] as &$c) {
			echo '<option value="', $c['cid'], '">', $c['country'], '</option>';
		}
		
		echo '
		</select></td>
	</tr>
	
	
	<tr class="rowH">
		<th colspan="2">Internet Banking</th>
	</tr>
	<tr class="rowA">
		<td><strong>Username</strong></th>
		<td><input name="iUser" style="width: 150px;"></td>
	</tr>
	<tr class="rowB">
		<td><strong>Password</strong></th>
		<td><input name="iPass" style="width: 150px;"></td>
	</tr>
	
	<tr class="rowH">
		<th colspan="2">Phone Banking</th>
	</tr>
	<tr class="rowA">
		<td><strong>Username</strong></th>
		<td><input name="pUser" style="width: 150px;"> [numbers only]</td>
	</tr>
	<tr class="rowB">
		<td><strong>Password</strong></th>
		<td><input name="pPass" style="width: 150px;"> [numbers only]</td>
	</tr>
	
	
	
	<tr class="rowH">
		<th colspan="2">Phone Banking</th>
	</tr>
	<tr class="rowA">
		<th colspan="2"><input type="submit" value="Add user &raquo;"></th>
	</tr>
	
	</table>
	</form>
	</center>
	<br /><br />
	
	';
	template_Footer();
}
