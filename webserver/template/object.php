<?php
	$BW->log('Execute object template.');
	$object = $BW->query('BW_Object_get', [ $BW->sitemap['Site'], $BW->sitemap['URL'] ], 1);

	if (!$object) #Record found in BW_Sitemap but missing in BW_Object
		throw new BW_DatabaseServerError(404, 'Object not found in BW_Object.');
	
	header('Content-Type: '.$object['MIME']);
	$BW->log('Pass control to sub template:');
	include $templateSub;
?>
