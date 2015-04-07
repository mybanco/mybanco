<?php
//---
//---        [ MyBanco Administration Welcome ]
//---
//---  This page allows admins to configure thier MyBanco system \o/
addRequest('admin', 'userList', Array());
$out = sendRequest();
checkSession2($out);

template_Header('Administration: Welcome');
echo '

<h2>User list</h2>



<style>
.moreinfo {
	background:#EAEAEA url(/i/64/user-properties.png) no-repeat scroll 2px 1px;
	bottom:0pt;
	color:#000000;
	height:68px;
	left:0pt;
	padding-left:68px;
	position:fixed !important;
	width:100%;
	z-index:5;
}
.moreinfo h3 {
	border-bottom:1px dashed #000000;
	color:#5E0909;
	font-size:14px;
	margin-bottom:4px;
	margin-top:2px;
}
</style>
<script src="/Skins/Simple/ajax.js"></script>
<script>
function getMoreInfo (uid) {
	document.getElementById("info").style.visibility="visible";
	document.getElementById("ajaxbar").style.visibility="visible";
	document.getElementById("info").style.height="128";
	document.getElementById("ajaxbar").style.height="128";
	ajaxpage("/admin/users/-info/"+uid+"/", \'ajaxbar\');
	return false;
}

</script>

<div id="info" class="moreinfo" onclick="this.style.visibility=\'hidden\'">
	<div id="ajaxbar" onclick="this.style.visibility=\'hidden\'">
		<h3>User detail bar</h3>
		<strong>How to use:</strong> Click on the user in the table, and see more details, such as back total $ in accounts.
		<div style="text-align: center; font-weight: 600;">
			<i>Click anywhere on this grey bar to hide!</i>
		</div>
	</div>
</div>

<table>
<tr class="rowH"><th>username</th><th># accounts</th></tr>
';

$y='A';
foreach ($out['packet:3']['users'] as $x) {
	$y = ($y=="A") ? "B" : "A";
	echo '<tr class="row',$y,'" onclick="getMoreInfo(\'', $x['username'] ,'\');"><td>',$x['username'],'</th><th>', $x['accounts'] ,'</th></tr>';
}

echo '
</table>

';


template_footer();
