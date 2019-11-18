<?php
	//MIME
	header('Content-Type: text/html');
	
	//Get all pages with the given sitename and URL
	writeLog('Fetch page index.');
	$pageIndex = $this->database->call(
		'Webpage_getLanguageIndex',
		array(
			'Site'	=> SITENAME,
			'URL'	=> $BW->URL,
		),
	true);
	if (!$pageIndex)
		throw new BW_ClientError(404,'Page not found.');
	
	foreach ($pageIndex as &$x)
		$x = $x['Language'];
	
	//Multilingual
	writeLog('Page index size (multilingual): '.count($pageIndex));
	$userLanguage = $BW->location['language'];
	$userRegion = $BW->location['region'];
	$userLocation = trim($userLanguage.'-'.$userRegion,'-');
	
	$determineMultilingual = function($userLanguage,$pageIndex) {
		//100% matched
		if (in_array($userLanguage,$pageIndex))
			return $userLanguage;
		
		//Partial matched
		foreach($pageIndex as &$x) #$pageIndex is copied into the function, and modified by reference here
			$x = substr($x,0,2);
		if (in_array( substr($userLanguage,0,2),$pageIndex ))
			return substr($userLanguage,0,2);
		
		return null;
	};
	
	$language = $determineMultilingual($userLanguage,$pageIndex); #Determine language by user language
	
	if (!$language) #Fallback: by default language
		$language = $determineMultilingual(DEFAULT_LANGUAGE[SITENAME],$pageIndex);
	
	if (!$language) #Fall back, use any page
		$language = '';
	
	//Fetch webpage data
	writeLog('Fetch page: ('.SITENAME.') - ('.$language.') - '.$BW->URL);
	$_webpage_ = $this->database->call(
		'Webpage_get',
		array(
			'Site'		=> SITENAME,
			'URL'		=> $BW->URL,
			'Language'	=> $language.'%'
		),
	true);
	
	$_webpage_ = array_merge($_webpage_[0],$BW->page);
	
	/*
	Multilingual in template:
	To support multilingual in template, write HTML code in this way:
	<?php if ($BW->location['language'] == 'option-language1'): ?>
		<span>Content in option language 1</span>
	<?php else if ($BW->location['language'] == 'option-language2'): ?>
		<span>Content in option language 2</span>
	<?php else: ?>
		<span>Content in default language</span>
	<?php endif; ?>
	Do not forget $BW->site['DefaultLanguage'] in stored BW_Config.
	*/
?>
<!DOCTYPE html>
<html
	data-pagestatus="<?= $_webpage_['Status']; ?>"
	data-httpstatus="<?= http_response_code(); ?>"
>
	<head>
		<title><?= $_webpage_['Title']; ?> - Das SAM Club</title>
		<meta name="keywords" content="<?= $_webpage_['Keywords'] ?>" />
		<meta name="description" content="<?= $_webpage_['Description'] ?>" />
		<meta name="author" content="<?= $_webpage_['Author'] ?>" />
		<meta name="robots" content="<?= $_webpage_['Status'] == 'S' ? 'noindex' : 'index'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta charset="utf-8" />
		<link href="https://beardle.com/web/favorite.png" rel="icon" type="image/png" />
		<link href="https://beardle.com/web/style.css" rel="stylesheet" type="text/css" />
		
		
	<!--link rel="alternate" hreflang="lang_code" href="url_of_page" /-->	
		
		
		<script src="https://beardle.com/web/ajax.js"></script>
		<script src="https://beardle.com/web/md5.js"></script>
		<script src="https://beardle.com/web/util.js"></script>
		<script src="https://beardle.com/web/user.js"></script>
		<script src="https://beardle.com/web/ini.js"></script>
	</head>
	<body>
		<header>
			<h1 id="header_logo">Beardle</h1>
			<span id="phone_menu_button">≡</span>
			<div id="search_container">
				<input id="search" />
			</div>
			<nav id="header_nav">
<?php if ($BW->location['language'] == 'en'): ?>
				<a href="/">Homepage</a>
				<a href="/about">About</a>
				<a href="/activity">Activity</a>
				<a href="/project">Project</a>
				<a href="/resource">Resource</a>
				<a href="http://mc.beardle.com:8123">Bearcraft</a>
				<a href="/user" id="header_nav_user">Login</a>
<?php else: ?>
				<a href="/">主页</a>
				<a href="/about">关于</a>
				<a href="/activity">动态</a>
				<a href="/project">作品</a>
				<a href="/resource">资源</a>
				<a href="http://mc.beardle.com:8123">Bearcraft</a>
				<a href="/user" id="header_nav_user">登录</a>
<?php endif; ?>
			</nav>
		</header>
		<img id="banner" alt="Banner image" src="/<?= $_webpage_['Info']['Poster'] ?? 'web/banner.jpg' ?>" />
		<div id="side">
			<img src="https://beardle.com/web/top.png" alt="Top of page" title="To page top" />
		</div>
		<main>
			<div id="main_title">
				<h1><?= $_webpage_['Title']; ?></h1>
			</div>
			<div id="main_content">
				<?php $BW->useTemplate(); ?>
			</div>
		</main>
		
		<footer>
			<div class="pltr">
				<img src="https://beardle.com/web/logo.png" />
				<div>
<?php $cprAuthorField = $_webpage_['Author'] ? '<span class="bearweb_author">'.$_webpage_['Author'].'</span>' : ''; ?>
<?php if ($BW->location['language'] == 'en'): ?>
					<p>Das SAM Club (Das Science And Military Club)</p>
					<p>Admin e-mail: <a href="mailto:admin@beardle.com">admin@beardle.com</a></p>
					<p>© <?php
						if (!$_webpage_['Copyright'])
							echo trim( $cprAuthorField.' - All rights reserved' ," \-");
						else if (substr($_webpage_['Copyright'],0,2) == 'R=')
							echo trim( substr($_webpage_['Copyright'],2).' - Uploaded by: '.$cprAuthorField ," \-");
						else
							echo trim( $cprAuthorField.' ★ This work is licensed under '.$_webpage_['Copyright'] ," \-");
					?></p>
<?php else: ?>
					<p>Das SAM Club （熊社，物理社与军事社）</p>
					<p>管理员邮箱： <a href="mailto:admin@beardle.com">admin@beardle.com</a></p>
					<p>© <?php
						if (!$_webpage_['Copyright'])
							echo trim( $cprAuthorField.' - 保留一切权利' ," \-");
						else if (substr($_webpage_['Copyright'],0,2) == 'R=')
							echo trim( substr($_webpage_['Copyright'],2).' - 由'.$cprAuthorField.'上传' ," \-");
						else
							echo trim( $cprAuthorField.' ★ 按照'.$_webpage_['Copyright'].'协议进行共享' ," \-");
					?></p>
<?php endif; ?>
				</div>
			</div>
		</footer>
	</body>
	
</html>
