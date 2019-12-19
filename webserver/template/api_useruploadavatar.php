<?php
	//Check - user loged in
	if (!isset($BW->client['UserInfo']))
		throw new BW_ClientError(401,'Not authed.');
	
	//Check data
	$avatarString = $POST['avatar'] ?? '?'; #Cannot be convert if not defined or bad format
	if (!base64_decode($avatarString))
		throw new BW_ClientError(400,'Bad data.');
	
	//Process image
	writeLog('Reading image string.');
	try {
		$avatar = new ImageProcess($avatarString);
	} catch(Exception $e) {
		throw new BW_ClientError(400,'Bad image.');
	}
	
	writeLog('Processing image.');
	try {
		$avatar->resize(200,200);
		$avatar = $avatar->render(65);
	} catch(Exception $e) {
		throw new BW_ServerError(500,'Cannot convert image: '.$e->getMessage());
	}
	
	//Update database
	writeLog('Updating user avatar.');
	$username = $BW->client['UserInfo']['Username'];
	$this->database->call(
		'User_modify',
		array(
			'Username'	=> $username,
			'Nickname'	=> null,
			'Group'		=> null,
			'Password'	=> null,
			'Email'		=> null,
			'Data'		=> null,
			'Photo'		=> $avatar
		)
	);
		
	http_response_code(201);
	$API = array('Status'=>'OK');
?>
