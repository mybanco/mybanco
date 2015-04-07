<?php
echo '
<img src="/Images/happy.jpg" align="right">
<h3>My Accounts</h3>
Hello <strong>' , $title , '. ' , $firstName , ' ' , $lastName, '</strong><br />
 &nbsp; &nbsp; &nbsp; -
Your last logon was ';

if ($lastLoginTime == 0)
	echo '<strong>never</strong>. Welcome to our banking system.';
else
	echo timeDiff($lastLoginTime, true);

echo '

<br /><br />
To view a summary of your account details and obtain further transaction information please select an account from the list below.
<br /><br />
';
