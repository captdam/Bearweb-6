<?php
	require_once './conoha.class.php';
	define('OS_TOKENEXPIRE',72000); #20 hours
	
	
	//Bearweb object storage interface
	/* This includes all object storage operation functions */
	interface BearwebObjectStorageInterface {
		public function getList($container);			#Get a list of all files
		public function getContent($container,$url);		#Get content of a file
		public function saveFile($container,$dest,$src);	#Save a file
		public function saveContent($container,$dest,$src);	#Save a variable's content
		public function deleteFile($container,$url);		#Delete a file on the server
	}
	
	/*User should directly call this object, this object will init itself if not init*/
	

	//Bearweb object storage util
	class BearwebObjectStorage extends Conoha implements BearwebObjectStorageInterface{
		
		private $init;
		
		function __construct() {
			$this->init = false;
		}
		
		function cst() {
			global $BW;
			$siteConfigs = $BW->database->call('Config_get',array('sitename'=>SITENAME),true);
			
			$config = false;
			foreach ($siteConfigs as $x) {
				if ($x['Key'] == 'ObjectStorageToken')
					$config = $x['Value'];
			}
			if (!$config)
				$config = '0,0';
			
			$config = explode(',',$config);
			$token = $config[0];
			$issue = $config[1];
			writeLog('[OS]Object Stroage config: Token: '.$token.' Issue: '.$issue);
			
			$this->container = '/'; #root
			parent::__construct(OS_TENANT);
			
			//If token expire, auth to request new
			if ($issue < time() - OS_TOKENEXPIRE) {
				writeLog('[OS]Token expired. Renew...');
				try {
					$token = parent::register(array(
						'username' => OS_USERNAME,
						'password' => OS_PASSWORD
					));
				} catch (Exception $e) {
					throw new BW_StorageServerError(500,$e->getMessage());
				}
				
				writeLog('New token: '.$token);
				$BW->database->call(
					'Config_write',
					array(
						'Site' => '', #For all site
						'Key' => 'ObjectStorageToken',
						'Value' => $token.','.time()
					)
				);
			}
			else {
				parent::__construct(OS_TENANT,$token);
			}
			
			//Test connection
			writeLog('[OS]Verify object storage server.');
			try {
				parent::get('');
			} catch (Exception $e) {
				throw new BW_StorageServerError(500,$e->getMessage());
			}
			
			writeLog('[OS]Object storage server ready.');
		}
		
		public function getList($container) {
			if (!$this->init)
				$this->cst();
			
			writeLog('[OS]Get file list in container '.$container);
			
//			var_dump($container);
//			exit();
			
			try {
				$data = parent::get($container);
			} catch(Exception $e) {
				throw new BW_StorageServerError(500,'[OS]Cannot get list ('.$container.'): '.$e->getMessage());
			}
			
			if($data[0] != 200)
				throw new BW_StorageServerError(500,'[OS]Cannot get list ('.$container.'): HTTP CODE-'.$data[0]);
			
			writeLog('[OS]File list fatched.');
			return json_decode($data[1],true);
		}
		
		public function getContent($container,$url) {
			if (!$this->init)
				$this->cst();
			
			$url = $container.'/'.$url;
			writeLog('[OS]Get file content of '.$url);
			
//			var_dump($url);
//			exit();
			
			try {
				$data = parent::get($url);
			} catch(Exception $e) {
				throw new BW_StorageServerError(500,'[OS]Cannot get content ('.$url.'): '.$e->getMessage());
			}
			
			if($data[0] != 200)
				throw new BW_StorageServerError(500,'[OS]Cannot get content ('.$url.'): HTTP CODE-'.$data[0]);
			
			writeLog('[OS]File content fatched.');
			return $data[1];
		}
		
		public function saveFile($container,$dest,$src) {
			if (!$this->init)
				$this->cst();
			
			$url = $container.'/'.$dest;
			writeLog('[OS]Saving local/'.$src.' to remote/'.$url);
			
			try {
				$data = parent::put($url,$src);
			} catch(BW_StorageServerError $e) {
				throw new BW_StorageServerError(500,'[OS]Cannot save file ('.$url.'): '.$e->getMessage());
			}
			
			if($data[0] != 201)
				throw new BW_StorageServerError(500,'[OS]Cannot save file ('.$url.'): HTTP CODE-'.$data[0]);
			
			writeLog('[OS]File saved.');
		}
		
		public function saveContent($container,$dest,$src) {
			if (!$this->init)
				$this->cst();
			
			$url = $container.'/'.$dest;
			writeLog('[OS]Saving content to remote/'.$url);
			
			try {
				$data = parent::put($url,$src,false);
			} catch(Exception $e) {
				throw new BW_StorageServerError(500,'[OS]Cannot save content ('.$url.'): '.$e->getMessage());
			}
			
			if($data[0] != 201)
				throw new BW_StorageServerError(500,'[OS]Cannot save content ('.$url.'): HTTP CODE-'.$data[0]);
			
			writeLog('[OS]Content saved.');
		}
		
		public function deleteFile($container,$url) {
			if (!$this->container)
				$this->cst();
			
			$url = $container.'/'.$url;
			writeLog('[OS]Deleting file remote/'.$url);
			
			try {
				$data = parent::delete($url);
			} catch(Exception $e) {
				throw new BW_StorageServerError(500,'[OS]Cannot delete resource ('.$url.'): '.$e->getMessage());
			}
			
			if($data[0] != 204 && $data[0] != 404)
				throw new BW_StorageServerError(500,'[OS]Cannot delete resource ('.$url.'): HTTP CODE-'.$data[0]);
			
			writeLog('[OS]File deleted.');
		}
	}
	
	$OS = new BearwebObjectStorage();
?>
