		<div class="center">
		<br /><br /><?php 
	if ($data == 'check_login') {
		echo '<span class="info">Incorrect username or password was entered.</span>';
	} elseif ($data == 'no_response') {
		echo '<span class="warning">There was no response from the backend server</span>';
	} elseif ($data == 'bad_response') {
		echo '<span class="warning">A bad response was given by the backend server</span>';
	} elseif ($data <> '') {
		echo '<span class="info">An unknown error occured.</span>';
	} else {
		echo '<br />';
	} ?>
		<h1>User Login</h1>
		<form action="/login/" method="POST">
		<table width="75%" align="center">
		<tr>
		<td width="50%" style="text-align: right;"><b>Username:&nbsp;</b></td>
		<td width="50%">
			<input type="text" name="user">
		</td>
		</tr>
		<tr>
		<td width="50%" style="text-align: right;"><b>Password:&nbsp;</b></td>
		<td width="50%">
			<input type="password" name="pass">
		</td>
		</tr>
		<tr>
		<td colspan="2" style="text-align: center;">
			<input type="submit" value="Submit &raquo;">
		</td>
		</tr>
		</table>
		</form>
		<br /><br /><br />
		</div>
