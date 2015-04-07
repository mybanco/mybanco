<?php global $pathInfo ?>
<h3>Transfer preview</h3>

<strong>Step Three</strong>! Please confirm the transaction you are about to complete.

<br /><br />

<form method="POST" action="/transfer/account/<?php echo $account . '/' . $pathInfo[3] . '/doTransaction'; ?>">
<center>
	<table width="80%" style="width: 80%;">
	<tr class="rowH">
		<th style="text-align: center;" colspan="2">
			Transaction details
		</th>
	</tr>
	
	<tr class="rowA">
		<td width="25%"><strong>From account</strong></td>
		<td>Account #<?php echo $account; ?></td>
	</tr>
<?php

if ($data['to_account'])
	echo '
	<tr class="rowB">
		<td width="25%"><strong>To account</strong></td>
		<td>Account #', $data['to_account'], '</td>
	</tr>
';

echo '
	<tr class="rowH">
		<th style="text-align: center;" colspan="2">
			Other
		</th>
	</tr>
	<tr class="rowB">
		<td> Amount before transaction </td>
		<td><strong>', $data['amount_after_transfer'], '</strong></td>
	</tr>
	<tr class="rowA">
		<td> Transaction amount</td>
		<td><strong>', $data['amount'], '</strong></td>
	</tr>
	
	<tr class="rowH">
		<th style="text-align: center;" colspan="2">
			Save details ...
		</th>
	</tr>
	
	<tr class="rowB">
		<td colspan="2" style="text-align: center;">
			To verify and accept these details, please enter the following code
			into the text box below.
		</td>
	</tr>
	
	<tr class="rowA">
		<td colspan="2" style="text-align: center;">
			<div style="font-size: 18pt; font-weight: bold;"><tt>',
				substr( md5($data['secret'] . "secret") , -5 )
				,'</tt>
			</div>
		</td>
	</tr>
	
	<tr class="rowB">
		<td colspan="2" style="text-align: center;">
			<input name="code" style="width: 100px; text-align: center;" />
		</td>
	</tr>
	
	<tr class="rowA">
		<td colspan="2" style="text-align: center;">
			<input type="hidden" value="" name="fields">
			<input type="submit" value="Transfer &raquo;">
		</td>
	</tr>
	</table>
</center>
<input type="hidden" name="transactionID" value="', $data['secret'],'" />
</form>
';
