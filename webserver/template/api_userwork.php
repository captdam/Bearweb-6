<?php
	//Check
	$username = $GET['username'] ?? 
		( isset($CLIENT['UserInfo']) ? $CLIENT['UserInfo']['Username'] : '');
	/* If undefined, user user's own username as default value; otherwise, give '' which will fail the regexp check */
	if ( !checkRegex('Username',$username)  )
		throw new BW_ClientError(400,'Username undefined or bad format.');
	
	//Get user info
	$user = $BW->database->call('User_get',array('Username' => $username),true);
	if (!$user)
		throw new BW_ClientError(404,'No such user.');
	$user = $user[0];
	
	//Get user works
	$works = $BW->database->call(
		'User_works',
		array(
			'Username'	=> $CLIENT['UserInfo']['Username'],
			'Site'		=> SITENAME #Only show works belongs to this site
		),
	true);
	/* Must be ORDER BY URL otherwise sorting and grouping will fail */
	
	//Set resource (/category/topic/resource) to be child of topic(/category/topic)
	$topics = array();
	foreach($works as $content) {
		
		//Resource: Parent URL for this content found in topics array
		$assigned = false;
		foreach($topics as &$topic) {
			if ( $content['Site'] == $topic['Site'] && strpos($content['URL'],$topic['URL'].'/') === 0 ) {
				$topic['Child'][] = $content;
				$assigned = true;
				break;
			}
		}
		unset($topic);
		
		//Topic: There is no parent URL for this URL in topics array
		if (!$assigned) {
			$content['Child'] = array();
			$topics[] = $content;
		}
	}
	
	
	//Hide details to people other than author and admin
	if (
		!isset($CLIENT['UserInfo']) || #Visitor or
		(
			!in_array('Admin',$CLIENT['UserInfo']['Group']) && #Not admin/authro
			!in_array($CLIENT['UserInfo']['Username'],$whiteUser)
		)
	) {
		//Show OK pages only
		$topics = array_filter($topics,function($x){
			return $x['Status'] == 'O';
		});
		
		//Remove details
		foreach($topics as &$topic) {
			$topic = array(
				'Site'		=> $topic['Site'],
				'URL'		=> $topic['URL'],
				'Category'	=> $topic['Category'],
				'CreateTime'	=> $topic['CreateTime'],
				'LastModify'	=> $topic['LastModify'],
				'Title'		=> $topic['Title']
			);
		}
		unset($topic);
	}
	
	http_response_code(200);
	$API = array(
		'Status' => 'OK',
		'Work'=> $topics
	);
?>