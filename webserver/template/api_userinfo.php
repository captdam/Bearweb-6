<?php
	//Check
	$username = $GET['username'] ?? ''; #If undefined, give '', will fail the regexp check
	if ( !checkRegex('Username',$username)  )
		throw new BW_ClientError(400,'Username undefined or bad format.');
	
	//Get user info
	$user = $BW->database->call('User_get',array('Username' => $username),true);
	if (!$user)
		throw new BW_ClientError(404,'No such user.');
	$user = $user[0];
	
	http_response_code(200);
	$API = array(
		'Status' => 'OK',
		'Info'=> array(
			 'Username'		=> $user['Username']
			,'Nickname'		=> $user['Nickname']
			,'Group'		=> $user['Group']
			,'LastActiveTime'	=> $user['LastActiveTime']
//			,'Email'		=> $user['Email']
		)
	);
?>
