<?php
	header('Content-Type: application/json');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	
	$POST = $_POST;
	$GET = $_GET;
	$API = array();
	
	try {
		$BW->database->begin();
		include $templateSub;
		$BW->database->commit();
		echo json_encode($API);
	
	} catch(Exception $e) {
		$BW->database->cancel();
		throw $e;
	}
	
	/*
	If success:
	- Database changes will be commit
	- Return data in $API as JSON and HTTP status code to the front-end API caller
	If fail: (Any exception, including BW_ClientError)
	- Database changes will be discard
	- Pass handler backto index.php, which will call error template writen in HTML
	- Front-end API caller will receive the HTTP status code and HTML file including the error detail
	For front-side API caller:
	Do NOT use HTTP code to determine API success/fail:
	eg. API_isThisFileOnServer returns 404 if the file is missed, but the API do successed
	Since HTML file will be returned if API fail and JSON will be returned if API success
	The front-end APi caller should check the format of the response to determine API success/fail
	*/
?>
