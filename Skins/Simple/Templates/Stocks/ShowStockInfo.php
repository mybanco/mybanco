<div style="text-align: left; width: 50%; margin-left: 25%;">
	<div style="text-align: center;"><b>Stock Search:</b></div>
	<form action="/stocks/search/" method="POST"><?php
if (isset($message)) {?>
		<div style="text-align: center;">
		<span class="info-big"><?php=$message?></span>
		</div><?php
}?>
		<input name="ticker" style="width: 70%;"<?php if (isset($search)) echo " value='", htmlspecialchars($search), "'";?>>
		<input value="Search &raquo;" type="submit">
		<div style="text-align: left; color: #6F6F6F;">e.g. "CSCO" or "Google"</div>
	</form>
</div>
<hr />


<div class="tophdg">
	<h1><?php=$compayName?></h1>
	(Symbol: <?php=$ticker?>)
</div>


<div id="market_data_n_chart_div" style="width: 100%">
<!-- MARKET DATA AND CHART -->
<table cellspacing="0" cellpadding="0" border="0" width="100%" valign="top">
<!-- MARKET DATA -->
<tbody>
	<tr>
		<td width="100%" colspan="2">
		<div style="padding: 5px 0pt 0pt;" id="market_data_div">
			<table cellspacing="0" cellpadding="1" border="0" width="100%" id="md">
			<tbody><tr>
				<td width="1%" valign="bottom" rowspan="4">
					<span id="ref_694653_l" class="pr">300.36</span><br/>
					<span class="bld"><span id="ref_694653_c" class="chr">-2.59</span></span>
					<span id="ref_694653_cp" class="chr">(-0.85%)</span><br/>
					Dec 26 - Close
				</td>
				<td>&nbsp;</td>
				<td width="1%" class="key">Open:</td>
				<td width="1%" class="val"><span id="ref_694653_op">304.07</span></td>
				<td>&nbsp;</td>
				<td width="1%" class="key">Mkt Cap:</td>
				<td width="1%" class="val"><span id="ref_694653_mc">94.54B</span></td>
				<td> </td>
				<td width="1%" class="key">Dividend:</td>
				<td width="1%" class="val"><span id="ref_694653_latest_div">    -</span></td>
			</tr>
			
			<tr>
				<td>&nbsp;</td>
				<td class="key">High:</td>
				<td class="val"><span id="ref_694653_hi">305.26</span></td>
				<td>&nbsp;</td>
				<td class="key">52Wk High:</td>
				<td class="val"><span id="ref_694653_hi52">716.00</span></td>
				<td>&nbsp;</td>
				<td width="1%" class="key">Yield:</td>
				<td width="1%" class="val"><span id="ref_694653_div_yield">    -</span></td>
			</tr>
			
			<tr>
				<td>&nbsp;</td>
				<td class="key">Low:</td>
				<td class="val"><span id="ref_694653_lo">298.31</span></td>
				<td>&nbsp;</td>
				<td class="key">52Wk Low:</td>
				<td class="val"><span id="ref_694653_lo52">247.30</span></td>
				<td>&nbsp;</td>
				<td width="1%" class="key">Shares:</td>
				<td width="1%" class="val"><span id="ref_694653_shares">314.75M</span></td>
			</tr>
			
			<tr>
				<td> </td>
				<td class="key">Vol:</td>
				<td class="val"><span id="ref_694653_vo">1.96M</span></td>
				<td> </td>
				<td class="key">Avg Vol:</td>
				<td class="val">6.91M</td>
				<td> </td>
				<td width="1%" class="key">Inst. Own:</td>
				<td width="1%" class="val"><span id="ref_694653_inst_own">60%</span></td>
			</tr>
			<tr>
				<td colspan="13">
				<div>
					<div style="float: left; padding-top: 5px;">
					<span style="color: black; white-space: normal;">
					<nobr><span class="bld">After Hours: <span id="ref_694653_el">300.36</span>
					<span class="chg" id="ref_694653_ec">0.00</span>
					</span>
					<span class="chg" id="ref_694653_ecp">(0.00%)</span> -
					<span id="ref_694653_elt" style="white-space: normal;">Dec 26, 6:51PM EST</span>
					</nobr></span>
					</div>
					
					<div style="float: right; padding-top: 5px;" class="rgt">
					<span class="dis-large"><nobr><strong>Real-time data</strong></nobr></span>
					</div><div class="clr"/></div>
				</div>
				</td>
			</tr>
			</tbody>
			</table>
		</div>
		</td>
	</tr>
<!-- CHART -->
</tbody>
</table><!-- End MARKET DATA and CHART -->
</div>

<div id="summary">
	<div class="hdg"><h3>Summary</h3></div>
	<div class="content">
		<div class="item companySummary">
		<?php=$companyDescription?>
		</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" style="line-height: 1.3em;" class="item">
		<tbody><tr>
			<td width="40%" valign="top">
			Address/Phone etc
			</td>
			
			<td width="60%" valign="top" style="padding-left: 1em;">
				Company website:<br/>
				
			</td>
		</tr>
		</tbody></table>
	</div>
</div>

