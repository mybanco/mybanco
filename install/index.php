<?php
/*
    MyInfo/MyBanco install script version 0.11

    Copyright (C) 2008, Tim Groeneveld

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

*/
global $versionCheckURL, $currentVersion, $CONFIG;
$versionCheckURL = 'http://mybanco.org/phonehome/';
$currentVersion  = '0.11';

$CONFIG['bank'] = 'MyBanco Installer';

header('X-Powered-By: MyBanco Install & Update');
define('_START', microtime());
require '../Skins/Simple/Simple.php';

if (file_exists('../config.php') and filesize('../config.php') > 2) {
	template_Error(700);
}

if (! isset($_GET['step'])) {
	// The very first page is here!
	page1();
} elseif ($_GET['step'] == "1") {
	page2();
} elseif (!isset($_POST) or !is_array($_POST) or !isset($_POST['start'])) {
	page1();
} elseif ($_GET['step'] == "2" AND $_POST['start'] == 'y') {
	page3();
} elseif ($_GET['step'] == "3" AND $_POST['start'] == 'yes') {
	page4();
} elseif ($_GET['step'] == "4" AND $_POST['start'] == 'yes') {
	page5();
}

function page1 () {
	// First, check and make sure that the license is in either
	// THIS folder, or the FOLDER BELOW!
	if (file_exists('../LICENSE'))		$file = '../LICENSE';
	elseif (file_exists('./LICENSE'))	$file = './LICENSE';
	else die('Missing license file.');
	template_Header( "Install &amp; Configure MyBanco" );
	
	echo '
<br /><br />

<h1>Welcome to MyBanco &amp; MyInfo</h1>
<strong>MyBanco</strong> is a kit made to run a \'bank\'. This \'bank\' only does core banking,
so the software does not take in account things like FOR-EX [foreign exchange] and other forms
of trading (such as a stock exchange).<br /><br />

<strong>MyInfo</strong> is a network-aware Remote procedure call protocol encoded in JSON.
<br /><br />

These applications <strong>mix together</strong> to create banking software that is easily
distributed (ie, it can handle heavy loads over multiple machines).<br /><br />

<h2>Enough chat!</h2>
Get me started on the installation and configuration script!
<a href="./?step=1">Click here and install it &raquo;</a>
<br /><br /><br /><br /><br /><br />
';
	template_Footer();
}

function page2 ($fail = false) {
	
	if (file_exists('../LICENSE'))		$file = '../LICENSE';
	elseif (file_exists('./LICENSE'))	$file = './LICENSE';
	else die('Missing license file. Please download the AGPL from http://www.gnu.org/licenses/agpl-3.0.txt and place it in the install/ folder. Make sure it is called \' LICENSE \'');
	
	if (md5_file($file) <> "73f1eb20517c55bf9493b7dd6e480788")
		die('Invalid license file. Please download the AGPL from http://www.gnu.org/licenses/agpl-3.0.txt and place it in the install/ folder. Make sure it is called \' LICENSE \'');
	
	template_Header( "Install &amp; Configure MyBanco" );
	
	if ($fail == true) {
		echo '<pre style="color: red;">Please select \'I Agree\' in order to continue installation.</pre>';
	}
	
echo '
<script>
function checkCheckBox(f) {
  if (f.agree.checked == false) {
    alert(\'Please check the box to continue.\');
    return false;
  } else
    return true;
}
</script>

<h2>The License</h2>
<pre>',
file_get_contents($file)
,'
</pre>

<form action="./?step=2" method="POST" onsubmit="return checkCheckBox(this)">
I accept: <input type="checkbox" value="0" name="agree">
<input type="submit" value="Install &raquo;">
<input type="hidden" name="start" value="y">
</form>
';
	template_Footer();
}

function page3 () {
	// Check that the system has the correct ingredients :)
	
	if (!isset($_POST['agree']) or $_POST['agree'] != '0') {
		page2(true); // fail.
		exit;
	}
	
	template_Header( 'System Check' );
	$f1 = checkSystem_modules (array (
		'json' => 'JavaScript Object Notation',
		'pcre' => 'Regular Expressions (Perl-Compatible)',
		'curl' => 'Client URL Library (cURL)',
		'mysql' => 'PHP MySQL Extension',
		), array(
		'../config.php' => 'MyBanco configuration',
		'../backend/config.php' => 'MyInfo configuration',
		) );
	if ($f1 == false) {
echo '
	<table width="75%" style="width: 75%; margin-left: auto; margin-right: auto;">
		<tr class="rowH">
			<th colspan="2">You can continue!</th>
		</tr>
		<tr class="rowA">
			<td width="48"><img src="./yes.png" alt="YES!" /></td>
			<td>Yes, you are able to install this version of MyBanco!</td>
		</tr>
		<tr class="rowB">
			<td colspan="2" style="text-align: center;">
			<form action="./?step=3" method="POST"">
			<input type="hidden" name="start" value="yes">
			<input type="submit" value="Start configuring ... &raquo;">
			</form>
			</td>
		</tr>
	</table>
';
	} else {
echo '
	<table width="75%" style="width: 75%; margin-left: auto; margin-right: auto;">
		<tr class="rowH">
			<th colspan="2">You can\'t continue!</th>
		</tr>
		<tr class="rowA">
			<td width="48"><img src="./no.png" alt="NO." /></td>
			<td>
				Sorry, but you either do not have all the required modules to install
				this version of MyBanco, or one or more files cannot be written to!
			</td>
		</tr>
	</table>
';
	}
	template_Footer();
}

function checkSystem_modules($modules, $files) {
	$fail = false;
	echo '
<table>
	<tr class="rowH">
	<th colspan="2">PHP Module Check</th>
	</tr>
';
	foreach ($modules as $x=>&$name) {
		if (extension_loaded($x)) {
			$i = 'y';
		} else {
			$fail = true;
			$i = 'n';
		}
	echo '
	<tr class="rowA">
		<td width="16"><img src="', $i, '.png" /></td>
		<td>[<strong>', $x, '</strong>] ', $name, '</td>
	</tr>
';
	}
	echo '
	<tr class="rowH">
	<th colspan="2">File check</th>
	</tr>
';
	foreach ($files as $x=>&$name) {
		if (is_writable($x)) {
			$i = 'y';
		} elseif (file_exists($x)) {
			$fail = true;
			$i = 'h';
			$name .= ' <strong><font color="red">ERROR</font></strong>: FILE IS NOT WRITABLE!';
		} else {
			if (is_writable( dirname( realpath( $x ) ) ) ) {
				$y = 'y';
			} else {
				$fail = true;
				$i = 'n';
			}
		}
	echo '
	<tr class="rowA">
		<td width="16"><img src="', $i, '.png" /></td>
		<td>[<strong>', $x, '</strong>] ', $name, '</td>
	</tr>
';
	}
echo '
<table>
';
return $fail;
}

//--
//-- PAGE FOUR!
//--
function page4($invalid='') {
	template_Header("System Configuration");
	
	if (is_array($invalid)) {
		echo '<pre>';
		print_r($invalid);
		echo '</pre>';
	}
	
	echo '
<form action="./?step=4" method="POST">
<br />
<h2>MyBanco Info ...</h2>
<table>
	<tr class="rowH">
		<th colspan="2">MyInfo Setup ...</th>
	</tr>
	
	<tr class="rowA">
		<td colspan="2">
			MyInfo is the basic application that stores data, and provides
			a simple to use, yet advanced method to retrieve that data.
		</td>
	</tr>
	
	<tr class="rowB">
		<td width="25%"><strong>MySQL Host</strong></td>
		<td><input name="mysql-host" value="\';
		if (isset($_POST[\'mysql-host\']))
			echo htmlspecialchars($_POST[\'mysql-host\']);
		
		echo \'"></td>
	</tr>
	<tr class="rowB">
		<td width="25%"><strong>MySQL Username</strong></td>
		<td><input name="mysql-user" value="';
		if (isset($_POST['mysql-user']))
			echo htmlspecialchars($_POST['mysql-user']);
		
		echo '"></td>
	</tr>
	<tr class="rowA">
		<td width="25%"><strong>MySQL Password</strong></td>
		<td><input name="mysql-pass" value="';
		
		if (isset($_POST['mysql-pass']))
			echo htmlspecialchars($_POST['mysql-pass']);
		
		echo '"></td>
	</tr>
	<tr class="rowB">
		<td width="25%"><strong>MySQL Database</strong></td>
		<td><input name="mysql-data" value="';
		
		if (isset($_POST['mysql-data']))
			echo htmlspecialchars($_POST['mysql-data']);
		
		echo '"></td>
	</tr>
	<tr class="rowA">
		<td width="25%"><strong>Country Name</strong></td>
		<td><input name="country" value="';
		
		if (isset($_POST['country']))
			echo htmlspecialchars($_POST['country']);
		
		echo '"></td>
	</tr>
	
	
	<tr class="rowH">
		<th colspan="2">MyBanco Customer Frontend Setup ...</th>
	</tr>
	
	<tr class="rowA">
		<td colspan="2">
			MyBanco is a frontend for the bank, transfer and loan plugins for MyInfo.
			MyBanco is the administration, internet and phone banking software that
			hooks into MyInfo, where it stores its data.
		</td>
	</tr>
	
	<tr class="rowB">
		<td width="25%"><strong>Bank Name</strong></td>
		<td><input name="bank" style="width: 50%;" value="';
		if (isset($_POST['bank']))
			echo htmlspecialchars($_POST['bank']);
		
		echo '"></td>
	</tr>

	<tr class="rowH">
		<th colspan="2">MyInfo location</th>
	</tr>
	
	<tr class="rowA">
		<td colspan="2">
			The MyInfo location is required, because MyBanco does not directly communicate
			with any databases. It uses MyInfo to talk to a database, and to do some of its
			core calculations.<br />
			For a default setup, if this URL is similar to <strong>http://localhost/install/?step=3</strong>,
			then the MyInfo URL would be <strong>http://localhost/backend/</strong>.
		</td>
	</tr>
	
	<tr class="rowB">
		<td width="25%"><strong>MyInfo Backend Location</strong></td>
		<td><input name="myinfo" style="width: 75%;" value="';
		if (isset($_POST['mysql-user']))
			echo htmlspecialchars($_POST['myinfo']);
		
		echo '"></td>
	</tr>
	
	<tr class="rowH">
		<th colspan="2">Anonymous usage collection</th>
	</tr>
	
	<tr class="rowA">
		<td colspan="2">
			When this option is turned on, MyBanco will "phone home" with statistics of what functions are
			used inside MyBanco and MyInfo. All data is annonymous, and the data that the server holds can be
			viewed at any time. Turning on this function will also show notifications in the admin panel when
			there is a new version of MyBanco.
		</td>
	</tr>
	
	<tr class="rowB">
		<td colspan="2" style="text-align: center;">
			<input type="checkbox" name="collection" value="true" checked="checked" id="collection" style="vertical-align: center;">
			<label for="collection"><strong>Allow MyBanco to send anonymous usage statistics</strong></label>
		</td>
	</tr>
	
	
	<tr class="rowH">
		<th colspan="2">Save data</th>
	</tr>
	<tr class="rowB">
		<td colspan="2" style="text-align: center;">
			<input type="hidden" name="start" value="yes">
			<input type="submit" value="Save configuration ... &raquo;">
		</td>
	</tr>
</table>
</form>
';
	template_Footer();
	exit;
}

function page5() {
	// --
	// -- Attempt to phone home to see if there is a new version, and to register the system
	// --
	global $CONFIG, $versionCheckURL, $currentVersion;
	$CONFIG['myinfo-servers'] = array($versionCheckURL);
	require '../libs/MyInfo-Client.php';
	
	newRequest();
	$server  = addRequest('usage', 'addNewServer');
	$details = sendRequest();
	
	$update = &$details[$server];
	if ($update['version'] <> $currentVersion) {
		echo '<h1>There is a new version availible!</h1>';
		echo "Version {$update['version']} was released " . date('r', $update['released']);
		echo '<ul>';
		
		if ($update['anouncement_url']) {
			echo '<li>For the release announcement, please go to <a href="' . $update['anouncement_url'] . '">' . $update['anouncement_url'] . '</a>';
		}
		
		if ($update['update_url']) {
			$url = $update['update_url'];
			$url = str_replace("{VERSION}", $currentVersion, $url);
			echo '<li>For the changelog since this release, please goto <a href="' . $url . '">' . $url . '</a>';
		}
		
		if ($update['url']) {
			echo '<li>To visit the MyBanco website, please goto <a href="' . $update['url'] . '">' . $update['url'] . '</a>';
		}
		echo '</ul>';
	} else {
		echo "<h3>You are using the latest version!</h3>";
	}
	
	echo "<h1>Viewing the data you send to us</h1>";
	echo 'When you enable statistics tracking, you will send usage details to our server. For full transperency, we allow you to view the information we keep ';
	echo 'about your installation. To view this information, please goto the following URL:<br /><br />';
	$url = $update['viewurl'];
	$url = str_replace("{MACHINEID}", $update['machineID'], $url);
	echo "<a href='$url'>$url</a><br /><br />";
	echo "and use the following key to view: <strong>{$update['machineKey']}</strong>";
	echo "<h1>Installing Database...</h1>";
	
	// --
	// -- Check the MySQL data :D
	// --
	if ($_POST['mysql-user']<>"" AND !preg_match('/^[a-z0-9A-Z\-\_@]{2,16}$/', $_POST['mysql-user']))
		$invalid[] = 'MySQL Username is invalid';
	
	if ($_POST['mysql-pass']<>"" AND !preg_match('/^[a-z0-9A-Z\-\_]{2,16}$/', $_POST['mysql-pass']))
		$invalid[] = 'MySQL Password is invalid';
	
	if ($_POST['mysql-data']<>"" AND !preg_match('/^[a-z0-9\-\_]{2,16}$/', $_POST['mysql-data']))
		$invalid[] = 'MySQL Databse is invalid';
	
	if (!preg_match('/^[a-z0-9A-Z\'\s]{2,32}$/', $_POST['country']))
		$invalid[] = 'Country name is invalid';
	
	if (!preg_match('/^[a-z0-9A-Z\'\s]{2,32}$/', $_POST['bank']))
		$invalid[] = 'Bank name is invalid';
	
	if (!preg_match('|^http(s)?://(.*)/|', $_POST['myinfo']))
		$invalid[] = 'MyInfo URL invalid';
	
	if (is_array($invalid)) page4($invalid);
	
	echo '<pre>';
	echo "[ MySQL Database ...]\n";
	echo 'Connecting to MySQL ... ';
	if (!mysql_connect($_POST['mysql-host'], $_POST['mysql-user'], $_POST['mysql-pass'])) {
		echo "FAIL!";
		exit;
	} else  echo "DONE!\n";
	
	echo 'Selecting database  ... ';
	if (!mysql_select_db($_POST['mysql-data'])) {
		echo "FAIL!";
		exit;
	} else  echo "DONE!\n";
	
	echo 'Opening import.sql  ... ';
	if (!file_exists('import.sql')) {
		echo "FAIL! [File not found]";
		exit;
	}
	
	$sql = file('import.sql');
	if (!is_array($sql)) {
		echo "FAIL!";
		exit;
	} else  echo "DONE!\n\n\n";
	
	foreach ($sql as &$statement) {
		if ($statement == "")
			continue;
		if ($statement == "\n")
			continue;
		echo substr($statement,0,64), "...\n";
		
		echo "   => Issueing MySQL Query ...";
		$res = mysql_query($statement);
		if ($res == "") {
			echo "FAIL!";
			exit;
		} else  echo "DONE!\n";
		
		
		echo "\n";
	}
	
	echo 'Adding machine ID and machine secret to `config` table... ';
	$res = mysql_query("INSERT INTO `config` (`item`, `value`) VALUES ('machineID',  '{$update['machineID']}');");
	$res2= mysql_query("INSERT INTO `config` (`item`, `value`) VALUES ('machineKey', '{$update['machineKey']}');");
	$res3= mysql_query("INSERT INTO `config` (`item`, `value`) VALUES ('verCheck',   '$versionCheckURL');");
	if ($res == "" or $res2 == "" or $res3 == "") {
		echo "FAIL!\n";
		exit;
	} else  echo "DONE!\n\n";
	
	echo 'Generating "../backend/config.php" ... ';
	$config = "<?php
//---
//---           [ MyInfo Configuration ]
//---
//---  MyInfo is the basic application that stores data,
//---  and provides functions that enable applications
//---  to search through that data through the use of
//---  plugins.
//---

global \$SYSTEM, \$CONFIG;

//---
//--- Please ensure this infomation is correct
//---
\$CONFIG['mysql-host'] = '{$_POST['mysql-host']}';
\$CONFIG['mysql-user'] = '{$_POST['mysql-user']}';
\$CONFIG['mysql-pass'] = '{$_POST['mysql-pass']}';
\$CONFIG['mysql-data'] = '{$_POST['mysql-data']}'; //database
\$CONFIG['self-creation'] = true;

//--- The enabled plugins
\$CONFIG['plugins'] = Array('core', 'sessions', 'admin', 'bank', 'transfer', 'register');

\$CONFIG['integrations'] = Array('visa');

\$PLUGINS['register']['canRegister'] = true;

\$PLUGINS['core'] = '';

//---
//--- Country name
//---
\$CONFIG['country'] = '{$_POST['country']}';

//---
//--- DO NOT CHANGE THIS INFOMATION
//---
\$SYSTEM['version']     = '0.11';

?>";
	echo "DONE!\n";
	echo 'Writting   "../backend/config.php" ... ';
	
	$return = file_put_contents( "../backend/config.php", $config );
	if ($return <> true) {
		echo "FAIL!";
		exit;
	} else  echo "DONE!\n";
	
	$URL = dirname($_POST['myinfo']);
	echo 'Generating "../config.php" ... ';
	$config = "<?php
//---
//---           [ MyBanco Configuration ]
//---
//---  MyBanco is a frontend for the bank plugin for MyInfo. It
//---  uses cURL, so most of the transactions are pretty fast.
//---
global \$SYSTEM, \$CONFIG;

//---
//--- Country name
//---
\$CONFIG['country'] = '{$_POST['country']}';
\$CONFIG['bank']    = '{$_POST['bank']}';
\$CONFIG['skin']    = 'Simple';
\$CONFIG['self-creation'] = true; // Users can create bank accounts and users

//---
//--- MyInfo address
//---
// MyBanco needs access to a MyInfo server
//
\$CONFIG['myinfo-servers'] = array(
	'{$_POST['myinfo']}'
);

\$CONFIG['url'] = '$URL';

//--- Presentation formats
\$CONFIG['date_short'] = '%d/%m/%y';		// 25/12/2008
\$CONFIG['date_short+t'] = '%d/%m/%y %I:%M%P';	// 25/12/2008 12:59pm
\$CONFIG['date_normal'] = '%d %B %y';		// 25 December 2008
\$CONFIG['date_normal+t'] = '%d %B %y  %I:%M%P';	// 25 December 2008 12:59pm


//--- MyStocko Integration
\$CONFIG['mystocko-integration'] = false;
\$CONFIG['mystocko-name'] = 'The Stock Exchange';

//---
//--- DO NOT CHANGE THIS INFOMATION
//---
\$SYSTEM['version']     = '0.11';

?>";
	echo "DONE!\n";
	echo 'Writting   "../config.php" ... ';
	
	$return = file_put_contents( "../config.php", $config );
	if ($return <> true) {
		echo "FAIL!";
		exit;
	} else  echo "DONE!\n";
	
	echo "\n\n";
}
?>
