<?php
	define('SITE',$argv[1]);
	
	require_once '../config.php';
	require_once '../database.class.php';
	
	function l($text) {
		echo "\t",'[Sitemap_gen] [',SITE,'] ',$text,"\n";
	}
	
	l('Generating sitemap, Job start!');
	
	l('Connecting to DBMS...');
	$db = new PDO(
		'mysql:dbname='.DB_NAME.';host='.DB_HOST.';charset=UTF8',
		DB_USERNAME,
		DB_PASSWORD,
		array(
			PDO::ATTR_PERSISTENT			=> false,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> false
		)
	);
	$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	
	l('Creating temp file...');
	$f = fopen('./sitemap_'.SITE.'.xml.temp','w');
	fwrite($f,'<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">');
	
	l('Reading sitemap...');
	$sql = $db->prepare('CALL Sitemap_get(?,?,?,?)');
	$sql->execute(array(SITE,NULL,NULL,'R,r,O,C,D'));
	$resources = $sql->fetchAll(PDO::FETCH_ASSOC);
	$sql->closeCursor();
	
	foreach ($resources as $resource) {
		$url = $resource['URL'];
		$template = $resource['TemplateMain'];
		$lastModify = $resource['LastModify'];
		
		$lastModifyInfo = $lastModify ? '<lastmod>'.str_replace(' ','T',$lastModify).'+00:00</lastmod>' : ''; #No LastModify, print empty
		
		if ($template == 'object') { #Object
			fwrite($f,'<url><loc>'.SEO_DOMAIN[SITE].$url.'</loc>'.$lastModifyInfo.'</url>');
		}
		
		else if ($template == 'page') { #Webpage: multilingual
			$pageSql = $db->prepare('CALL Webpage_getLanguageIndex(?,?)');
			$pageSql->execute(array(SITE,$url));
			$language = $pageSql->fetchAll(PDO::FETCH_ASSOC);
			
			fwrite($f,'<url><loc>'.SEO_DOMAIN[SITE].$url.'</loc>'.$lastModifyInfo); #Base URL
			foreach ($language as $altLangIndex) {
				$altLang = $altLangIndex['Language'];
				fwrite($f,'<xhtml:link rel="alternate" hreflang="'.$altLang.'" href="'.SEO_DOMAIN[SITE].$altLang.'/'.$url.'"/>');
			}
			fwrite($f,'</url>');
			
			foreach ($language as $langIndex) { #URL with multilingual info
				$lang = $langIndex['Language'];
				fwrite($f,'<url><loc>'.SEO_DOMAIN[SITE].$lang.'/'.$url.'</loc>'.$lastModifyInfo);
				foreach ($language as $altLangIndex) {
					$altLang = $altLangIndex['Language'];
					fwrite($f,'<xhtml:link rel="alternate" hreflang="'.$altLang.'" href="'.SEO_DOMAIN[SITE].$altLang.'/'.$url.'"/>');
				}
				fwrite($f,'</url>');
			}
		}
		
	}
	
	l('Updating sitemap file...');
	fwrite($f,'</urlset>');
	fclose($f);
?>