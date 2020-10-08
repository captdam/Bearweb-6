<?php
	$BW->log('Execute main template: object.php');

	$sql = $BW->remoteDB->prepare('CALL BW_Object_get(?,?)');
	$sql->bindValue(1, $BW->sitemap['Site'], PDO::PARAM_STR);
	$sql->bindValue(2, $BW->sitemap['URL'], PDO::PARAM_STR);
	$sql->execute();
	$object = $sql->fetch();
	$sql->closeCursor();

	if (!$object)
		throw new BW_DatabaseServerError(404, 'Object not found in BW_Object.');

	$resource = array_merge($object, $BW->sitemap);
	
	header('Content-Type: '.$object['MIME']);
	$BW->log('Pass control to sub template.');
	include $templateSub;
?>
