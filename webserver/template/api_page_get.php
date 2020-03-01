<?php
	//Check - user loged in
	if (!isset($BW->client['UserInfo']))
		throw new BW_ClientError(401,'Not authed.');
	
	//Check - URL format
	$url = $GET['url'] ? ''
	if ( !checkRegex('URL',$url) )
		throw new BW_ClientError(400,'Request URL contains invalid character.');
	
	//Get BW_Sitemap
	$sitemap = $BW->database->call(
		'Sitemap_get',
		array(
			'Site'		=> SITENAME,
			'URL'		=> $url,
			'Category'	=> null,
			'Status'	=> null
		),
	true);
	if (!$sitemap)
		throw new BW_ClientError(404,'URL resource not found.');
	$sitemap = $sitemap[0];
	$sitemap['Info'] = json_decode($sitemap['Info'],true);
	
	//Check - ownership
	if (
		!in_array('Admin',$client['UserInfo']['Group']) &&
		$client['UserInfo']['Username'] != $page['Author']
	) {
		throw new BW_ClientError(403,'Access denied: you are not the owner.');
	}
	
	//Get BW_Webpage (Nullable)
	$webpage = $BW->database->call(
		'Webpage_get',
		array(
			'Site'		=> SITENAME,
			'URL'		=> $url,
			'Language'	=> null
		),
	true);
	
	//Get BW_Object (Nullable)
	$object = $BW->database->call(
		'Object_get',
		array(
			'Site'		=> SITENAME,
			'URL'		=>$url
		),
	true);
	
	if (!$object)
		$object = null;
	else {
		$object['Binary'] = base64_encode($object['Binary']); #Base64 encode for transfer
		
		//Special object - Remote storage
		if ($sitemap['TemplateSub'] == 'externalimage') {
			$object['HD_Display'] = objectStorageServer
		}
	}
	
	
		
	http_response_code(200);
	$API = array(
		'Status' => 'OK',
		'Sitemap' => $sitemap,
		'Webpage' => $webpage,
		'Object' => $object
	);
?>
