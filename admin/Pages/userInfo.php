<?php
//---
//---        [ MyBanco AJAX User Info ]
//---
//---  AJAX user information
global $pathInfo;
$u = addRequest('admin', 'userInfo', Array('user' => $pathInfo[2]));
$out = sendRequest();
checkSession2($out);

$total = 0;

echo '# of accounts ' . count($out[$u]['accounts']);
if (is_array($out[$u]['accounts']))
foreach ($out[$u]['accounts'] as $x) {
	$total = $total + $x['amount'];
}
echo '<br />TOTAL: $' . $total;
