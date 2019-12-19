<?php
	//Check - user loged in
	if (!isset($BW->client['UserInfo']))
		throw new BW_ClientError(401,'Not authed.');
	
	//Check data
	$passwordOld = $POST['passwordOld'] ?? '?';
	$passwordNew = $POST['passwordNew'] ?? '?';
	
	if ($passwordOld != $BW->client['UserInfo']['Password'])
		throw new BW_ClientError(400,'Old password is wrong.');
	
	if (!checkRegex('MD5',$passwordNew))
		throw new BW_ClientError(400,'Password bad format.');
	
	//Update database
	writeLog('Updating user password.');
	$username = $BW->client['UserInfo']['Username'];
	$this->database->call(
		'User_modify',
		array(
			'Username'	=> $username,
			'Nickname'	=> null,
			'Group'		=> null,
			'Password'	=> $passwordNew,
			'Email'		=> null,
			'Data'		=> null,
			'Photo'		=> null
		)
	);
		
	http_response_code(201);
	$API = array('Status'=>'OK');
?>
