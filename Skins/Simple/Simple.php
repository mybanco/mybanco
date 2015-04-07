<?php

function template_Header($title = "") {
global $CONFIG, $session, $loggedIn;

if ($title <> "")
	$title = ' &raquo; ' . $title;

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<title>', $CONFIG['bank'] , $title  , '</title>
<link rel="stylesheet" type="text/css" href="/Skins/Simple/default.css" />
<style>
img, div { behavior: url(iepngfix.htc) }

.debug {
	width: 99%;
	padding: 5px;
	background-color: #f5f5f5;
	color: #666;
	border: 1px solid #000000;
	font-weight: bold;
	position: absolute;
        left: -10000px;
}
</style>

<script>
function toggleDivOL( elemID )
{
    var elem = document.getElementById( elemID );
    if (elem.style.position == "")
       elem.style.position = "absolute";
    if( elem.style.position != "absolute")
    {
        elem.style.position = "absolute";
        elem.style.left = "-4000px";
    }
    else
    {
        elem.style.position = "relative";
        elem.style.left = "0px";
    }
}
</script>
</head>

<body>

<div style="width: 700px; margin: 0 auto;">
	
	<a href="/about/"><div class="logo"></div></a>
';

// print the menu
if ($loggedIn)
	echo '
	<ul class="solidblockmenu">
		<li><a href="/" class="current">Accounts</a></li>
		<li><a href="/logout/">Logout</a></li>
	</ul>
	<br style="clear: left" /><br />
';
else // we print the poor (ie, home page) menu
	echo '
	<ul class="solidblockmenu">
		<li><a href="/" class="current">Home</a></li>
		<li><a href="/whyus/">Why us?</a></li>
		<li><a href="/about/">About</a></li>
	</ul>
	<br style="clear: left" /><br />
';


echo '
	<div class="shadowcontainer">
	<div class="innerdiv">
';

}

function template_Footer() {
	global $CONFIG;
	echo '
	</div>
	</div>
	</div>
	<br />
	
	<div class="center">
	&copy; ', $CONFIG['bank'] ,', 2008 <br />
	<a href="javascript:toggleDivOL(\'toHide\');" title="Hide the DIV">Hide/Show the debug</a>';
	list($secs, $usecs) = explode(' ', _START);
	$start = $secs + $usecs;
	list($secs, $usecs) = explode(' ', microtime());
	$finish = $secs + $usecs;
	//$time = $finish - $start;
	
	$time = ($finish - $start) . '00000000';
	$time = number_format($time, 5);
	
	echo '
		<br />It took roughly <strong>' . $time . ' seconds</strong> to generate this page.<br /><br />
		Best viewed in <a href="http://www.firefox.com/">Mozilla Firefox</a>, <a href="http://www.apple.com/">Apple
		Safari</a> or <a href="http://www.kde.org/">Konqueror</a><br /><br />
	</div>


</div>
</body>
</html>';
}

function template_Error($number = 18, $info=null) {
	$error['unknown'] = 'This error is unknown ...';
	$error[100] = 'No input. Please read the MyInfo development documentation';
	$error[101] = 'Cannot connect to Database';
	$error[102] = 'Database error';
	$error[201] = 'Application access denied [missing appid]';
	$error[350] = 'The requested action requires a session - and a getSession packet was not sent.';
	$error[355] = 'Session storage names should only contain letters and numbers, and be max 8 chrs long.';
	$error[360] = 'Session data passed onto MyInfo is invalid or non existant';
	$error[361] = 'The requested action required session data, but it is not set';
	$error[401] = 'Woops! The backend URL does not work correctly';
	$error[403] = 'The requested plugin is not availible on this server';
	$error[425] = 'Login failed';
	$error[980] = 'MySQL query error';
	$error[981] = 'Invalid arguments were sent to this function. Please read the bloody manual! THIS IS BAD!';
	$error[982] = 'Invalid data sent to an internal function. This is a fatal error.';
	$error['existsalready'] = 'The data to be inserted already exists.';

	$error[700] = 'Install cannot continue because configuration already exists!';


	$error[401]       = 'No HTTP code was returned';
	$error[402]       = 'MyInfo backend does not funtion! This is usually do to a bad configuration.';

	if (!isset($error[$number]))
		$error[$number] = "This error is unknown ...";
	
	if (isset($info))
		$error[$number] = $info;
	
	if (is_array($info)) {
		$error[$number] = '<pre>' . print_r($info, true) . '</pre>';
	}
	
        template_Header("Runtime Error");
echo '
	<div style="text-align: center;">
		<img src="/i/128/error.png" /><br />
		<h1>Runtime Error!</h1>
		
		<strong>Error Identification String: </strong>';settype($number, 'string');
		echo sprintf("%02x ", $number[0]) .
			sprintf("%02x ", $number[1]) .
			sprintf("%02x ", $number[2]);echo '[' , $number , ']<br /><br />
		
		<center>
		<table width="80%" style="width: 80%;">
		<tr class="rowH" style="text-align: center;">
			<th colspan="2">Reason for this error?</th>
		</tr>
		<tr class="rowA">
			<td width="64"><img src="/i/64/oh-no.png" /></td>
			<td>', $error[$number], '</td>
		</tr>';

echo '
                </table>
                </center>
        </div>';
        template_Footer();
        exit;
}
