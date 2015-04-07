<div style="text-align: center; font-size: 14px; font-weight: bold;">
	<br /><img src="/i/48/money.png" alt="[c]" /><br />
	Search results<br />
</div>
<div style="text-align: center; font-size: 13px;">
	<?php=$results?> result(s) found.
</div>

<div style="text-align: center;">
	<br />
	<form action="/stocks/search/" method="POST">
	<input name="ticker" style="width: 40%;"<?php if (isset($search)) echo " value='", htmlspecialchars($search), "'";?>>
	<input value="Search &raquo;" type="submit">
	</form>
	<br />
</div>

<table width="90%">
<tr class="rowH">
	<th width="10%">Symbol</th>
	<th width="75%">Company Name</th>
	<th width="15%">Last Price</th>
</tr>

<?php
if (is_array($data['result']))
foreach ($data['result'] as &$result) {
echo '
<tr class="rowA">
	<td style="font-weight: bold;">
		<a href="/stock/', $result['ticker'], '/">', $result['ticker'], '</a>
	</td>
	<td>', $result['compayName'],'</td>
	<td>', $account['balance'],'</td>
</tr>
<tr class="rowB">
	<td colspan="3">
		', htmlspecialchars($result['companyDescription']), '
	</td>
</tr>
';
}
?>

</table>

<div style="text-align: center;">
	<form action="/stocks/search/" method="POST">
	<input name="ticker" style="width: 40%;"<?php if (isset($search)) echo " value='", htmlspecialchars($search), "'";?>>
	<input value="Search &raquo;" type="submit">
	</form>
</div>
