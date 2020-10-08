<?php
	//Show HD from storage server
	if (array_key_exists('HD',$_GET)) {
		$os = 'https://object-storage.tyo1.conoha.io/v1/nc_'.OS_TENANT.'/'.
			OS_PUBLICCONTAINER.'/'.SITENAME.'/'.$object['URL'];
		http_response_code(303);
		header('Location: '.$os);
		$BW->log('Redirect to orginal image at: '.$os);

		//In case 303 is not supported
		header('Content-Type: text/html');
		echo '<html><h1>Orginal image on object storage server</h1><p><a href="'.$os.'">Click this click if your browser is not automatically redirected to the external server</a></p></html>';
	}
	
	//Show thumb from local
	else {
		$BW->log('Printing thumb in BW_Object.Binary.');
		echo $object['Binary'];
	}
?>
