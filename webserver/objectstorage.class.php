<?php
	require_once './conoha.class.php';
	define('OS_TOKENEXPIRE',72000); #20 hours
	
	
	//Bearweb object storage interface
	/* This includes all object storage operation functions */
	interface BearwebObjectStorageInterface {
		public function getList();			#Get a list of all files
		public function getContent($url);		#Get content of a file
		public function saveFile($dest,$src);		#Save a file
		public function saveContent($dest,$src);	#Save a variable's content
		public function deleteFile($url);		#Delete a file on the server
	}
	

	//Bearweb object storage util
	class BearwebObjectStorage extends Conoha implements BearwebObjectStorageInterface{
		
		private $container;
		
		function __construct() {
			global $BW;
			$site = $BW->database->call('Config_get',array('sitename'=>SITENAME),true);
			$config = explode(',',$site['ObjectStorageToken']);
			$token = $config[0];
			$issue = $config[1];
			writeLog('Object Stroage config: Token: '.$token.' Issue: '.$issue);
			
			$this->container = OS_CONTAINER.'/';
			parent::__construct(OS_TENANT);
			
			//If token expire, auth to request new
			if ($issue < time() - OS_TOKENEXPIRE) {
				writeLog('Expired. Renew...');
				try {
					$token = parent::register(array(
						'username' => OS_USERNAME,
						'password' => OS_PASSWORD
					));
				} catch (Exception $e) {
					throw new BW_StorageServerError(500,$e->getMessage());
				}
				
				writeLog('New token: '.$this->token);
				$BW->database->call(
					'Config_renewObjectStorage',
					array('Token'=>$this->token.','.time())
				);
			}
			
			//Test connection
			writeLog('Verify object storage server.');
			try {
				parent::get('');
			} catch (Exception $e) {
				throw new BW_StorageServerError(500,$e->getMessage());
			}
			
			writeLog('Object storage server ready.');
		}
		
		public function getList() {
			$url = $this->container;
			writeLog(__METHOD__.' - Get file list in container '.$url.'/');
			
			try {
				$data = parent::get($url);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if($data[0] != 200)
				throw new BW_Error(__METHOD__.' - Error: HTTP CODE-'.$data[0]);
			
			writeLog(__METHOD__.'File list fatched.');
			return explode("\n",trim($data[1]));
		}
		
		public function getContent($url) {
			$url = $this->container.$url;
			writeLog(__METHOD__.' - Get file content of '.$url);
			
			try {
				$data = parent::get($url);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if($data[0] != 200)
				throw new BW_Error(__METHOD__.' - Error: HTTP CODE-'.$data[0]);
			
			writeLog(__METHOD__.'File content fatched.');
			return $data[1];
		}
		
		public function saveFile($dest,$src) {
			$url = $this->container.$dest;
			writeLog(__METHOD__.' - Saving local/'.$src.' to remote/'.$url);
			
			try {
				$data = parent::put($url,$src);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if($data[0] != 201)
				throw new BW_Error(__METHOD__.' - Error: HTTP CODE-'.$data[0]);
			
			writeLog(__METHOD__.' - File saved.');
		}
		
		public function saveContent($dest,$src) {
			$url = $this->container.$dest;
			writeLog(__METHOD__.' - Saving content to remote/'.$url);
			
			try {
				$data = parent::put($url,$src,false);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if($data[0] != 201)
				throw new BW_Error(__METHOD__.' - Error: HTTP CODE-'.$data[0]);
			
			writeLog(__METHOD__.' - Content saved.');
		}
		
		public function deleteFile($url) {
			$url = $this->container.$url;
			writeLog(__METHOD__.' - Deleting file remote/'.$url);
			
			try {
				$data = parent::delete($url);
			} catch(BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			if($data[0] != 204 && $data[0] != 404)
				throw new BW_Error(__METHOD__.' - Error: HTTP CODE-'.$data[0]);
			
			writeLog(__METHOD__.' - File deleted.');
		}
	}
?>
