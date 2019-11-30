<?php
	//Check
	$username = $POST['username'] ?? ''; #If undefined, give '', will fail the regexp check
	$nickname = $POST['nickname'] ?? '';
	$password = $POST['password'] ?? '';
	
	if ( !checkRegex('Username',$username) || !checkRegex('MD5',$password) || !checkRegex('Nickname',$nickname) )
		throw new BW_ClientError(400,'Username, nickname and/or password undefined or bad format.');
	
	//Register
	writeLog('New user: Nickname '.$nickname.' (Username '.$username.'). IP: '.$BW->client['SessionInfo']['IP']);
	try {
		$user = $BW->database->call(
			'User_new',
			array(
				'Username'	=> $username,
				'Nickname'	=> $nickname,
				'Password'	=> $password,
				'IP'		=> $BW->client['SessionInfo']['IP']
			)
		);
	} catch(Exception $e) {
		if (strpos($e->getMessage(),'Duplicate'))
			throw new BW_ClientError(409,'Username has been used.');
		else
			throw $e;
	}
	
	http_response_code(201);
	$API = array('Status'=>'OK');
?>
