<?php
	//Check - user loged in
	if (!isset($BW->client['UserInfo']))
		throw new BW_ClientError(401,'Not authed.');
	
	//Check data
	$nickname = $POST['nickname'] ?? '?';
	$email = $POST['email'] ?? '?';
	
	if (!checkRegex('Nickname',$nickname))
		throw new BW_ClientError(400,'Nickname bad format.');
	
	if (!checkRegex('Email',$email))
		throw new BW_ClientError(400,'Email bad format.');
	
	//Update database
	writeLog('Updating user info.');
	$username = $BW->client['UserInfo']['Username'];
	$this->database->call(
		'User_modify',
		array(
			'Username'	=> $username,
			'Nickname'	=> $nickname,
			'Group'		=> null,
			'Password'	=> null,
			'Email'		=> $email,
			'Data'		=> null,
			'Photo'		=> null
		)
	);
		
	http_response_code(201);
	$API = array('Status'=>'OK');
?>
