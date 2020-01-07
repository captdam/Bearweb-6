<?php
	//Check
	$username = $_GET['username'] ?? ''; #If undefined, give '', will fail the regexp check
	if ( !checkRegex('Username',$username)  )
		throw new BW_ClientError(400,'Username undefined or bad format.');
	
	//Get user info
	$user = $BW->database->call('User_get',array('Username' => $username),true);
	if (!$user)
		throw new BW_ClientError(404,'No such user.');
	$user = $user[0];
	
	if ($user['Photo'])
		echo $user['Photo'];
	else {
		header('Content-Type: image/png');
		imagepng(imagecreatefromstring(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAGQAAABkAQMAAABKLAcXAAAABlBMVEX///8AAABVwtN+AAABJklEQVQ4y+XUPU4FIRAAYAgFJYWNlZzDxIhH8QgvsVkTf7B6jXfwKhwFs4WlJK94FGRHdvjZIWpiZ4wUzH4b/mdZxv57EeCIDIDd9AgQROwNZ0hqqZKJaTBQnp322Bi76mByVEniuCY9rH2DQEFKueZe+DVEHcrIqHDmUXwNoi0KJZuwpbQ/kaOSg9Qgbel8hhFx3C1PVWtUsJW/qrz3u7IlzK20keopUO3LMfFQxIj6KY3CLHJnPNEMsaeI8bdRdtr6N8kvxOZBwhWVBZ64SHT+GgbhTKpqRumy3OmAby+rjoNuUPWscLYrdkq0axdlus5V6Nq1Q0GFT/JU4jsl8uGz26VvIJd7qF9quabvz2LZrvD+BVLXRc59nzwvXg7XncVf+ut8ACROJcEPe9WeAAAAAElFTkSuQmCC')));
		/*
		The server will compile a small PNG file on-the-fly.
		Redirect to a compiled image in another URL lead to a huge overhead comes from:
		1. The user open and load a new resource from the server.
		2. The server construct a new BW instance, tons of DBMS access and log writing.
		*/
	}
?>
