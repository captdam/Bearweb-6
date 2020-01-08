<div class="main_content_title">
	<h1><?= $PAGEDATA['Title'] ?></h1>
	<p>By: <?= $PAGEDATA['Author'] ?></p>
</div>

<div>
<?php if ($PAGELANG == 'en'): ?>
	<p>Description: <?= $PAGEDATA['Description'] ?></p>
	<p class="content_keywords"><?= $PAGEDATA['Keywords'] ?></p>
	<p>Last Modify: <?= $PAGEDATA['LastModify'] ?></p>
	<p>First publish; <?= $PAGEDATA['CreateTime'] ?></p>
<?php else: ?>
	<p>简介：<?= $PAGEDATA['Description'] ?></p>
	<p class="content_keywords"><?= $PAGEDATA['Keywords'] ?></p>
	<p>修改：<?= $PAGEDATA['LastModify'] ?></p>
	<p>发布：<?= $PAGEDATA['CreateTime'] ?></p>
<?php endif; ?>
	<p class="content_multilingual">
	<?php foreach ($LANGUAGESET as $x) echo '<a href="/',$x,'/',$PAGEDATA['URL'],'"> 🌍',$x,'</a>'; ?>
	</p>
</div>

<?php
	$statusTable = array(
		'en' => array(
			'C' => '<h2>Construction</h2><i>This page is under cunstruction, the author may alter the content on this page.</i>',
			'D' => '<h2>Deprecated</h2><i>Content on this page is deprecated and this page may be deleted/locked at any time.</i>',
			'S' => '<h2>Special</h2><i>This is a special page. Technically speaking, this is a page that should not be indexed.</i>',
			'A' => '<h2>Auth required</h2><i>Auth is required. The content is available to the author, the admin and those who has granted permission only.</i>',
			'P' => '<h2>Pending</h2><i>Pending page, available to the author and admin only.</i>'
		),
		'default' => array(
			'C' => '<h2>施工中</h2><i>此页面正在施工中，页面内容随时可能发生更改。</i>',
			'D' => '<h2>不推荐</h2><i>由于某些<del>黑幕</del>，本页面的所提到的的内容是不被推荐的内容，本页面随时可能会被删除或锁定（无法访问）。</i>',
			'S' => '<h2>特殊页面</h2><i>总之这个页面的内容都是特殊内容。Web技术层面来解释的话，这个页面是可以被访问但是不应该出现在网站地图的内容。</i>',
			'A' => '<h2>授权限制</h2><i>本页面带有权限管理，只有作者，管理员，或是白名单内的用户才可访问。</i>',
			'P' => '<h2>挂起页面</h2><i>本页面已被挂起，只有作者与管理员才可以访问。</i>'
		)
	);
	
	if (in_array($PAGEDATA['Status'],['C','D','S','A','P'])) {
		echo '<div style="background: linear-gradient(0.375turn, orange 7px, #000 7px,  #000 21.28px, orange 21.28px, orange 35.28px, #000 35.28px,  #000 49.57px, orange 49.57px); background-size: 40px 40px;"><div style="background-color: rgba(200,200,200,0.8);">';
		if ($PAGELANG == 'en')
			echo $statusTable[$PAGELANG][$PAGEDATA['Status']];
		else
			echo $statusTable['default'][$PAGEDATA['Status']];
		echo '</div></div>';
	}
?>

<?php
	writeLog('Print content.');
	echo $PAGEDATA['Content'];
?>
<script>
	window.addEventListener('load',function(){
		var image = document.getElementById('banner').src;
		if (image != 'web/banner.jpg')
			document.getElementById('main_content').style.backgroundImage = 'url(\''+image+'\')';
	});
</script>