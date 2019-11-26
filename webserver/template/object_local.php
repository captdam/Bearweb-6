<?php
	$file = './object/'.$PAGEDATA['Binary'];
	writeLog('Reading file: '.$file);
	
	if (!file_exists($file))
		throw new BW_WebServerError(500,'Object missed.');
	
	readfile($file);
?>