<?php
	echo '
<table>
	<tr class="rowH">
		<th colspan="2">Functionality Enabled on MyBanco</th>
	</tr>
';
	foreach ($have as $module) {
	echo '
	<tr class="rowA">
		<td width="16"><img src="/Images/y.png" /></td>
		<td>[<strong>', $module, '</strong>] ', $functionalities[$module], '</td>
	</tr>
';
	}
	echo '
	<tr class="rowH">
		<th colspan="2">Functionality Disabled on MyBanco</th>
	</tr>
';
	foreach ($missing as $module) {
	echo '
	<tr class="rowA">
		<td width="16"><img src="/Images/n.png" /></td>
		<td>[<strong>', $module, '</strong>] ', $functionalities[$module], '</td>
	</tr>
';
	}
	echo '
</table>
';
?>
