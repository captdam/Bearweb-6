<?php
	try {
		$file = './object/'.$object['Binary'];
		$BW->log('Printing content of file: '.$file);
		readfile($file);
	} catch (Exception $e) {
		throw new BW_WebServerError(500, 'Fail to output object file: '.$e->getMessage());
	}
?>