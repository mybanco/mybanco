<?php
//---
//---        [ MyBanco Administration Welcome ]
//---
//---  This page allows admins to configure thier MyBanco system \o/
global $INI;
$stats   = addRequest('admin', 'statistics', Array());
$version = addRequest('core', 'MyInfoVersion', Array());
$out = sendRequest($INI);
checkSession2($out);

// Authors
$authors = array_to_english($out['packet:4']['authors']);
if ($out['packet:4']['license'] == "agpl")
	$license = '<a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html">Affero General Public License [version 3]</a>';

template_Header('Administration: Welcome');
echo '

<h2>MyInfo administration</h2>

Welcome to the <strong>MyInfo Administration System</strong>. From here, you can add/edit and remove many records that are maintained by the system. This frontend to MyInfo will allow you to: <br /><br />
<ul>
	<li>set application wide options and preferences.</li>
	<li>control permissions for users and guests.</li>
	<li>view IP statistics for users.</li>
	<li>ban users.</li>
	<li>&quot;create&quot; money in the MyBanco system</li>
</ul><br />

<table width="60%" style="width: 60%; margin: 0 auto;">
<tr class="rowH"><th colspan="2">Actions</th></tr>

<tr class="rowA">
	<td width="48"><img src="/i/48/system.png" alt="system"></td>
	<td>
		<h2><a href="/admin/system/">System Maintenance</a></h2>
		Set core data about the system here. Details such as the main currency to be used by the system etc
	</td>
</tr>

<tr class="rowB">
	<td width="48"><img src="/i/48/users.png" alt="user"></td>
	<td>
		<h2><a href="/admin/users/">User Maintenance</a></h2>
		Create and edit users and accounts. Link extra accounts to new users, and set there access limits.
	</td>
</tr>

<tr class="rowA">
	<td width="48"><img src="/i/48/package.png" alt="pacakges"></td>
	<td>
		<h2><a href="/admin/packages/">Packages Administration</a></h2>
		Set details about accounts, create new templates and move accounts between template types.
	</td>
</tr>

</table>

<h2>Statistics</h2>
<strong>MyInfo Version</strong><br />
<div style="margin-left: 25px;">
	<strong>MyInfo</strong> ' . $out['packet:4']['version'] . '<br />
	&copy; Copyright 2007, 2008, 2009 ' . $authors . '<br />';
	if ($license)
		echo 'Licensed under the ' . $license;
	else
		echo 'Modified without permission';

echo '</div><br />

<strong>Server load</strong>
<div style="margin-left: 25px;">
	Database Size: ' . number_format($out['packet:3']['size'],1) . " KB ({$out['packet:4']['database']})
</div><br />";

template_Footer();





function array_to_english ( &$list, $glueword='and' ) {
	$string = false;
	$glue = '';
	foreach ( array_reverse ( $list ) as $index=>$value ) {
		$string = "<strong>$value</strong>$glue$string";
		if ( $index == 0 ) $glue = " $glueword ";
		if ( $index == 1 ) $glue = ', ';
	}
	return $string;
}
