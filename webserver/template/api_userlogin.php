<?php
	//Check
	$username = $POST['username'] ?? ''; #If undefined, give '', will fail the regexp check
	$password = $POST['password'] ?? '';
	
	if ( !checkRegex('Username',$username) || !checkRegex('MD5',$password) )
		throw new BW_ClientError(400,'Username and/or password undefined or bad format.');
	
	//Get user info
	$user = $BW->database->call('User_get',array('Username' => $username),true);
	if (!$user)
		throw new BW_ClientError(404,'No such user.');
	$user = $user[0];
	
	//Verify password
	if ( md5($user['Password'].$BW->client['SessionInfo']['Salt']) != $password)
		throw new BW_ClientError(401,'Wrong Password.');
	
	//Update
	writeLog('User login, username: '.$username);
	$this->database->call('User_active',array('Username'=>$username));
	$this->database->call(
		'Session_bind',
		array(
			'SessionID'	=> $BW->client['SessionInfo']['SessionID'],
			'Username'	=> $user['Username'] #Case sensitive
		)
	);
	
	http_response_code(201);
	$API = array('Status'=>'OK');
