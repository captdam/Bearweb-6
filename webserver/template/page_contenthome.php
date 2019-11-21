<?php
	//Get elements on current page
	
	$pageSize = 20;
	$pageOffset = getInputPage() * $pageSize - $pageSize;
	echo $PAGEDATA['Content'];
	
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
	
	foreach($list as $page) {
		echo '<a href="'.$page['URL'].'" class="contentlist" data-bgimage="/',
			($page['Poster'] ?? 'NONE'),'"><div>',
			'<h2>',$page['Title'],'</h2>',
			'<p class="content_description">',$page['Description'],'</p>',
			'<p class="content_keywords">',$page['Keywords'],'</p>',
			'<p class="content_author">',$page['Author'],'</p>',
			'<p class="content_lastmodify">',$page['LastModify'],'</p>',
			'</div></a>';
	}
	
	/*
	
	
	$currentSize = sizeof($pages);
	$totalSize = $this->database->countPagesByCategory($BW->data['JSON']['category']);
	$totalPage = ceil($totalSize / $pageSize);
	writeLog('Result: '.$currentSize.'/'.$totalSize);
	
	if ($currentSize == 0) {
		echo '<div class="pltr">',
			'<img src="http://beardle.com/web/heihua.jpg" alt="装傻" />',
			'<div>',
			'<h2>找不到结果</h2>',
			'<del>结果，不存在的，这辈子都不可能有的</del>',
			'<p>当前分类，当前分页，找不到任何结果。</p>',
			'<p>目前还没有足够达到你输入的页码那么多的结果，你可以尝试点击“首页”。</p>',
			'</div>',
			'</div>';
	}
	else {
		$closePageNum = array_filter(
			array($cp-3, $cp-2, $cp-1, $cp, $cp+1, $cp+2, $cp+3),
			function($x) use($totalPage) {
				if ($x < 1 || $x > $totalPage)
					return false;
				return true;
			}
		);
	}
	
	echo '<div class="resultlabels">';
	echo '<a href="/',$BW->URL,'">首页</a>';
	if(isset($closePageNum)) {
		echo '<span>';
		foreach($closePageNum as $x) {
			echo '<a href="/',$BW->URL,'?page=',$x,'">',$x,'</a>';
		}
		echo '<a href="/',$BW->URL,'?page=',$totalPage,'">尾页</a>',
			'</span>';
	}
	echo '</div>';
	
	*/
?>
