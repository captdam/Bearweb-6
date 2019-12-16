<?php
	//Check
	if (isset($BW->client['UserInfo'])) {
		$username = $BW->client['UserInfo']['Username'];
		
		writeLog('User logout, username: '.$username);
		$this->database->call('Session_unbind',array('SessionID'=>$BW->client['SessionInfo']['SessionID']));
		
		http_response_code(201);
		$API = array('Status'=>'OK');
	}
	else {
		throw new BW_ClientError(401,'Not authed.');
	}
?>
