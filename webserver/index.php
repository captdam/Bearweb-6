<?php
	define('SITENAME',getenv('sitename'));
	date_default_timezone_set('UTC');

	define('TRANSACTIONID',uniqid(SITENAME.'-',true));
	define('CURRENT_TIMESTAMP',date('Y-m-d H:i:s'));
	
	header('Cache-Control: private, max-age=3600');
	header('B-Powered-By: Bearweb 6.0');
	header('B-Request-ID: '.TRANSACTIONID);
	
	//Include the Bearweb framwwork
	require_once './config.php';
	require_once './bearweb.class.php';
	require_once './database.class.php';
	require_once './util.php';
	require_once './conoha.class.php';
	
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
	class BW_WebServerError extends BW_ServerError{}
	class BW_DatabaseServerError extends BW_ServerError{}
	
	class BW_ClientError extends BW_Error {}
	
	//Process page
	writeLog('Job start!');
	ob_start();
	try {
		$BW = new Bearweb();
		$BW->ini();
//		var_dump($BW);
		$BW->useTemplate(false);
		
	} catch(BW_ClientError $e) { #Client error: show the error detail
		ob_clean(); ob_start();
		http_response_code($e->getHttpCode());
		writeLog('Task TERMINATED! Due to CLIENT ERROR: '.$e);
		$BW->useErrorTemplate($e);
		
	} catch(BW_ServerError $e) { #Server error: show error type
		ob_clean(); ob_start();
		http_response_code($e->getHttpCode());
		writeLog('Task TERMINATED! Due to SERVER ERROR: '.$e,true);
		$BW->useErrorTemplate(DEBUGMODE ? $e : get_class($e));
		
	} catch(Exception $e) { #Unexcepted error
		ob_clean(); ob_start();
		http_response_code(500);
		writeLog('Task TERMINATED! Due to SERVER ERROR: '.$e,true);
		writeLog('Error debug info: '.print_r($e,true),true);
		$BW->useErrorTemplate('BW_InternalServerError');
	}
	
	//Process done: record request result
	$BW->done();
	writeLog('Job done!');
	
	//Write log to file system
	function writeLog($string,$err=false) {
		$text  = '['.date('y-m-d H:i:s').']';
		$text .= '['.TRANSACTIONID.']';
		$text .= $err ? '[ERROR]' : '';
		$text .= $string."\n";
		
		$file = './log/'.date('y-m-d').'.log';
		for ($i = 0; $i < 5; $i++) { #Retry 5 times if files system busy
			if (file_put_contents($file,$text,FILE_APPEND))
				break;
			usleep(1000);
		}
		
		if (!$err) #Write error to error log file
			return;
		$file = './log/'.date('y-m-d').'-error.log';
		for ($i = 0; $i < 5; $i++) { #Retry 5 times if files system busy
			if (file_put_contents($file,$text,FILE_APPEND))
				break;
			usleep(1000);
		}
	}
?>
