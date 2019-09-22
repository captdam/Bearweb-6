<?php
	//Bearweb object storage interface
	/* This includes all object storage operation functions */
	interface BearwebObjectStorageInterface {
		public function getList();			#Get a list of all files
		public function getContent($url);		#Get content of a file
		public function saveFile($dest,$src);		#Save a file
		public function saveContent($dest,$src);	#Save a variable's content
		public function deleteFile($url);		#Delete a file on the server
	}

	
	//Base object stoarge util
	/** For Conoha Object Storage Server ONLY **/
	class Conoha {
		private $token;
		private $endpoint;

                //Constructor
                //$username, $password, $tenant are provided by Conoha
                //$token: Your last token, if not know, put anything (NOTICE: PASS BY REFERENCE)
                //$expire: Expire time in UNIX timestamp, usually 24hrs after request token
                function __construct($username,$password,$tenant,&$token,$expire) {
                        //Token expire, request new
			if ($expire < time()) {
                                //Prepare cURL request
                                $curl = curl_init();
        			$header = array('Content-Type: application/json');
        			$data = array(
        				'auth' => array(
        					'passwordCredentials' => array(
        						'username' => $username,
        						'password' => $password
        					),
        					'tenantId' => $tenant
        				)
        			);
        			$option = array(
        				CURLOPT_HTTPHEADER	=> $header,
	        			CURLOPT_URL		=> 'https://identity.tyo1.conoha.io/v2.0/tokens',
        				CURLOPT_POST		=> true,
        				CURLOPT_POSTFIELDS	=> json_encode($data),
        				CURLOPT_RETURNTRANSFER	=> true,
        			);
        			curl_setopt_array($curl,$option);
        			//Send request
        			$body = curl_exec($curl);
        			if (curl_error($curl))
                                        throw new BW_Error(' - CURL error: Fail to renew token, error message: '.curl_error($curl).'.');
        			$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        			if(curl_errno($curl) || $status_code != 200)
                                        throw new BW_Error(' - CURL error: Bad return code, cURL error: '.curl_errno($curl).'; HTTP: '.$status_code.'.');
        			$authinfo = json_decode($body);
                                if (!$authinfo)
                                        throw new BW_Error(' - CURL error: Bad return data.');
                                //Renew token
                                $token = $authinfo->access->token->id;
			}
			//Token OK (not expired)
			$this->token = $token;
			$this->endpoint = 'https://object-storage.tyo1.conoha.io/v1/nc_'.$tenant.'/';
			//Notice: as long as the token did not expire (24hrs), it is possible to have more than one active token
                }

		//PUT
                //To create a container: $url = 'container_name'
                //To save a file, $url = 'url', $content = '/local/direction/to/file'
                //To save an string/blob, $url = 'url', $content = 'string/blob', $stream = false
		protected function put($url,$content=null,$stream=true) {
			//Create stream
			if ($content) {
				if (!$stream) {
					$temp = tmpfile();
					fwrite($temp,$content);
					fseek($temp,0);
					$content = $temp;
				}
				else $content = fopen($content,'r');
			}
			//Prepare request
			$curl = curl_init();
			$header = array();
			$header = array(
				'X-Auth-Token: '.$this->token
			);
			if ($content) $options = array( #Create object
				CURLOPT_PUT		=> true,
				CURLOPT_INFILE		=> $content,
				CURLOPT_URL		=> $this->endpoint.$url,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_HTTPHEADER	=> $header
			);
			else $options = array( #Create container
				CURLOPT_PUT		=> true,
				CURLOPT_URL		=> $this->endpoint.$url,
				CURLOPT_VERBOSE		=> true,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_HTTPHEADER	=> $header
			);
			curl_setopt_array($curl, $options);
			//Send request
			$body = curl_exec($curl);
                        if (curl_error($curl))
                                throw new BW_Error(' - CURL error: Fail to put content, error message: '.curl_error($curl).'.');
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$body);

		}

		//GET
                //$url: URL to resource, just the local URL, do not include beginning '/', for example 'photo/myphoto.png'
		protected function get($url) {
			//Prepare request
			$curl = curl_init();
			$header = array(
				'X-Auth-Token: '.$this->token
			);
			$options = array(
				CURLOPT_URL		=> $this->endpoint.$url,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_HTTPHEADER	=> $header
			);
			curl_setopt_array($curl, $options);
			//Send request
			$body = curl_exec($curl);
                        if (curl_error($curl))
                                throw new BW_Error(' - CURL error: Fail to get content, error message: '.curl_error($curl).'.');
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$body);
		}

		//DELETE
                //$url: URL to resource, just the local URL, do not include beginning '/', for example 'photo/myphoto.png'
		protected function delete($url) {
			//Prepare request
			$curl = curl_init();
			$header = array(
				'X-Auth-Token: '.$this->token
			);
			$options = array(
				CURLOPT_CUSTOMREQUEST	=> 'DELETE',
				CURLOPT_URL		=> $this->endpoint.$url,
				CURLOPT_VERBOSE		=> true,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_HTTPHEADER	=> $header
			);
			curl_setopt_array($curl, $options);
			//Send request
			$body = curl_exec($curl);
                        if (curl_error($curl))
                                throw new BW_Error(' - CURL error: Fail to delete content, error message: '.curl_error($curl).'.');
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$body);
		}
	}


	//Bearweb object storage util
	class BearwebObjectStorage extends Conoha implements BearwebObjectStorageInterface{
		
		private $container;
		
		function __construct(&$token,$expire) {
			$this->container = OS_CONTAINER.'/';
			writeLog(__METHOD__.' - Creating object storage server connection.');
			
			try {
				parent::__construct(
					OS_USERNAME,
					OS_PASSWORD,
					OS_TENANT,
					$token,
					$expire
				);
			} catch (BW_Error $e) {
				throw new BW_Error(__METHOD__.$e->getMessage());
			}
			
			writeLog(__METHOD__.' - Object storage server connection created.');
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
