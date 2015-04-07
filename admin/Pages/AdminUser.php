<?php
//---
//---        [ MyBanco Administration Welcome ]
//---
//---  This page allows admins to configure thier MyBanco system \o/
$out = sendRequest();
checkSession2($out);

template_Header('Administration &raquo; Users');
userHome();
template_Footer();



function userHome () {

echo '

<h2>User administration</h2>
<div style="text-align: center;">
	<img src="/i/48/back.png"> <a href="/admin/">Back to administration</a>
</div>

<table width="60%" style="width: 60%; margin: 0 auto;">
<tr class="rowH"><th colspan="2">Actions</th></tr>

<tr class="rowA">
	<td width="48"><img src="/i/48/user-add.png" alt="add user"></td>
	<td>
		<h2><a href="/admin/+user/">Add user</a></h2>
		Add a new user (and bank account) to the system.
	</td>
</tr>

<tr class="rowB">
	<td width="48"><img src="/i/48/users.png" alt="user"></td>
	<td>
		<h2><a href="/admin/user/">Manage Users</a></h2>
		Edit user details, add new accounts.
	</td>
</tr>

<tr class="rowA">
	<td width="48"><img src="/i/48/user-remove.png" alt="user"></td>
	<td>
		<h2><a href="/admin/-user/">Remove User</a></h2>
		Remove a user, and all associated links from MyInfo.
	</td>
</tr>

</table>

<br /><br />

';
}
