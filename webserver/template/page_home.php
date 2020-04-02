<?php
	$pagedata = json_decode($PAGEDATA['Content'],true);
	foreach ( $pagedata['Sections'] as $url=>$info) {
		/* Syntax: "AbsoluteURL" : {"BgImg","Title","SubTitle","Description"} */
		echo 
			'<a href="',$USERLANGUAGE,$url,'" class="menu" style="background-image:linear-gradient( rgba(0, 0, 0, 0.5) 100%, rgba(0, 0, 0, 0.5)100%), url(\'/web/',$info[0],'_long.jpg\');">',
			'<img src="/web/',$info[0],'.png" alt="',$info[1],'" /><div>',
			'<h2>',$info[1],'</h2>',
			'<p>',$info[2],'</p>',
			'<p>',$info[3],'</p>',
			'</div></a>';
	}
?>

<div class="main_content_title">
<?php if ($PAGELANG == 'en'): ?>
	<h1>Recent update</h1>
	<p>There are some updates since your last visit:</p>
<?php else: ?>
	<h1>最近更新</h1>
	<p>查看最近更新的内容：</p>
<?php endif; ?>
</div>

<?php
	writeLog('Get page list for category '.$TEMPLATEDATA['Category'].'. Size 10, offset 0');
	$recent = $BW->database->call(
		'Sitemap_getRecentWebpageIndex',
		array(
			'Site'		=> $PAGEDATA['Site'],
			'Category'	=> $TEMPLATEDATA['Category'],
			'Size'		=> 10,
			'Offset'	=> 0
		),
	true);
	
	//Re-format page index by URL then Language
	$urlSet = array();
	$urlLang = array();
	foreach($recent as $x) {
		//URL->Language->Info: Contains pages info
		if (!isset( $urlSet[ $x['URL'] ] )) $urlSet[ $x['URL'] ] = array();
		$urlSet[ $x['URL'] ][ $x['Language'] ] = array(
			'Poster'	=> $x['Poster'] ? (',url(\''.$x['Poster'].'\')') : '',
			'Title'		=> $x['Title'],
			'Description'	=> $x['Description'],
			'Keywords'	=> $x['Keywords'],
			'Author'	=> $x['Author'],
			'LastModify'	=> $x['LastModify']
		);
		//URL->Language: For util function chooseLanguage()
		if (!isset( $urlLang[ $x['URL'] ] )) $urlLang[ $x['URL'] ] = array();
		array_push( $urlLang[$x['URL']] ,$x['Language'] );
	}
	
	//Print list, one for each URL
	foreach($urlLang as $url => $langIndex) {
		$lang = chooseLanguage($langIndex,$PAGELANG,$TEMPLATEDATA['DefaultLanguage']); #Language in the index best match user language
		echo '<a href="/',$lang,'/',$url,'" class="menu" style="background-image:linear-gradient(rgba(0,0,0,0.5)100%,rgba(0,0,0,0.5)100%)',$urlSet[$url][$lang]['Poster'],'"><div>',
			'<h2>',$urlSet[$url][$lang]['Title'],'</h2>',
			'<p class="content_description">',$urlSet[$url][$lang]['Description'],'</p>',
			'<p class="content_keywords">',$urlSet[$url][$lang]['Keywords'],'</p>';
		if (count($urlLang[$url]) > 1) { #Multiple language avaliable for a URL
			echo '<p class="content_multilingual">';
			foreach ($urlLang[$url] as $x) echo '<span class="langlink">',$x,'</span>';
			echo '</p>';
		}
		echo '<p class="content_author">',$urlSet[$url][$lang]['Author'],'</p>',
			'<p class="content_lastmodify">',$urlSet[$url][$lang]['LastModify'],'</p>',
			'</div></a>';
		
	}
?>
