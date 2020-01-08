<?php
	//MIME
	header('Content-Type: text/html');
	
	//Get config
	writeLog('Read page template config file.');
	$filename = './template/'.SITENAME.'_page.json';
	$pageConfig = json_decode(file_get_contents($filename),true);
	
	//Get all pages with the given sitename and URL
	writeLog('Fetch page index.');
	$pageIndex = $this->database->call(
		'Webpage_getLanguageIndex',
		array(
			'Site'	=> $BW->page['Site'],
			'URL'	=> $BW->page['URL']
		),
	true);
	if (!$pageIndex)
		throw new BW_ClientError(404,'Page not found.');
	
	foreach ($pageIndex as &$x)
		$x = $x['Language'];
	unset($x);
	
	
	//Multilingual page content
	writeLog('Page index size (multilingual): '.count($pageIndex));
	$userLocation = trim($BW->location['language'].'-'.$BW->location['region'],'-');
	$language = chooseLanguage($pageIndex,$userLocation,$pageConfig['DefaultLanguage']);
	
	//Fetch webpage data
	writeLog('Fetch page: ('.$BW->page['Site'].') - ('.$language.') - '.$BW->URL);
	$webpage = $this->database->call(
		'Webpage_get',
		array(
			'Site'		=> $BW->page['Site'],
			'URL'		=> $BW->URL,
			'Language'	=> $language
		),
	true);
	
	

/********************************* TO WRITE THE TEMPLATE *********************************/

	//This contains page data from BW_Sitemap and BW_Webpage
	$PAGEDATA = array_merge($webpage[0],$BW->page);
	
	//Prefix user's multilingual info to links on the page
	$USERLANGUAGE = $userLocation=='' ? '' : ('/'.$userLocation);
	
	//Array of all available language for this page (Get `Language` from `BW_Webpage` by `URL`)
	$LANGUAGESET = $pageIndex;
	
	//Webpage template data in JSON saved in extra file
	$TEMPLATEDATA = $pageConfig;
	
	/*
	Multilingual in template:
	To support multilingual in template, write HTML code in this way:
	<?php if ($USERLANGUAGE == 'option-language1'): ?> #Language + Region
		<span>Content in option language 1</span>
	<?php elseif (substr($USERLANGUAGE,1,2) == 'option-language2'): ?> #Language
		<span>Content in option language 2</span>
	<?php else: ?>
		<span>Content in default language</span>
	<?php endif; ?>
	Do not forget $BW->site['DefaultLanguage'] in stored BW_Config.
	*/
	
	//Language used on the page (Those languages are available in the SITENAME_page.json)
	if (substr($USERLANGUAGE,1,2) == 'en')
		$PAGELANG = 'en';
	else
		$PAGELANG = 'default';

/*****************************************************************************************/
?>
<!DOCTYPE html>
<html
	data-pagestatus="<?= $PAGEDATA['Status'] ?>"
	data-httpstatus="<?= http_response_code() ?>"
	lang="<?= $PAGEDATA['Language'] ?>"
	data-urlprefix="<?= $USERLANGUAGE ?>"
>
	<head>
		<title><?= $PAGEDATA['Title']; ?> - Das SAM Club</title>
		<meta name="keywords" content="<?= $PAGEDATA['Keywords'] ?>" />
		<meta name="description" content="<?= $PAGEDATA['Description'] ?>" />
		<meta name="author" content="<?= $PAGEDATA['Author'] ?>" />
		<meta name="robots" content="<?= $PAGEDATA['Status'] == 'S' ? 'noindex' : 'index'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta charset="utf-8" />
		<link href="/web/favorite.png" rel="icon" type="image/png" />
		<link href="/web/style.css" rel="stylesheet" type="text/css" />
<?php
	//If "S", SEO no index, do not provide multilingual info
	if ($PAGEDATA['Status'] != 'S') foreach ($LANGUAGESET as $x)
		echo '<link rel="alternate" hreflang="',$x,'" href="/',$x,'/',$PAGEDATA['URL'],'" />';
?>
		<script src="/web/ajax.js"></script>
		<script src="/web/md5.js"></script>
		<script src="/web/util.js"></script>
		<script src="/web/ini.js"></script>
	</head>
	<body>
		<header>
			<h1 id="header_logo">Beardle</h1>
			<span id="phone_menu_button">≡</span>
			<form id="search_container" action="<?= $USERLANGUAGE ?>/search" method="get" target="searchtab">
<?php if ($PAGELANG == 'en'): ?>
				<input name="search" id="search" placeholder="Enter keyword to search" />
<?php else: ?>
				<input name="search" id="search" placeholder="输入关键字搜索" />
<?php endif; ?>
			</form>
			<nav id="header_nav">
<?php
	foreach ($TEMPLATEDATA['NavLinks'][$PAGELANG] as $name=>$link)
		echo '<a href="',$USERLANGUAGE,$link,'">',$name,'</a>';
?>
			</nav>
		</header>
		<img id="banner" alt="Banner image" src="/<?= $PAGEDATA['Info']['Poster'] ?? 'web/banner.jpg' ?>" />
		<div id="side">
			<img src="/web/top.png" alt="Top of page" title="To page top" />
		</div>
		<main>
			<div id="main_title">
				<h1><?= $PAGEDATA['Title']; ?></h1>
			</div>
			<div id="main_content">
<?php include $templateSub; ?>
			</div>
		</main>
		
		<footer>
			<div class="pltr">
				<img src="/web/logo.png" />
				<div>
					<p><?= $TEMPLATEDATA['SiteOwner'][$PAGELANG] ?></p>
					<p>Admin e-mail: <a href="mailto:<?= $TEMPLATEDATA['AdminEmail'] ?>"><?= $TEMPLATEDATA['AdminEmail'] ?></a></p>
					<p>© 
<?php
	$cprAuthorField = $PAGEDATA['Author'] ? '<span class="bearweb_author">'.$PAGEDATA['Author'].'</span>' : '';
	if ($PAGELANG == 'en') {
		if (!$PAGEDATA['Copyright'])
			echo trim( $cprAuthorField.' - All rights reserved' ," \-");
		else if (substr($PAGEDATA['Copyright'],0,2) == 'R=')
			echo trim( substr($PAGEDATA['Copyright'],2).' - Uploaded by: '.$cprAuthorField ," \-");
		else
			echo trim( $cprAuthorField.' ★ This work is licensed under '.$PAGEDATA['Copyright'] ," \-");
	}
	else {
		if (!$PAGEDATA['Copyright'])
			echo trim( $cprAuthorField.' - 保留一切权利' ," \-");
		else if (substr($PAGEDATA['Copyright'],0,2) == 'R=')
			echo trim( substr($PAGEDATA['Copyright'],2).' - 由'.$cprAuthorField.'上传' ," \-");
		else
			echo trim( $cprAuthorField.' ★ 按照'.$PAGEDATA['Copyright'].'协议进行共享' ," \-");
	}
?>
					</p>
				</div>
			</div>
			<div>
<?php
	switch ($PAGELANG) {
		case 'en':	echo '<span>This page is also avaliable in: </span>'; break;
		default:	echo '<span>本页面亦适用于这些语言： </span>';
	}
	foreach ($LANGUAGESET as $x)
		echo '<a hreflang="',$x,'" href="/',$x,'/',$PAGEDATA['URL'],'"> 🌍',$x,'</a> ';
?>
			</div>
		</footer>
		
		<div id="modal_container" onclick="modal()"><div id="modal">
			
<?php if ($PAGELANG == 'en'): ?>
			<div id="modal_close">╳ （Close）</div>
<?php else: ?>
			<div id="modal_close">╳ （关闭）</div>
<?php endif; ?>
			<div id="modal_content" onclick="event.stopPropagation()"></div>
		</div></div>
	</body>
</html>
