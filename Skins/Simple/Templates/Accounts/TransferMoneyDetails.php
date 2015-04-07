<?php global $pathInfo; ?>
<h3>Enter transfer details</h3>

<strong>Step two</strong>! You're almost done! All that is left now is entering a few more
details for the transaction to complete.

<br /><br />

<form method="POST" action="/transfer/account/<?php echo $account; ?>/<?php echo $pathInfo[3]; ?>">
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
$fields = $methods['fields'];
if (is_array( $fields )) {
	$formfd = '';
	foreach ($fields as $name=>&$field) {
		if ($formfd == "")
			$formfd = "$name";
		else
			$formfd.= ";$name";
		$letter = 'A';
		$letter = ($letter == "B" ? "A" : "B");
		
		echo '	<tr class="row', $letter,'">
		<td><strong>',$field['desc'],'</strong></td>
		<td>';
		
		if ($field['type'] == "select") {
			echo '<select name="',$name,'">';
			foreach ($field['choices'] as $name=>&$value) {
				echo '<option value="',$name,'">', $value,'</option>';
			}
			echo '</select>';
		} if ($field['type'] == "text") {
			echo '<input name="',$name,'" value="',$value,'" />';
		}
		echo '</td>
	</tr>';
	}
}
?>
	<tr class="rowH">
		<th style="text-align: center;" colspan="2">
			Amount
		</th>
	</tr>
	<tr class="rowB">
		<td width="25%"><strong>Amount</strong></td>
		<td><input type="text" name="amount"></td>
	</tr>
	
	<tr class="rowH">
		<th style="text-align: center;" colspan="2">
			Save details ...
		</th>
	</tr>
	<tr class="rowB">
		<td colspan="2" style="text-align: center;">
			<input type="hidden" value="<?php echo $formfd; ?>" name="fields">
			<input type="submit" value="Transfer &raquo;">
		</td>
	</tr>
	</table>
</center>
</form>
