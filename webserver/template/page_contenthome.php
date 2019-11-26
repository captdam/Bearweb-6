<?php
	$pageSize = 20;
	$currentPage = getInputPage();
	$pageOffset = $currentPage * $pageSize - $pageSize;
	echo $PAGEDATA['Content']; #Custom header
	
	writeLog('Get page list for category '.$PAGEDATA['Info']['Category'].'. Size '.$pageSize.', offset '.$pageOffset);
	$list = $BW->database->call(
		'Sitemap_getRecentWebpageIndex',
		array(
			'Site'		=> $PAGEDATA['Site'],
			'Category'	=> $PAGEDATA['Info']['Category'],
			'Size'		=> $pageSize,
			'Offset'	=> $pageOffset
		),
	true);
	
	//Re-format page index by URL then Language
	$urlSet = array();
	$urlLang = array();
	foreach($list as $x) {
		//URL->Language->Info: Contains pages info
		if (!isset( $urlSet[ $x['URL'] ] )) $urlSet[ $x['URL'] ] = array();
		$urlSet[ $x['URL'] ][ $x['Language'] ] = array(
			'Poster'	=> $x['Poster'] ?? 'NULL',
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
		echo '<a href="/',$lang,'/',$url,'" class="contentlist" data-bgimage="/',
			$urlSet[$url][$lang]['Poster'],'"><div>',
			'<h2>',$urlSet[$url][$lang]['Title'],'</h2>',
			'<p class="content_description">',$urlSet[$url][$lang]['Description'],'</p>',
			'<p class="content_keywords">',$urlSet[$url][$lang]['Keywords'],'</p>',
			'<p class="content_author">',$urlSet[$url][$lang]['Author'],'</p>',
			'<p class="content_lastmodify">',$urlSet[$url][$lang]['LastModify'],'</p>';
		if (count($urlLang[$url]) > 1) { #Multiple language avaliable for a URL
			echo '<p class="content_multilingual"> 🌍';
			foreach ($urlLang[$url] as $x) echo '<a href="/',$x,'/',$url,'">',$x,'</a>';
			echo '</p>';
		}
		echo '</div></a>';
	}
	
	//If no result
	if (!count($list)) {
		echo '<div class="pltr">',
			'<img src="/web/zhuangsha.jpg" alt="装傻" />',
			'<div>';
		if ($PAGELANG == 'en') {
			echo '<h2>No result</h2>',
				'<del>It\'s void! It is an endless darkness!</del>',
				'<p>There is no result in this category on this page.</p>',
				'<p>The result set is not enough to pop up to this page, try "Last page" or "First page".</p>';
		}
		else {
			echo '<h2>找不到结果</h2>',
				'<del>结果，不存在的，这辈子都不可能有的</del>',
				'<p>当前分类，当前分页，找不到任何结果。</p>',
				'<p>目前还没有足够达到你输入的页码那么多的结果，你可以尝试点击“上一页”或“第一页”。</p>';
		}
		echo '</div></div>';
	}
	
	//Page navigation
	$pageNumNav = array_filter(
		array($currentPage-2, $currentPage-1, $currentPage, $currentPage+1, $currentPage+2),
		function($x) {
			if ($x < 2) return false;
			return true;
		}
	);
	
	echo '<div class="resultlabels">';
	echo '<a href="',$USERLANGUAGE,'/',$PAGEDATA['URL'],'">1</a>';
	foreach($pageNumNav as $x)
		echo '<a href="',$USERLANGUAGE,'/',$PAGEDATA['URL'],'?page=',$x,'">',$x,'</a>';
	echo '</div>';
?>
