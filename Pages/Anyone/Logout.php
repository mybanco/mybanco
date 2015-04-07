<?php
//--
//-- logoutSession :)
//--

// Ask MyInfo for all the accounts :)
addRequest('sessions', 'logoutSession');
sendRequest(false);

// And now we delete the cookies :)
setcookie ("user", "", time() - 3600, "/");
setcookie ("id",   "", time() - 3600, "/");

header("Location: /");

exit;
