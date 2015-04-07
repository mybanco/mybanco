<h3>Choose a transfer method</h3>

<?php echo htmlspecialchars($CONFIG['bank']); ?> prides itself on giving it's customers a wide range of transfer methods!
Please choose how you wish to transfer money by chooseing one of the options below.

<br /><br />

<center>
	<table width="50%" style="width: 50%;">
	<tr class="rowH">
		<th colspan="2">Select a transfer method</th>
	</tr>
	
<?php

	$letter = 'A';
	foreach ($methods['methods'] as $name=>$method ) {
		$letter = ($letter == "A" ? "B" : "A");
		echo "\t", '<tr class="row', $letter ,'">',"\n\t\t",
		     '<td width="48"><img src="/i/48/', $method['icon'], '.png" /></td>',
		     "\n\t\t<td>",
		     "\n\t\t\t", '<a href="/transfer/account/', $account, '/', $name, '">', $method['title'], '</a><br />', 
		     "\n\t\t\t", str_replace("{bank}", $CONFIG['bank'], $method['description']),
		     "\n\t\t</td>",
		     "\n\t</tr>\n\n";
	}
	
	
	?>
	
	</table>
</center>

