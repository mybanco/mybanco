<br />
<table width="90%">
<tr class="rowH">
	<th width="10%">Ticker</th>
	<th width="50%">Company Name</th>
	<th width="15%">Amount</th>
	<th width="20%">Market Price</th>
	<th width="5%">.</th>
</tr>

<?php
if (is_array($data)) {
// We have accounts!1
foreach ($data as &$account) {
echo '
<tr class="rowA">
	<td><img src="/i/24/money.png" alt="[c]" />
		', $account['description'], '
	</td>
	<td>', $account['aid'],'</td>
	<td>', $account['balance'],'</td>
</tr>
<tr class="rowB">
	<td colspan="3" style="text-align: center;">
		[ <a href="/view/account/', $account['aid'], '">View &raquo;</a> ]
';
	if ($account['canEdit'] == 1)
		echo "\t\t\t", '[ <a href="/transfer/account/', $account['aid'], '">Send/transfer money &raquo;</a> ]';
	else
		echo "\t\t\t[ <strong>This account is for view only.</strong> ]";
echo '
	</td>
</tr>
';
}
} else {
	echo '
<tr class="rowA">
	<td colspan=5>
		<div style="text-align: center; font-size: 14px; font-weight: bold;">
		<img src="/i/48/money.png" alt="[c]" /><br />
		You presently have no stocks!<br />
		</div>
		
		<div style="text-align: center;">
			<a href="/stocks/">Buy some stocks &raquo;</a>
		</div>
	</td>
</tr>
';
}
?>

</table>
