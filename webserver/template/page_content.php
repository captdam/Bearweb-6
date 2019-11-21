<div class="main_content_title">
	<h1><?= $PAGEDATA['Title'] ?></h1>
	<p>By: <?= $PAGEDATA['Author'] ?></p>
</div>

<div>
<?php if (substr($USERLANGUAGE,1,2) == 'en'): ?>
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
</div>

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