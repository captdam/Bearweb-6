<?php
	$BW->log('Execute page template.');
	header('Content-Type: text/html');
	
	//Get config
	try {
		$BW->log('Reading page template config file.');
		$filename = './template/'.SITENAME.'_page.json';
		$pageConfig = json_decode(file_get_contents($filename), true);
	} catch (Exception $e) {
		throw BW_WebServerError(500, 'Fail to load config file: '.$e->getMessage());
	}
	
	//Get all pages with different multilingual info by the given sitename and URL
	$BW->log('Fetch page language index.');
	$pageIndex = $BW->query('BW_Webpage_getLanguageIndex', [ $BW->sitemap['Site'], $BW->sitemap['URL'] ], 2);
	if (!$pageIndex) #Record found in BW_Sitemap but missing in BW_Webpage
		throw new BW_DatabaseServerError(404, 'Webpage not found.');

	foreach ($pageIndex as &$x)  #$pageIndex = [0=>['Language'=>lang1], 1=>['Language'=>lang2], 2=>['Language'=>lang3], ...]
		$x = $x['Language']; #$pageIndex = [lang1, lang2, lang3, ...]
	unset($x);
	$language = selectMultilingual($pageIndex, $BW->language, $BW->region, $pageConfig['DefaultLanguage'], $pageConfig['DefaultRegion']);
	
	//Fetch webpage data by specific multilingual info
	$BW->log('Fetch webpage: Site('.$BW->sitemap['Site'].') Language('.$language.') URL('.$BW->sitemap['URL'].').');
	$webpage = $BW->query('BW_Webpage_get', [ $BW->sitemap['Site'], $BW->sitemap['URL'], $language ], 1);
	

/********************************* TO WRITE THE TEMPLATE *********************************/

	//Data from database BW_Sitemap and BW_Webpage
	$BW_SITEMAP = $BW->sitemap;
	$BW_WEBPAGE = $webpage;
	
	//Prefix user's multilingual info to links on the page
	$BW_URLPREFIX = ( trim($BW->language.'-'.$BW->region,'-') == '' ) ? '' : ('/'.trim($BW->language.'-'.$BW->region,'-'));
	
	//Array of all available language for this page (SELECT `Language` FROM `BW_Webpage` WHERE `Site+URL`)
	$BW_MULTILINGUAL = $pageIndex;
	
	//Webpage template data in JSON saved in extra file
	$BW_TEMPLATEDATA = $pageConfig;

/*****************************************************************************************/

	//Text used in template
	$BW_TEMPLATE_TEXT = array(
		'__INDEX__' => ['en','zh'],
		'SEARCH_BOX' => ['Enter keyword to search','输入关键字搜索'],
		'FOOTER_MULTILINGUAL_PICKUP' => ['This page is also avaliable in','本页面亦适用于这些语言'],
		'MODAL_CLOSE' => ['Close','关闭']
	);
	$BW_TEMPLATE_TEXT = multilingualTextFilter($BW_TEMPLATE_TEXT, $BW->language, $BW->region, $BW_TEMPLATEDATA['DefaultLanguage'], $BW_TEMPLATEDATA['DefaultRegion']);
	$BW_NAV = multilingualTextFilter($BW_TEMPLATEDATA['Nav'], $BW->language, $BW->region, $BW_TEMPLATEDATA['DefaultLanguage'], $BW_TEMPLATEDATA['DefaultRegion']);

?>
<!DOCTYPE html>
<html
	data-pagestatus="<?= $BW_SITEMAP['Status'] ?>"
	data-httpstatus="<?= http_response_code() ?>"
	lang="<?= $BW_WEBPAGE['Language'] ?>"
	data-urlprefix="<?= $BW_URLPREFIX ?>"
>
	<head>
		<title><?= $BW_WEBPAGE['Title'].' - '.SITENAME; ?></title>
		<meta name="keywords" content="<?= $BW_WEBPAGE['Keywords'] ?>" />
		<meta name="description" content="<?= $BW_WEBPAGE['Description'] ?>" />
		<meta name="author" content="<?= $BW_SITEMAP['Author'] ?>" />
		<meta name="robots" content="<?= $BW_SITEMAP['Status'] == 'S' ? 'noindex' : 'index'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta charset="utf-8" />
		<link href="/web/favorite.png" rel="icon" type="image/png" />
		<link href="/web/style.css" rel="stylesheet" type="text/css" />
		<link href="/web/stylemodules.css" rel="stylesheet" type="text/css" />
<?php
	if ($BW_SITEMAP['Status'] != 'S') { #If "S" (SEO no index), do not provide multilingual info
		foreach ($BW_MULTILINGUAL as $x)
			echo '<link rel="alternate" hreflang="',$x,'" href="/',$x,'/',$BW_SITEMAP['URL'],'" />';
	}
?>
		<script src="/web/ajax.js"></script>
		<script src="/web/md5.js"></script>
		<script src="/web/util.js"></script>
		<script src="/web/ini.js"></script>
	</head>
	<body>
		<header>
			<h1 id="header_logo"><?= $BW_TEMPLATEDATA['Name'] ?></h1>
			<span id="phone_menu_button">≡</span>
			<form id="search_container" action="<?= $BW_URLPREFIX ?>/search" method="get" target="searchtab">
				<input name="search" id="search" placeholder="<?= $BW_TEMPLATE_TEXT['SEARCH_BOX'] ?>" />
			</form>
			<nav id="header_nav">
<?php
	foreach ($BW_NAV as $url=>$text)
		echo '<a href="',$BW_URLPREFIX,$url,'">',$text,'</a>';
?>
			</nav>
		</header>
		<div id="side">
			<img src="/web/top.png" alt="Top of page" title="To page top" />
		</div>
		<main>
			<div id="main_title" style="background-image:url('/<?= $BW_SITEMAP['Info']['poster'] ?? 'web/banner.jpg' ?>')">
				<div>
					<h1><?= $BW_WEBPAGE['Title']; ?></h1>
					<p><?= $BW_WEBPAGE['Description']; ?></p>
				</div>
			</div>
			<div id="main_content">
<?php include $templateSub; ?>
			</div>
		</main>
		
		<footer>
			<div class="pltr">
				<img src="/web/logo.png" />
				<div>
					<p>Admin: <?= $BW_TEMPLATEDATA['SiteOwner'] ?></p>
					<p>Admin e-mail: <a href="mailto:<?= $BW_TEMPLATEDATA['AdminEmail'] ?>"><?= $BW_TEMPLATEDATA['AdminEmail'] ?></a></p>
					<p>© 
<?php
	$cprAuthorField = $BW_SITEMAP['Author'] ? '<span class="bearweb_author">'.$BW_SITEMAP['Author'].'</span>' : '';
	echo $cprAuthorField;
/*	if (!$PAGEDATA['Copyright'])
		echo trim( $cprAuthorField.' - '.$BW_TEMPLATE_TEXT['ALL_RIGHTS_RESERVED'] ," \-");
	else if (substr($PAGEDATA['Copyright'],0,2) == 'R=')
		echo trim( substr($PAGEDATA['Copyright'],2).' - Uploaded by: '.$cprAuthorField ," \-");
	else
		echo trim( $cprAuthorField.' ★ This work is licensed under '.$PAGEDATA['Copyright'] ," \-");*/
?>
					</p>
				</div>
			</div>
			<div>
<?php
	echo '<span>',$BW_TEMPLATE_TEXT['FOOTER_MULTILINGUAL_PICKUP'],': </span>';
	foreach ($BW_MULTILINGUAL as $x)
		echo '<a class="langlink" hreflang="',$x,'" href="/',$x,'/',$BW->url,'">',$x,'</a> ';
?>
			</div>
		</footer>
		
		<div id="modal_container" onclick="modal()"><div id="modal">
			<div id="modal_close">╳ <?= $BW_TEMPLATE_TEXT['MODAL_CLOSE'] ?></div>
			<div id="modal_content" onclick="event.stopPropagation()"></div>
		</div></div>
	</body>
</html>
