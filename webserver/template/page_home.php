<div class="main_content_title">
	<h1><?= $PAGEDATA['Title'] ?></h1>
	<p><?= $PAGEDATA['Description'] ?></p>
</div>
<?php
	$pagedata = json_decode($PAGEDATA['Content'],true);
	foreach ( $pagedata['Sections'] as $url=>$info) {
		/* Syntax: "AbsoluteURL" : {"BgImg","Title","SubTitle","Description"} */
		echo 
			'<a href="',$USERLANGUAGE,$url,'" class="menu" style="background-image: url(\'/web/',$info[0],'_long.jpg\');">',
			'<img src="/web/',$info[0],'.png" alt="',$info[1],'" /><div>',
			'<h2>',$info[1],'</h2>',
			'<p>',$info[2],'</p>',
			'<p>',$info[3],'</p>',
			'</div></a>';
	}
?>

<div class="main_content_title">
<?php if (substr($USERLANGUAGE,1,2) == 'en'): ?>
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
	
	foreach($recent as $page) {
		echo '<a href="'.$page['URL'].'" class="contentlist" data-bgimage="/',
			($page['Poster'] ?? 'NONE'),'"><div>',
			'<h2>',$page['Title'],'</h2>',
			'<p class="content_description">',$page['Description'],'</p>',
			'<p class="content_keywords">',$page['Keywords'],'</p>',
			'<p class="content_author">',$page['Author'],'</p>',
			'<p class="content_lastmodify">',$page['LastModify'],'</p>',
			'</div></a>';
	}
?>
