<?php
	//Show HD from storage server
	if (array_key_exists('HD',$_GET)) {
		$os = 'https://object-storage.tyo1.conoha.io/v1/nc_'.OS_TENANT.'/'.
			OS_PUBLICCONTAINER.'/'.SITENAME.'/'.$PAGEDATA['URL'];
		http_response_code(303);
		header('Location: '.$os);
	}
	
	//Show thumb from local
	else {
		echo $PAGEDATA['Binary'];
		writeLog('Binary (thumb) data output.');
	}
?>
