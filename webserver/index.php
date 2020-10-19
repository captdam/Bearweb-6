<?php
	require_once './config.php';
	define('SITENAME',getenv('sitename'));
	date_default_timezone_set('UTC');
	define('CURRENT_TIMESTAMP',date('Y-m-d H:i:s'));
	
	//Setup error handler
	set_error_handler(function($errNo,$errStr,$errFile,$errLine){
		if (error_reporting() == 0) {
			return false;
		}
		throw new ErrorException($errStr,0,$errNo,$errFile,$errLine);
	});
	class BW_Error extends Exception {
		protected $httpcode; #HTTP status code descript the error
		function __construct($code,$message) {
			$this->httpcode = $code;
			parent::__construct($message,0,null);
		}
		public function __toString() {
			return get_class($this).' - '.$this->message;
		}
		public function getHttpCode() {
			return $this->httpcode;
		}
	}
	class BW_ServerError extends BW_Error {}
	class BW_WebServerError extends BW_ServerError{}	#Server-side front-end error:	PHP script error
	class BW_DatabaseServerError extends BW_ServerError{}	#Server-side end-end error:	Database error
	class BW_ExternalServerError extends BW_ServerError{}	#Server-side cloud-end error:	External server error, such as cloud database, token server, object storage server...
	class BW_ClientError extends BW_Error {}		#Client-side error:		Bad request from client
	
	//Include the Bearweb framwwork
	require_once './bearweb.class.php';
	require_once './util.php';
	require_once './objectstorage.class.php';
	
	//Process page
	ob_start();
	try {
		$BW = new Bearweb();
		$BW->init();
		$BW->processRequest();

		list($templateMain, $templateSub) = $BW->getTemplate();
		$BW->log('Executing template...');
		include $templateMain;

		/* Error handling in template file:
		 * Use $BW->query() to access remote database. Do not use try/catch block, error handled by framework. If error found, all database transactions in template will be rollback.
		 * Do NOT access local database, it is for BW's private database. Find a workaround.
		 * If an error needs to be throw, using:
		 * 	- BW_ClientError(4xx, 'Description.')		for client error, such as bad request, bad data format.
		 * 	- BW_DatabaseServerError(5xx, 'Description.')	for database error, such as missing record. (Most database error is handled by framework. You may catch other error and throw this one if you think it is appropriate)
		 * 	- BW_ExternalServerError(5xx, 'Description.')	for error on another server, such as external database server time-out.
		 * 	- BW_WebServerError(5xx, 'Description.')	for error on this server, such as missing local file.
		*/

		$BW->log('Template executed.');
		$BW->postProcess();

	} catch(BW_ClientError $e) { #Client error: show the error detail
		ob_clean(); ob_start();
		http_response_code($e->getHttpCode());
		$BW->log('HALT! CLIENT ERROR: '.$e->getMessage());
/**/	//	die($e->getMessage());
		$BW->useErrorTemplate($e);
		
	} catch(BW_ServerError $e) { #Server error: show error type
		ob_clean(); ob_start();
		http_response_code($e->getHttpCode());
		$BW->log('HALT! SERVER ERROR: '.$e->getMessage());
/**/	//	die($e->getMessage());
		$BW->useErrorTemplate(DEBUGMODE ? $e->getMessage() : 'BW_ServerError');
		
	} catch(Exception $e) { #Unexcepted error: dump detail in log
		ob_clean(); ob_start();
		http_response_code(500);
		$BW->log('HALT! UNEXPECTED SERVER ERROR: '.$e->getMessage());
		$BW->log(print_r($e,true));
/**/	//	die($e->getMessage());
/**/	//	var_dump($e);
		$BW->useErrorTemplate(DEBUGMODE ? $e->getMessage() : 'BW_ServerError');
	}

	$BW = null;
?>
