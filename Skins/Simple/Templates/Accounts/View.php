<h3>My account details</h3>
<h2>
	<img src="/i/48/money.png" alt="[c]">
	<?php echo $data['accountInfo']['description']; ?>
</h2>
<strong>As at</strong> <?php echo strftime($CONFIG['date_short+t']); ?><br />

<?php

if ($data['accountInfo']['sign'] == "")
	$data['accountInfo']['sign'] = chr($data['accountInfo']['chr']);
?>

<h3><img src="/i/32/chart.png" alt="_-^"> Transactions for the period <?php echo
strftime($CONFIG['date_short'], $data['startTime']); ?> to <?php echo (
$data['endTime']==null) ? "now" : strftime($CONFIG['date_short'], $data['endTime']); ?></h3>
<table>
<tr class="rowH">
	<th width="15%">Date</th>
	<th>Description</th>
	<th width="15%">Debit</th>
	<th width="15%">Credit</th>
	<th width="15%">Balance</th>
</tr>

<tr class="rowA">
	<td colspan="4" style="text-align: right;">
		<strong>Opening balance &nbsp;</strong> &nbsp;
	</td>
	<td><?php echo $data['accountInfo']['sign'] . $data['openingBalance']; ?></td>
</tr>

<?php if (isset($data['transactions']) AND is_array($data['transactions']) AND count($data['transactions']) > 0) {
	$x = 0;
	$b = $data['open'];
	while ($x < count($data['transactions'])) {
		if ( ($data['transactions'][$x]['credit'] == -1) )
			$b -= $data['transactions'][$x]['amount'];
		else
			$b += $data['transactions'][$x]['amount'];
echo '
<tr class="rowB">
	<td>', strftime($CONFIG['date_short'], $data['transactions'][$x]['transactionTime']) ,'</th>
	<td>',$data['transactions'][$x]['description'],'<br />(<i>',
		strftime($CONFIG['date_normal+t'], $data['transactions'][$x]['transactionTime']),
	'<i>)</td>
	<td>', ($data['transactions'][$x]['credit'] == -1) ? '-<font color="red">' .
			$data['accountInfo']['sign'] .
			number_format($data['transactions'][$x]['amount'],2) . '</font>' : '' ,'</td>
	<td>', ($data['transactions'][$x]['credit'] ==  1) ? '+<font color="green">' .
			$data['accountInfo']['sign'] .
			number_format($data['transactions'][$x]['amount'],2) . '</font>' : '' ,'</td>
	<td>', $data['accountInfo']['sign'] . number_format($b,2) ,'</td>
</tr>';
	$x++;
	}
}?>

<tr class="rowA">
	<td colspan="4" style="text-align: right;">
		<strong>Closing balance &nbsp;</strong> &nbsp;
	</td>
	<td><?php

	if ($data['closingBalance'] < 0) {
		echo '<font color="red">';
	} else {
		echo '<font color="green">';
	}

	echo $data['accountInfo']['sign'] . $data['closingBalance']; ?></font></td>
</tr>

</table>
