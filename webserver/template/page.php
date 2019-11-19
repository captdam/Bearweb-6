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
	
	//Multilingual page content
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
	
	$language = $determineMultilingual($userLanguage,$pageIndex); #Determine language by user language (in URL)
	
	if (!$language) #Fallback: by default language (in config.php)
		$language = $determineMultilingual(DEFAULT_LANGUAGE[SITENAME],$pageIndex);
	
	if (!$language) #Fall back, use any page
		$language = '';
	
	//Fetch webpage data
	writeLog('Fetch page: ('.SITENAME.') - ('.$language.') - '.$BW->URL);
	$webpage = $this->database->call(
		'Webpage_get',
		array(
			'Site'		=> SITENAME,
			'URL'		=> $BW->URL,
			'Language'	=> $language.'%'
		),
	true);

/********************************* TO WRITE THE TEMPLATE *********************************/

	//This CONSTANT contains page data from BW_Sitemap and BW_Webpage
	define('PAGEDATA',array_merge($webpage[0],$BW->page));
	
	//Prefix user's multilingual info to links on the page
	define('USERLANGUAGE', $userLocation=='' ? '' : ('/'.$userLocation) );
	
	//Array of all available language for this page (Get `Language` from `BW_Webpage` by `URL`)
	define('LANGUAGESET',$pageIndex);
	
	/*
	Multilingual in template:
	To support multilingual in template, write HTML code in this way:
	<?php if (USERLANGUAGE == 'option-language1'): ?> #Language + Region
		<span>Content in option language 1</span>
	<?php elseif (substr(USERLANGUAGE,1,2) == 'option-language2'): ?> #Language
		<span>Content in option language 2</span>
	<?php else: ?>
		<span>Content in default language</span>
	<?php endif; ?>
	Do not forget $BW->site['DefaultLanguage'] in stored BW_Config.
	*/

/*****************************************************************************************/
?>
<!DOCTYPE html>
<html
	data-pagestatus="<?= PAGEDATA['Status']; ?>"
	data-httpstatus="<?= http_response_code(); ?>"
>
	<head>
		<title><?= PAGEDATA['Title']; ?> - Das SAM Club</title>
		<meta name="keywords" content="<?= PAGEDATA['Keywords'] ?>" />
		<meta name="description" content="<?= PAGEDATA['Description'] ?>" />
		<meta name="author" content="<?= PAGEDATA['Author'] ?>" />
		<meta name="robots" content="<?= PAGEDATA['Status'] == 'S' ? 'noindex' : 'index'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta charset="utf-8" />
		<link href="https://beardle.com/web/favorite.png" rel="icon" type="image/png" />
		<link href="https://beardle.com/web/style.css" rel="stylesheet" type="text/css" />
<?php
	//If "S", SEO no index, do not provide multilingual info
	if (PAGEDATA['Status'] != 'S') foreach (LANGUAGESET as $x)
		echo '<link rel="alternate" hreflang="',$x,'" href="/',$x,'/',PAGEDATA['URL'],'" />';
?>
		<script src="https://beardle.com/web/ajax.js"></script>
		<script src="https://beardle.com/web/md5.js"></script>
		<script src="https://beardle.com/web/util.js"></script>
		<!--script src="https://beardle.com/web/user.js"></script-->
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
<?php if (substr(USERLANGUAGE,1,2) == 'en'): ?>
				<a href="<?= USERLANGUAGE ?>/">Homepage</a>
				<a href="<?= USERLANGUAGE ?>/about">About</a>
				<a href="<?= USERLANGUAGE ?>/activity">Activity</a>
				<a href="<?= USERLANGUAGE ?>/project">Project</a>
				<a href="<?= USERLANGUAGE ?>/resource">Resource</a>
				<a href="http://mc.beardle.com:8123">Bearcraft</a>
				<a href="<?= USERLANGUAGE ?>/user" id="header_nav_user">Login</a>
<?php else: ?>
				<a href="<?= USERLANGUAGE ?>/">主页</a>
				<a href="<?= USERLANGUAGE ?>/about">关于</a>
				<a href="<?= USERLANGUAGE ?>/activity">动态</a>
				<a href="<?= USERLANGUAGE ?>/project">作品</a>
				<a href="<?= USERLANGUAGE ?>/resource">资源</a>
				<a href="http://mc.beardle.com:8123">Bearcraft</a>
				<a href="<?= USERLANGUAGE ?>/user" id="header_nav_user">登录</a>
<?php endif; ?>
			</nav>
		</header>
		<img id="banner" alt="Banner image" src="/<?= PAGEDATA['Info']['Poster'] ?? 'web/banner.jpg' ?>" />
		<div id="side">
			<img src="https://beardle.com/web/top.png" alt="Top of page" title="To page top" />
		</div>
		<main>
			<div id="main_title">
				<h1><?= PAGEDATA['Title']; ?></h1>
			</div>
			<div id="main_content">
				<?php $BW->useTemplate(); ?>
			</div>
		</main>
		
		<footer>
			<div class="pltr">
				<img src="https://beardle.com/web/logo.png" />
				<div>
<?php $cprAuthorField = PAGEDATA['Author'] ? '<span class="bearweb_author">'.PAGEDATA['Author'].'</span>' : ''; ?>
<?php if (substr(USERLANGUAGE,1,2) == 'en'): ?>
					<p>Das SAM Club (Das Science And Military Club)</p>
					<p>Admin e-mail: <a href="mailto:admin@beardle.com">admin@beardle.com</a></p>
					<p>© <?php
						if (!PAGEDATA['Copyright'])
							echo trim( $cprAuthorField.' - All rights reserved' ," \-");
						else if (substr(PAGEDATA['Copyright'],0,2) == 'R=')
							echo trim( substr(PAGEDATA['Copyright'],2).' - Uploaded by: '.$cprAuthorField ," \-");
						else
							echo trim( $cprAuthorField.' ★ This work is licensed under '.PAGEDATA['Copyright'] ," \-");
					?></p>
<?php else: ?>
					<p>Das SAM Club （熊社，物理社与军事社）</p>
					<p>管理员邮箱： <a href="mailto:admin@beardle.com">admin@beardle.com</a></p>
					<p>© <?php
						if (!PAGEDATA['Copyright'])
							echo trim( $cprAuthorField.' - 保留一切权利' ," \-");
						else if (substr(PAGEDATA['Copyright'],0,2) == 'R=')
							echo trim( substr(PAGEDATA['Copyright'],2).' - 由'.$cprAuthorField.'上传' ," \-");
						else
							echo trim( $cprAuthorField.' ★ 按照'.PAGEDATA['Copyright'].'协议进行共享' ," \-");
					?></p>
<?php endif; ?>
				</div>
			</div>
		</footer>
	</body>
</html>
