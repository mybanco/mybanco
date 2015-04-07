<?php
//---
//---               [ MyInfo 'core' plugin ]
//---
//---  This is a basic plugin for the MyInfo server to provide a simple
//---  example on how to create MyInfo plugins :)
//---

global $PLUGIN;
$PLUGIN['core']['actions'] = Array('listPlugins', 'listActions', 'MyInfoVersion');

function __core_listPlugins($data) {
	global $CONFIG;
	return $CONFIG['plugins'];
}

function __core_listActions($data) {
	global $CONFIG, $PLUGIN;
	if (in_array($data, $CONFIG['plugins'])) {
		$plugin = $data;
		
		if (!file_exists('./Plugins/'.$plugin.'.php'))
			return array('error'=>403);
		require_once './Plugins/'.$plugin.'.php';
		
		return $PLUGIN[$plugin]['actions'];
	} else {
		// The plugin does not exist, error please!
		return array('error'=>403);
	}
}

function __core_MyInfoVersion($data) {
	global $SYSTEM;
	
	$a = Array('Tim Groeneveld');
	
	return Array(
		'version' => $SYSTEM['version'],
		'database'=>'mysql',
		'authors'=>$a,
		'license'=>'agpl'
	);
}
