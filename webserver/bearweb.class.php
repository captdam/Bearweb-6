<?php
	/**=========================================================**\
	|                          Bearweb 6                          |
	|            A lightweight PHP MySQL web framework            |
	\**=========================================================**/

	class Bearweb {

		protected $URL;		#URL of current page (from request)
		protected $location;	#Language and region info in URL
		
		protected $database;	#Database connection interface
		protected $logID;
		
		protected $site;	#Site infomation from database
		protected $client;	#Client infomation
		protected $page;	#page data from database
		
		

		

		//Print error page (using the HTML page template)
		public function useErrorTemplate($errorMessage) {
			writeLog('Error found, now handing by error template: '.$errorMessage);
			
			//Create dummy BW class for template
			$this->URL = '@ERROR';
			/* Using the @ERROR page in BW_Sitemap and BW_Webpage */
			
			$this->location = $this->location ?? array(
				'language' => '',
				'region' => ''
			);
			/* Using user language, or fallback to default language */
			
			/*
			DBMS:
			If database has been connected, use it; otherwise, raise exception in template-page and print error info in plaintext
			#Log ID is created if database connected (PK of BW_Transaction). If database is not connected, do not care log ID.
			Site config:
			*/
			
			$this->site = $this->site ?? array();
			/* By default, no site config */
			
			$this->client = $this->client ?? array(
				'SessionInfo'	=> array(
					'IP'	=> $_SERVER['REMOTE_ADDR']
				)
			);
			/* By default, no user info */
			
			$this->page = array(
				'Site'		=> SITENAME,
				'URL'		=> '@ERROR', #Using error page in BW_Webpage
				'Category'	=> 'Error',
				'TemplateMain'	=> 'page', #Using error template
				'TemplateSub'	=> 'error',
				'Author'	=> null,
				'CreateTime'	=> null, #Page has no life time
				'LastModify'	=> null,
				'Copyright'	=> null,
				'Status'	=> 'S', #Special page: SEO: no-index
				'Info'		=> array(
					'ErrorInfo'	=> $errorMessage #Passing error info to template
				)
			);
			/* Dummy page info */
			
			//Execute error template
			try {
				$this->useTemplate(false);
			} catch(Exception $e) {
				writeLog('Fail to execute error template. Print in plain text: '.$e,true);
				echo $errorMessage;
			}
			writeLog('Error template executed!');
		}

		//Using template file
		public function useTemplate($sub=true) {
			//Get template file
			$template = $sub ? 
				($this->page['TemplateMain'].'_'.$this->page['TemplateSub']) : 
				$this->page['TemplateMain'];
			writeLog('Using template: '.$template);
			$templateFile = './template/'.$template.'.php';
			
			//In case template file missing
			if (!file_exists($templateFile)) {
				throw new BW_ClientError(500,'Template script missed.');
			}
			
			//Execute template
			global $BW;
			include $templateFile;
			
			/*
			Error handling in template file:
			If an error needs to be throw,
			Do NOT use try/catch block, all error handling done by the framework.
			Using:
				- BW_ClientError(4xx,'Description.') for client error (eg. bad request). Error info will be printed on client-side.
				- BW_ServerError(5xx,'Description.') for server error. Error info will be recorded in error_log file. If DEBUGMODE, error info will be printed on client-side as well.
			DBMS error is automaticly handled by framework. Do NOT using try/catch block for DBMS error.
			*/
				
			writeLog('Template: "'.$template.'" executed!');
		}
		
		//Inilization and ending process
		public function ini() {
			$this->smartURL($this->URL,$this->location);
			$this->connectDatabase($this->database);
			$this->getSiteConfig($this->site);
			$this->getClientInfo($this->client);
			$this->getPage($this->page);
			$this->processPage($this->page,$this->client);
		}
		public function done() {
			
			//Record statistic
			$timeUsed = (microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']) * 1000000;
			$this->database->call(
				'Transaction_bindStatisticInfo',
				array(
					'RecordID'	=> $this->logID,
					'ExecutionTime'	=> $timeUsed,
					'Status'	=> http_response_code()
				)
			);
			
			$this->database = null; //Destruct: commit
			writeLog('Execution time: '.$timeUsed.'ns');
		}

		//Process request URL
		protected function smartURL(&$url,&$location) {
			
			//Trim URL
			$url = trim($_GET[URL_PARAM]);
			$url = ltrim($url,'/');
			writeLog('Checking request URL. The request URL is: '.$url);
			
			//Check URL format
			if (!checkRegex('URL',$url)) {
				throw new BW_ClientError(400,'Request URL contains invalid character.');
			}
			writeLog('Request URL is valid!');
			
			//Multilingual determine (2 char ISO639-1 + [optional 2 char ISO3166-1Alpha2])
			if (strlen($url) > 2 && $url[2] == '/' && ctype_alpha(substr($url,0,2))) {
				$location = array(
					'language' => substr($url,0,2),
					'region' => ''
				);
				$url = substr($url,3);
			}
			else if (strlen($url) > 5 && $url[2] == '-' && $url[5] == '/' && ctype_alpha(substr($url,0,2)) && ctype_alpha(substr($url,3,2))) {
				$location = array(
					'language' => substr($url,0,2),
					'region' => substr($url,3,2)
				);
				$url = substr($url,6);
			}
			else {
				$location = array(
					'language' => '',
					'region' => ''
				);
			}
			
			$url = trim($url,'/');
			writeLog('User language: '.$location['language'].'; User region: '.$location['region']);
			
		}

		//Connect to database
		protected function connectDatabase(&$db) {
			
			//Connect to DBMS
			writeLog('Connecting to database.');
			$db = new BearwebDatabase();
			writeLog('Database connected!');
			
			//Init transaction log
			$this->logID = $db->call(
				'Transaction_new',
				array('TransactionID' => TRANSACTIONID),
			true)[0]['RecordID'];
			writeLog('Transaction log record ID: '.$this->logID);
		}

		//Get site setting from config database
		protected function getSiteConfig(&$site) {
			writeLog('Getting site config.');
			
			//Get site configs from db
			$site = array();
			$config = $this->database->call('Config_get',array('sitename'=>SITENAME),true);
			foreach ($config as $x)
				$site[$x['Key']] = $x['Value'];
			writeLog('Site config fetched!');
			
			//Site closed? (Column 'Closed' exists and not empty)
			if (array_key_exists('Closed',$site) && $site['Closed']) { #Use array_key_exists because it can be null
				throw new BW_ClientError(503,'Server closed: '.$config['Closed']); #Special case: This is a server error; use BW_ClientError to provide info to client.
			}
			writeLog('Site is ready.');
		}

		//Get client info
		protected function getClientInfo(&$client) {
			writeLog('Getting client info. IP: '.$_SERVER['REMOTE_ADDR']);
			$client = array(
				'SessionInfo'	=> array(
					'IP'	=> $_SERVER['REMOTE_ADDR']
				)
			);
			
			/*
			BW_Session table restriction:
			PK are SessionID and CreateTime. The table contains all active sessions and expired old sesions.
			Application logic restriction:
			Only one active session for each SessionID.
			Key "Expire" will be set to 1:
			1 - Every hour, the BDMS check for any session that LastUsed is more than 1 hour ago.
			2 - The application set it manully (eg. Logout event).
			*/
			
			/* Assembly-style code here: */
			
			//Check user session ID
			Returned_user_check:
				
				//SID found and verifed
				if ( !isset($_COOKIE['SessionID']) ) goto New_user;
				if ( !checkRegex('Token',$_COOKIE['SessionID']) ) goto New_user;
				
				//Check session database
				$session = $this->database->call(
					'Session_get',
					array('SessionID' => $_COOKIE['SessionID']),
				true);
				if (!$session) goto New_user;
				$session = $session[0];
				
				//Append session info to client info
				writeLog('User session ID: '.$_COOKIE['SessionID']);
				$client['SessionInfo'] = array_merge($client['SessionInfo'],$session);
				
				//Renew session
				$this->database->call(
					'Session_renew',
					array('SessionID'=>$session['SessionID'])
				);
				
				//Log - returned
				$this->database->call(
					'Transaction_bindClientInfo',
					array(
						'RecordID'	=> $this->logID,
						'SessionID'	=> $session['SessionID'],
						'Username'	=> $session['Username'],
						'IP'		=> $client['SessionInfo']['IP']
					)
				);
				
				//Member or visitor?
				if ($session['Username'])
					goto Returned_user_member;
				else
					goto Returned_user_visitor;
				
			
			//User has valid session ID but no username
			Returned_user_visitor:
				writeLog('Returned user: Visitor');
				return;
			
			//User has valid session ID and username
			Returned_user_member:
				
				//Get user (member) info
				$user = $this->database->call(
					'User_get',
					array('Username'=>$session['Username']),
				true);
				if (!$user) goto Returned_user_visitor; #This should not happen because of foreign key, but just in case
				
				//User group is an array
				$user = $user[0];
				$user['Group'] = explode(',',$user['Group']);
				foreach ($user['Group'] as &$x) {
					$x = trim($x);
				}
				
				//Append user info
				$client['UserInfo'] = $user;
				writeLog('Returned user: Member. Username: '.$client['UserInfo']['Username']);
				return;
			
			//User has no valid session ID
			New_user:
				
				//Create new session
				$session = $this->database->call('Session_new',array(),true)[0];
				$client['SessionInfo'] = array_merge($client['SessionInfo'],$session);
				writeLog('New user. Generate Session ID: '.$session['SessionID']);
				
				//Log - returned
				$this->database->call(
					'Transaction_bindClientInfo',
					array(
						'RecordID'	=> $this->logID,
						'SessionID'	=> $session['SessionID'],
						'Username'	=> $session['Username'],
						'IP'		=> $client['SessionInfo']['IP']
					)
				);
				
				//Send token to user
				setcookie('SessionID',$session['SessionID'],0,'/','',FORCEHTTPS,true);
				setcookie('JSKey',$session['JSKey'],0,'/','',FORCEHTTPS,false);
				setcookie('Salt',$session['Salt'],0,'/','',FORCEHTTPS,false);
				return;
			
		}

		//Get page info
		protected function getPage(&$page) {
			writeLog('Loading page data: '.$this->URL);
			
			//Log
			$this->database->call(
				'Transaction_bindPageInfo',
				array(
					'RecordID'	=> $this->logID,
					'URL'		=> $this->URL
				)
			);
			
			//Get page info (from sitemap)
			$page = $this->database->call(
				'Sitemap_get',
				array(
					'Site'		=> SITENAME,
					'URL'		=> $this->URL,
					'Category'	=> null,
					'Status'	=> null
				),
			true);
			if(!$page) {
				throw new BW_ClientError(404,'Page not found.');
			}
			/*
			Sitemap_get:
			Site: Apache param SITENAME, or NULL for ALL
			URL: URL w/o location info, select * using LIKE, or NULL for ALL
			Category: String array split using ",", or NULL for ALL
			Status: Char array, FIND_IN_SET, or NULL for ALL
			*/
			
			$page = $page[0];
			$page['Info'] = json_decode($page['Info'],true);
			writeLog('Page data fetched.');
		}

		//Process page data
		protected function processPage($page,$client) {
			writeLog('Processing page. Status: '.$page['Status']);
			
			//Determine flag
			switch($page['Status']) {
			  case 'R': #Page removed perm
			  case 'r': #Page removed temp
				if (
					!isset($page['Info']['Redirect']) ||
					!is_string($page['Info']['Redirect'])
				) {
					throw new BW_WebServerError(500,'Redirect info missed.');
				}
				
				$redirect = $page['Info']['Redirect'];
				http_response_code($page['Status'] == 'R' ? 301 : 302);
				header('Location: /'.$redirect);
				writeLog('Page redirect to: '.$redirect);
				break;
			
			  case 'A': #Auth need (privilege)
				if (
					!isset($page['Info']['Whitelist']) ||
					!is_array($page['Info']['Whitelist']) ||
					!isset($page['Info']['Whitelist']['Username']) ||
					!is_array($page['Info']['Whitelist']['Username']) ||
					!isset($page['Info']['Whitelist']['Group']) ||
					!is_array($page['Info']['Whitelist']['Group'])
				) {
					throw new BW_WebServerError(500,'Whitelist info missed.');
				}
				
				if (
					!in_array('Admin',$client['Group']) &&
					!in_array($client['Username'],$page['Info']['Whitelist']['Username']) &&
					count(array_intersect($client['Group'],$page['Info']['Whitelist']['Group'])) == 0
				) {
					throw new BW_WebServerError(401,'Access denied: auth required. Page is locked/pending, only admin, author and those have the privilege could access this resource, please auth first.');
				}
				break;
			
			  case 'P': #Pending page
				if (
					!in_array('Admin',$client['Group']) &&
					$client['Username'] != $page['Author']
				) {
					throw new BW_WebServerError(403,'Access denied: pending page. Page is locked/pending, only admin and the author have the privilege to access this resource, please auth first.');
				}
				break;
			
			  case 'O': #OK
			  case 'C': #Construction
			  case 'D': #Deprecated
			  case 'S': #Special
				break;
			
			  default:
				throw new BW_WebServerError(500,'Invalid status code for page.');
			}
			
			//Send page misc headers
			if ($page['LastModify'] == null) {
				header('Last-Modified: '.date('D, j M Y G:i:s').' GMT');
				header('Etag: '.md5(rand()));
			}
			else {
				header('Last-Modified: '.date('D, j M Y G:i:s',strtotime($page['LastModify'])).' GMT');
				header('Etag: '.trim( base64_encode($page['LastModify']),"= \t\n\r\0\x0B") );
			}
			
			writeLog('Page processed.');
		}

	}
?>
