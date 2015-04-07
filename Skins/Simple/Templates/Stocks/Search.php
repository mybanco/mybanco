<div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
	<h1><img src="/Images/Stocks.png" alt="Stock Search"></h1>
	<br />
	
		<form action="/stocks/search/" method="POST"><?php
if (isset($message)) {?>
		<div style="text-align: center;">
		<span class="info-big"><?php=$message?></span>
		</div><?php
}?>
		<input name="ticker" style="width: 40%;"<?php if (isset($search)) echo " value='", htmlspecialchars($search), "'";?>>
		<input value="Search &raquo;" type="submit">
	
	<br />
</div>
