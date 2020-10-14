<?php
	try {
		$BW->log('Printing content of file: '.$object['Binary']);
		readfile('./object/'.$object['Binary']);
	} catch (Exception $e) {
		throw new BW_WebServerError(500, 'Fail to output object file: '.$e->getMessage());
	}
?>