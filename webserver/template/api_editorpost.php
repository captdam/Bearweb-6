<?php
	//Check - user loged in
	if (!isset($BW->client['UserInfo']))
		throw new BW_ClientError(401,'Not authed.');
	
	//Check - URL format
	$url = $GET['site'] ?? '*';
	if ( !checkRegex('URL',$url) )
		throw new BW_ClientError(400,'Request URL contains invalid character.');
	
	//Get webpage config
	writeLog('Read page template config file.');
	$filename = './template/'.SITENAME.'_page.json';
	$pageConfig = json_decode(file_get_contents($filename),true);
	
	//Get user params
	$category = $POST['category']	?? NULL; #Cannot be null, whitelist-ed
	$template = $POST['template']	?? NULL;
	if (!$category || !$template)
		throw new BW_ClientError(400,'Bad category or template.');
	if (!in_array($category, explode(',',$pageConfig['Category']) ))
		throw new BW_ClientError(400,'Category not allowed.');
	if (!in_array($template, ['page_content','object_externalimage'] ))
		throw new BW_ClientError(400,'Template not supported.');
	$templateMS = explode('_',$template);
	
	$copyright = $POST['copyright']	?? null; #Missing or client send '' means null (protected, do not copy)
	if ($copyright == '')
		$copyright = null;
	
	$status = $POST['status']	?? 'P';  #Default = pendding
	$info = $POST['info']		?? '{}'; #No info (JSON string)
	
	$main = $POST['main']		?? '[]'; #Empty array (JSON string)
	$main = json_decode($main,true);
	
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
	
	//Create new
	if (!$sitemap) {
		writeLog('URL not exist, about to create new.');
		$BW->database->call(
			'Sitemap_create',
			array(
				'Site'		=> SITENAME,
				'URL'		=> $url,
				'Category'	=> $category,
				'TemplateMain'	=> $templateMS[0],
				'TemplateSub'	=> $templateMS[1],
				'Author'	=> $BW->client['UserInfo']['Username'],
				'Copyright'	=> $copyright,
				'Status'	=> $status,
				'Info'		=> $info
			)
		);
		
		if ($template == 'object_externalimage') {
			writeLog('Sitemap created, about to create new object.');
			/*$BW->database->call(
				'Sitemap_create',
				array(
					'Site'		=> SITENAME,
					'URL'		=> $url,
					'Category'	=> $category,
					'TemplateMain'	=> $templateMS[0],
					'TemplateSub'	=> $templateMS[1],
					'Author'	=> $BW->client['UserInfo']['Username'],
					'Copyright'	=> $copyright,
					'Status'	=> $status,
					'Info'		=> json_encode($info)
				)
			);*/
		}
		else if ($template == 'page_content') {
			$pageCount = count($main);
			writeLog('Sitemap created, about to create new webpage. Total = '.$pageCount);
			for ($i = 0; $i < $pageCount; $i++) {
				writeLog('Webpage '.$i.', lang = '.$main[$i]['language']);
				$BW->database->call(
					'Webpage_createModify',
					array(
						'Site'		=> SITENAME,
						'URL'		=> $url,
						'Language'	=> $main[$i]['language'],
						'Title'		=> $main[$i]['title'],
						'Keywords'	=> $main[$i]['keywords'],
						'Description'	=> $main[$i]['description'],
						'Content'	=> $main[$i]['content'],
						'Source'	=> $main[$i]['source'],
						'Style'		=> $main[$i]['style']
					)
				);
			}
		}
		
		http_response_code(201);
		$API = array(
			'Status' => 'Created'
		);
	}
	
	//Modify old
	else {
		$sitemap = $sitemap[0];
		writeLog('Sitemap exist, about to modify old.');
		
		//Check - ownership
		if (
			!in_array('Admin',$BW->client['UserInfo']['Group']) &&
			$client['UserInfo']['Username'] != $page['Author']
		) {
			throw new BW_ClientError(403,'Access denied: you are not the owner.');
		}
		
		//Modify
		$BW->database->call(
			'Sitemap_modify',
			array(
				'Site'		=> SITENAME,
				'URL'		=> $url,
				'Category'	=> $category,
				'TemplateMain'	=> $templateMS[0],
				'TemplateSub'	=> $templateMS[1],
				'Author'	=> NULL,
				'Copyright'	=> $copyright,
				'Status'	=> $status,
				'Info'		=> $info
			)
		);
		
		if ($template == 'object_externalimage') {
			writeLog('Sitemap modified, about to create new object.');
			/*$BW->database->call(
				'Sitemap_create',
				array(
					'Site'		=> SITENAME,
					'URL'		=> $url,
					'Category'	=> $category,
					'TemplateMain'	=> $templateMS[0],
					'TemplateSub'	=> $templateMS[1],
					'Author'	=> $BW->client['UserInfo']['Username'],
					'Copyright'	=> $copyright,
					'Status'	=> $status,
					'Info'		=> json_encode($info)
				)
			);*/
		}
		else if ($template == 'page_content') {
			$pageCount = count($main);
			writeLog('Sitemap created, about to create new webpage. Total = '.$pageCount);
			for ($i = 0; $i < $pageCount; $i++) {
				writeLog('Webpage '.$i.', lang = '.$main[$i]['language']);
				$BW->database->call(
					'Webpage_createModify',
					array(
						'Site'		=> SITENAME,
						'URL'		=> $url,
						'Language'	=> $main[$i]['language'],
						'Title'		=> $main[$i]['title'],
						'Keywords'	=> $main[$i]['keywords'],
						'Description'	=> $main[$i]['description'],
						'Content'	=> $main[$i]['content'],
						'Source'	=> $main[$i]['source'],
						'Style'		=> $main[$i]['style']
					)
				);
			}
		}
		
		
		http_response_code(202);
		$API = array(
			'Status' => 'Modified'
		);
	}
	
?>
