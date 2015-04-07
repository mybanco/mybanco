<?php
// Make sure wacko servers don't have slashes in the inputted data
//   (thanks dr-spangle for helping to hunt this one down!)
if (get_magic_quotes_gpc()) {
	$_POST = array_map('stripslashes',$_POST);
	$_GET  = array_map('stripslashes',$_GET);
}

if ($_POST['ini']) {
	drawFunkyEditINIThing_top();
	echo '<font color="red">Output</font><br />';
	echo '<pre>';
	
	$content = _getURLContents($_POST['ini']);
	echo '<textarea style="width: 80%; height: 225px;">',$content,'</textarea><br /><br />';
	print_r(_parse_ini_file($content));
	drawFunkyEditINIThing_bum();
} else {
$_POST['ini'] = ';
; this is the test data :)
;
[CARVER]
packets=1
api=1
appid=s

[packet:1]
plugin=core
action=listActions
data=core';
	drawFunkyEditINIThing_top();
	drawFunkyEditINIThing_bum();
}



function drawFunkyEditINIThing_top() {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<h1>Test data</h1>
<form id="form1" name="form1" method="post" action="">
  <label>Test Data<br />
  <textarea name="ini" id="ini" cols="45" rows="5" style="width: 100%; height: 225px;"><?php echo htmlspecialchars($_POST['ini']); ?></textarea>
  </label>
    <input type="submit" name="submit" id="submit" value="Submit" /><br />
</form>
<?php }
function drawFunkyEditINIThing_bum() {
?>
</body>
</html>
<?php
}



function _parse_ini_file($input, $process_sections = false) {
  $process_sections = ($process_sections !== true) ? false : true;
  
  $ini = split("\n", $input);
  
  $process_sections = true;
  
  //$ini = file($file);
  if (count($ini) == 0) {return array();}

  $sections = array();
  $values = array();
  $result = array();
  $globals = array();
  $i = 0;
  foreach ($ini as $line) {
    $line = trim($line);
    $line = str_replace("\t", " ", $line);

    // Comments
    if (!preg_match('/^[a-zA-Z0-9[]/', $line)) {continue;}

    // Sections
    if ($line{0} == '[') {
      $tmp = explode(']', $line);
      $sections[] = trim(substr($tmp[0], 1));
      $i++;
      continue;
    }

    // Key-value pair
    list($key, $value) = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);
    if (strstr($value, ";")) {
      $tmp = explode(';', $value);
      if (count($tmp) == 2) {
        if ((($value{0} != '"') && ($value{0} != "'")) ||
            preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
            preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value) ){
          $value = $tmp[0];
        }
      } else {
        if ($value{0} == '"') {
          $value = preg_replace('/^"(.*)".*/', '$1', $value);
        } elseif ($value{0} == "'") {
          $value = preg_replace("/^'(.*)'.*/", '$1', $value);
        } else {
          $value = $tmp[0];
        }
      }
    }
    $value = trim($value);
    $value = trim($value, "'\"");

    if ($i == 0) {
      if (substr($line, -1, 2) == '[]') {
        $globals[$key][] = $value;
      } else {
        $globals[$key] = $value;
      }
    } else {
      if (substr($line, -1, 2) == '[]') {
        $values[$i-1][$key][] = $value;
      } else {
        $values[$i-1][$key] = $value;
      }
    }
  }

  for($j = 0; $j < $i; $j++) {
    if ($process_sections === true) {
      $result[$sections[$j]] = $values[$j];
    } else {
      $result[] = $values[$j];
    }
  }

  return $result + $globals;
}

//--
//-- _getURLContents
//--   @ (string)$action, (string)$sending_stuff, (bool)$debug
//--
//-- Function for retrieving data from a MyInfo server (internal helper)
function _getURLContents ($xmldata = "", $debug = true) {
	global $SYSTEM, $CONFIG;
	
	$mtime = microtime();
	$mtime = explode(' ',$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
	
	$curl = curl_init('http://127.0.0.1/b/backend/?v=1');
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, "i=" . urlencode($xmldata));
	curl_setopt($curl, CURLOPT_USERAGENT, 'MyCountry v0.05.1');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($curl);
	
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	if ($debug)
		echo "MyInfo took ".$totaltime." seconds to respond";
	
	// TODO: This needs some cleaning :)
	$info = curl_getinfo($curl);
	if (empty($info['http_code'])) {
		template_Error(ERROR_FATAL, "No HTTP code was returned");
	} if ($info['http_code'] <> 200) {
		$info['url'] = '{CENSORED}';
		template_Error(ERROR_FATAL, "MyInfo backend does not funtion!",
			$info);
	}
	
	if (curl_errno($curl)) {
		print "Error: " . curl_error($ch);
		exit;
	} else {
		// Show me the result
		curl_close($curl);
		
		$OUTPUT = Array(
				'data' => $data,
				'debug' => "<pre>" . htmlspecialchars (print_r($data, true)) . "</pre>"
			);
		if ($debug == true) {
			echo "<!--- $data .......... $xmldata --->\n";
		}
		return $data;
	}
}
?>
