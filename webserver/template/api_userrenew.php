<?php
	http_response_code(201);
	$API = array('Status'=>'OK');
	
	/*
	Session has been renewed in the framework, no action required here
	A new cookie has been send to client-side, hints the next renew time
	
	Client-side should use HEAD method, because no useful content is returned
	The framework will return a JSON indecates the API is success, this can be ignored
	*/
?>
