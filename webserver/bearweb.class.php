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
			$this->data = array(
				'URL'		=> '',
				'MIME'		=> 'text/html',
				'Title'		=> 'An error has been detected',
				'Keywords'	=> '',
				'Description'	=> 'Bearweb framework error page',
				'Category'	=> 'Error',
				'Author'	=> '@Bearweb',
				'TemplateMain'	=> 'page',
				'TemplateSub'	=> 'error',
				'Data'		=> $errorMessage,
				'Binary'	=> '',
				'JSON'		=> array(),
				'CreateTime'	=> '1000-01-01 00:00:00',
				'LastModify'	=> '1000-01-01 00:00:00',
				'Version'	=> 0,
				'Status'	=> 'S',
				'Copyright'	=> 'All rights reserved'
			);
			try {
				$this->useTemplate(false);
			} catch(Exception $e) {
				writeLog('Fail to execute error template. Print in plain text.',true);
				echo $errorMessage;
				exit;
			}
			writeLog('Error template executed!');
		}

		//Using template file
		public function useTemplate($sub=true) {
			//Get template file
			$template = $sub ? 
				($this->data['TemplateMain'].'_'.$this->data['TemplateSub']) : 
				$this->data['TemplateMain'];
			writeLog('Using template: '.$template);
			$templateFile = './template/'.$template.'.php';
			
			//In case template file missing
			if (!file_exists($templateFile)) {
				http_response_code(500);
				writeLog('Template file is missing.',true);
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework server error: Fail to load template file. Template file: '.$template) :
					'Bearweb framework server-side error'
				);
			}
			
			//Execute template
			try {
				global $BW;
				include $templateFile;
			//Error found in template
			/*
			What to do in template
			1 - Write log with writeLog('description') for critical steps
			2 - If you need to throw an error (cause by client, ex, bad request)
				http_response_code(default:500);
				define('TEMPLATE_NOTEERROR','whatever');
				throw new BW_Error('description');
			3 - If you need to throw an error (cause by server, ex, external server fail)
				http_response_code(default:500);
				throw new BW_Error('description');
			4 - When you call some method, such as database operation
				You do not need to use try/catch, error will be catch here
			*/
			} catch(BW_Error $e) {
				/*
				NOTICE: BW_ERROR
				If the error is throw in sub-template, the error will be re-catch in
				main-template. So, do not process the exception while sub-template,
				just pass it to mian-template.
				*/
				if ($sub)
					throw new BW_Error($e->getMessage());
				
				if (defined('TEMPLATE_NOTEERROR')) {
					writeLog('BW_Error found in template: '.$e->getMessage());
				}
				else {
					writeLog('BW_Error found in template: '.$e->getMessage(),true);
					if (http_response_code() == 200)
						http_response_code(500);
				}
				
				throw new BW_Error( DEBUGMODE ?
					('Bearweb framework template error: error occured when execute template: '.$e->getMessage()) :
					'Bearweb framework template error'
				);
			}
			writeLog('Template: '.$template.' executed!');
		}
		
		//Inilization and ending process
		public function ini() {
			$this->smartURL($this->URL,$this->location);
			$this->connectDatabase($this->database);
			$this->getSiteConfig($this->site);
			$this->getClientInfo($this->client);
			$this->getPage($this->page);
			return;
			$this->processData();
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
				
				//Log
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
				
				//Append user info
				$client['UserInfo'] = $user[0];
				writeLog('Returned user: Member. Username: '.$client['UserInfo']['Username']);
				return;
			
			//User has no valid session ID
			New_user:
				
				//Create new session
				$session = $this->database->call('Session_new',array(),true)[0];
				$client['SessionInfo'] = array_merge($client['SessionInfo'],$session);
				writeLog('New user. Generate Session ID: '.$session['SessionID']);
				
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
				throw new BW_ClientError(404,'Page not found');
			}
			/*
			Sitemap_get:
			Site: Apache param SITENAME, or NULL for ALL
			URL: URL w/o location info, select * using LIKE, or NULL for ALL
			Category: String array split using ",", or NULL for ALL
			Status: Char array, FIND_IN_SET, or NULL for ALL
			*/
			
			writeLog('Page data fetched.');
		}

		//Process page data
		protected function processData() {
			writeLog('Processing page. Status: '.$this->data['Status']);
			
			//Determine flag
			switch($this->data['Status']) {
			  case 'R': #Page removed perm
			  case 'r': #Page removed temp
				if (
					!isset($this->data['JSON']['redirect']) ||
					!is_string($this->data['JSON']['redirect'])
				) {
					http_response_code(500);
					writeLog('Redirect URL undefined.',true);
					throw new BW_Error( DEBUGMODE ?
						'Bearweb framework server error: Redirect URL undefined, redirect fail.' :
						'Bearweb framework server-side error'
					);
				}
				
				http_response_code($this->data['Status'] == 'R' ? 301 : 302);
				header('Location: /'.$this->data['JSON']['redirect']);
				writeLog('Page redirect to: '.$this->data['JSON']['redirect']);
				exit;
			
			  case 'A': #Auth need (privilege)
				if (
					!isset($this->data['JSON']['whitelist']) ||
					!is_array($this->data['JSON']['whitelist'])
				) {
					http_response_code(500);
					writeLog('Whitelist undefined.',true);
					throw new BW_Error( DEBUGMODE ?
						'Bearweb framework server error: Whitelist undefined, fail to varify privilege.' :
						'Bearweb framework server-side error'
					);
				}
				
				if (
					$this->client['Group'] != '@Admin' &&
					$this->client['Username'] != $this->data['Author'] &&
					!in_array($this->client['Username'],$this->data['JSON']['whitelist']) &&
					!in_array($this->client['Group'],$this->data['JSON']['whitelist'])
				) { #Open to admin group, author and those has privilege
					http_response_code(401);
					writeLog('Access denied: auth required.');
					throw new BW_Error('Page is locked/pending, only admin, author and those have the privilege could access this resource, please auth first.');
				}
				break;
			
			  case 'P': #Pending page
				if (
					$this->client['Group'] != '@Admin' &&
					$this->client['Username'] != $this->data['Author']
				) {
					http_response_code(403);
					writeLog('Access denied: pending page.');
					throw new BW_Error('Page is locked/pending, only admin and the author have the privilege to access this resource, please auth first.');
				}
				break;
			
			  case 'O': #OK
			  case 'C': #Construction
			  case 'D': #Deprecated
			  case 'S':
				break;
			
			  default:
				http_response_code(500);
				writeLog('Invalid status code.',true);
				throw new BW_Error( DEBUGMODE ?
					'Bearweb framework server error: Status code not supported.' :
					'Bearweb framework server-side error'
				);
			}
			writeLog('Page status processed.');
			
			//Send page misc headers
			header('Content-Type: '.$this->data['MIME']);
			if ($this->data['CreateTime'] == '1000-01-01 00:00:00') {
				header('Last-Modified: '.date('D, j M Y G:i:s').' GMT');
				header('Etag: '.md5(rand()));
			}
			else {
				header('Last-Modified: '.date('D, j M Y G:i:s',strtotime($this->data['LastModify'])).' GMT');
				header('Etag: '.$this->data['LastModify']);
			}
			writeLog('Page processed.');
		}
		
		
//		//Debug using
//		function __debuginfo() {
//			$return = array(
//				'URL' => $this->URL,
//				'site' => array(
//					'Closede' => $this->site['Closed']
//				),
//				'client' => $this->client,
//				'data' => $this->data
//			);
//			$return['data']['Data'] = '==STRING==';
//			$return['data']['Binary'] = '==BINARY==';
//			$return['data']['JSON'] = '==JSON==';
//			return $return;
//		}

	}
?>
