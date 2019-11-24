<?php
	//Fetch object data
	$object = $this->database->call(
		'Object_get',
		array(
			'Site'		=> $BW->page['Site'],
			'URL'		=> $BW->URL
		),
	true);
	if (!$object)
		throw new BW_ClientError(404,'Object not found.');
	
	//This contains object data from BW_Sitemap and BW_Object
	$PAGEDATA = array_merge($object[0],$BW->page);
	
	header('Content-Type: '.$PAGEDATA['MIME']);
	writeLog('Pass control to sub template.');
	include $templateSub;
?>
