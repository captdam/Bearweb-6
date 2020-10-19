<?php
	#  Bearweb 6  ##########################################################################
	class Bearweb {
		const VERSION = '6.1';

		//Database resources
		private		$localDB;	#A SQLite database used to store transaction and session data. Plus log of this transaction.
						#Other application should NOT access this database. Template should NOT access this database.
						#Since this is a database purely based on file, and only accessable to BW, we assume this is a
						#reliable database. BW will NOT perform any check.

		protected	$remoteDB;	#A MySQL database used to store other data, such as sitemap, resources and user data.
						#These data can be shared with other applications.
						#This database may be on the localhost, or on another host; this database may be modified
						#by other application and hence corrupted, BW will always perform check (try catch). 

		//Transaction control: A transaction is a single HTTP request
		protected	$transactionID;
		protected	$transactionIP;

		//Session control: A number of transactions from the same client (identified by cookie) in a period of time form a session
		protected	$sessionID;
		protected	$sessionUser; #Do not use! This may be incorrect if database is manully modified. Use $this->username and $this->userInfo instead.
		protected	$sessionJSKey;
		protected	$sessionSalt;

		//Server config: Dynamic server setting, such as external server token. Compare to those setting in config.php, these are constantly changing.
		protected	$server;

		//URL from user request, plus multilingual (region and language) info
		protected	$url;
		protected	$language;
		protected	$region;

		//User info
		protected	$username;	#null OR username
		protected	$userInfo;	#[] (empty array) OR array from remoteDB::BW_User
		protected	$userIsAdmin;	#true if user group has 'Admin'

		//Resource info
		protected	$sitemap;	#From remoteDB::BW_Sitemap

		//Getter
		public function __get($name) {
			switch ($name) {
				case 'url':		return $this->url;
				case 'language':	return $this->language;
				case 'region':		return $this->region;
				case 'username':	return $this->username;
				case 'userInfo':	return $this->userInfo;
				case 'userIsAdmin':	return $this->userIsAdmin;
				case 'sitemap':		return $this->sitemap; break;
				default: throw new BW_WebServerError(500, 'Access denied BW->'.$name);
			}
		}	

		//Write log to local database for this transaction
		public function log($text) {
			$sql = $this->localDB->prepare('UPDATE BW_Transaction SET Log = Log || ? WHERE TransactionID = ?');
			$sql->bindValue(1, $text.PHP_EOL, PDO::PARAM_STR);
			$sql->bindValue(2, $this->transactionID, PDO::PARAM_STR);
			$sql->execute();
			$sql->closeCursor();
		}

		//Get the main and sub template script file, init remote database transaction control
		public function getTemplate() {
			$templateMain = './template/'.$this->sitemap['TemplateMain'].'.php';
			$templateSub = './template/'.$this->sitemap['TemplateMain'].'_'.$this->sitemap['TemplateSub'].'.php';
			if (!file_exists($templateMain))	throw new BW_WebServerError(500, 'Missing main template script.');
			if (!file_exists($templateSub))		throw new BW_WebServerError(500, 'Missing sub template script.');

			try {
				$this->remoteDB->beginTransaction();
				$this->log('[DBMS][RemoteDB] Init transaction control.');
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500, 'Fail to begin transaction: '.$e->getMessage());
			}

			return [$templateMain, $templateSub];
		}

		//Post process: commit remote database transaction (call this after successfully executing template)
		public function postProcess() {
			try {
				$this->remoteDB->commit();
				$this->log('[DBMS][RemoteDB] Transaction commit.');
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500, 'Fail to commit transaction: '.$e->getMessage());
			}
		}

		//For template, use this to access remote database
		public function query($procedureName, $param, $return=0) { #Return = 0 if no data should be returned, 1 for first record (fetch), 2 or others for all records (fetchAll)
			$this->log('[DBMS][RemoteDB] Executing procedure: '.$procedureName);
			$result = null;
			
			try {
				$paramCount = sizeof($param);
				$procedureName .= '(';
				if ($paramCount)
					$procedureName .= '?';
				for ($i = 1; $i < $paramCount; $i++)
					$procedureName .= ',?';
				$procedureName .= ')';
				
				$sql = $this->remoteDB->prepare('CALL '.$procedureName);

				$i = 0;
				foreach($param as $x) {
					switch (gettype($x)) {
						case 'boolean':		$sql->bindValue(++$i, $x,		PDO::PARAM_BOOL	); break;
						case 'integer':		$sql->bindValue(++$i, $x,		PDO::PARAM_INT	); break;
						case 'double':		$sql->bindValue(++$i, strval($x),	PDO::PARAM_STR	); break; #MySQL pass double as string
						case 'string':		$sql->bindValue(++$i, $x, 		PDO::PARAM_STR	); break;
						case 'NULL':		$sql->bindValue(++$i, $x, 		PDO::PARAM_NULL	); break;
						case 'resource':	$sql->bindValue(++$i, $x, 		PDO::PARAM_LOB	); break;
						default:	throw new BW_DatabaseServerError(500, 'Param ('.$i.') type not supported:'.gettype($x));
					}
				}

				$sql->execute();
				if ($return) {
					if ($return == 1)
						$result = $sql->fetch();
					else
						$result = $sql->fetchAll();
				}
				$sql->closeCursor();
			} catch (Exception $e) {
				throw new BW_DatabaseServerError(500, 'Fail to query remote database: '.$e->getMessage());
			}
			
			return $result;
		}


		/******************************************************************************/
		//Init Bearweb (always scuccess): Connect to local database, create transaction and record client IP, get (or create) client session
		/******************************************************************************/
		public function __construct() {
			header('B-Powered-By: Bearweb '.self::VERSION);
			header('Cache-Control: private, max-age=3600'); #default cache control

			/* Task in the constructor always success:
			 * Because localDB is a database based on file system, and only BW will access it,
			 * We always assume write/read to the localDB success.
			*/

			$this->initLocalDB(); #Log writes to it, this must success; otherwise we doomed
			$this->initTransaction();
			header('B-Request-ID: '.$this->transactionID);

			/* STAGE-I: Transaction init: Local database ready and transaction record created. Now, we can start to write log to the transaction. */

			$this->processSession();
		}

		//Connect to local database
		private function initLocalDB() {
			$this->localDB = new PDO('sqlite:./localdb/bw.db'); #Since this is a single file database, should not get any error in this case
			$this->localDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->localDB->setAttribute(PDO::ATTR_TIMEOUT, 10000); #10s waiting time should be far more than enough
			$this->localDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}

		//Create a new transaction record, record client IP
		protected function initTransaction() {
			$this->transactionIP = $_SERVER['REMOTE_ADDR'];
			$this->transactionID = null;

			$sql = $this->localDB->prepare('INSERT INTO BW_Transaction (TransactionID, IP) VALUES (?,?)');
			$sql->bindParam(1, $this->transactionID, PDO::PARAM_STR); #By reference
			$sql->bindValue(2, $this->transactionIP, PDO::PARAM_STR);
			while (!$this->transactionID) { #Retry if TransactionID is not unique
				try {
					$this->transactionID = SITENAME.'-'.base64_encode(random_bytes(96)); #128 characters
					$sql->execute();
					$sql->closeCursor();
				} catch(Exception $e) {
					if (strpos($e->getMessage(), 'UNIQUE constraint failed'))
						$this->transactionID = null; #Run the while loop again
					else {
						http_response_code(500);
						if (DEBUGMODE)
							die('Bearweb init error: Local DB: '.$e->getMessage());
						else
							die('Bearweb error');
					}
				}
			}
		}

		//Get client session, or issue a new one
		protected function processSession() {
			$session = $this->getSession();
			if (is_string($session)) { #If client doesn't have valid session, a string describe the situation will be returned. In this case, issue a new session to client
				$this->log($session);

				list($this->sessionID, $this->sessionUser, $this->sessionJSKey, $this->sessionSalt) = $this->createSession();
				setcookie( 'SessionID',	$this->sessionID,	0, '/', '', FORCEHTTPS, true );
				setcookie( 'JSKey',	$this->sessionJSKey,	0, '/', '', FORCEHTTPS, false );
				setcookie( 'Salt',	$this->sessionSalt,	0, '/', '', FORCEHTTPS, false );
				$this->log('New Session issued.');
			}
			else { #Session info valid. In this case, renew client session
				list($this->sessionID, $this->sessionUser, $this->sessionJSKey, $this->sessionSalt) = $session;
				$this->log('Session ID OK, Username ('.$this->sessionUser.').');
			}

			//Record session ID in transaction database
			$sql = $this->localDB->prepare('UPDATE BW_Transaction SET SessionID = ? WHERE TransactionID = ?');
			$sql->bindValue(1, $this->sessionID, PDO::PARAM_STR);
			$sql->bindValue(2, $this->transactionID, PDO::PARAM_STR);
			$sql->execute();
			$sql->closeCursor();

			setcookie('LastCom', time(), 0, '/', '', FORCEHTTPS, false); #Client flag: Time to renew session (Using cookie instead of JS variable to share this value between different tabs)
		}

		//Get session info based on request cookie, renew session if valid, expire session if timeout
		private function getSession() {
			//Client should submit SID, and...
			if ( !isset($_COOKIE['SessionID']) ) {
				return 'No SID from client.';
			}

			//and this SID should be in good format, and...
			if ( !Checker::sid($_COOKIE['SessionID']) ) {
				return 'Client submit SID in bad format.';
			}
			
			//and this SID should be in the database, and...
			$sql = $this->localDB->prepare('SELECT * FROM BW_Session WHERE SessionID = ?');
			$sql->bindValue(1, $_COOKIE['SessionID'], PDO::PARAM_STR);
			$sql->execute();
			$session = $sql->fetch();
			$sql->closeCursor();
			if (!$session) {
				return 'Client submit SID cannot match with session database.';
			}

			//and this SID should not be expired, and...
			if ($session['Expire']) #1 = expire, 0 = OK
				return 'SID expired at'.$session['LastUsed'].'.';

			//and this SID should not be timeout (1hr) (In this case, expire it)
			$sessionCreateTime = new DateTime($session['LastUsed'], new DateTimeZone('UTC'));
			$inactiveTime = time() - $sessionCreateTime->getTimestamp();
			if ($inactiveTime > 3600) {
				$sql = $this->localDB->prepare('UPDATE BW_Session SET Expire = 1 WHERE SessionID = ?'); #Expire it
				$sql->bindValue(1, $session['SessionID'], PDO::PARAM_STR);
				$sql->execute();
				$sql->closeCursor();
				return 'SID inactive since'.$session['LastUsed'].', '.$inactiveTime.' seconds ago.';
			}
			
			//...then, this is a valid SID. Update (renew) the LastUsed field
			$sql = $this->localDB->prepare('UPDATE BW_Session SET LastUsed = CURRENT_TIMESTAMP WHERE SessionID = ?');
			$sql->bindValue(1, $session['SessionID'], PDO::PARAM_STR);
			$sql->execute();
			$sql->closeCursor();

			return [ $session['SessionID'], $session['Username'], $session['JSKey'], $session['Salt'] ];
		}

		//Generate a new session
		private function createSession() {
			$jskey = base64_encode(random_bytes(192)); #256 characters
			$salt = base64_encode(random_bytes(192));
			$sid = null;

			$sql = $this->localDB->prepare('INSERT INTO BW_Session (SessionID, JSKey, Salt) VALUES (?,?,?)');
			$sql->bindParam(1, $sid, PDO::PARAM_STR); #By reference
			$sql->bindValue(2, $jskey, PDO::PARAM_STR);
			$sql->bindValue(3, $salt, PDO::PARAM_STR);
			while (!$sid) { #Retry if SessionID is not unique
				try {
					$sid = base64_encode(random_bytes(96)); #128 characters
					$sql->execute();
					$sql->closeCursor();
				} catch(Exception $e) {
					if (strpos($e->getMessage(), 'UNIQUE constraint failed'))
						$sid = null; #Run the while loop again
					else {
						http_response_code(500);
						if (DEBUGMODE)
							die('Bearweb init error: Local DB: '.$e->getMessage());
						else
							die('Bearweb error');
					}
				}
			}

			return [$sid, null, $jskey, $salt];
		}
		

		/******************************************************************************/
		//Connect to remote database (MySQL) and fetch server config
		/******************************************************************************/
		public function init() {
			$this->initRemoteDB();
			$this->getServerConfig();

			/* STAGE-II: Remote database init: Now, we can start to read/write to remote database. Transaction control is NOT ready. Auto-commit mode. */
		}

		//Connect to remote database
		protected function initRemoteDB() {
			try {
				$this->remoteDB = new PDO(
					'mysql:dbname='.DB_NAME.';host='.DB_HOST.';charset=UTF8',
					DB_USERNAME,
					DB_PASSWORD,
					array(
						PDO::ATTR_PERSISTENT			=> false,
						PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> false
					)
				);
				$this->remoteDB->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
				$this->remoteDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			} catch(PDOException $e) {
				http_response_code(500);
				if (DEBUGMODE)
					die('Bearweb init error: Remote DB: '.$e->getMessage());
				else
					die('Bearweb error');
			}

			$this->log('Connection to MySQL server established.');
		}

		//Fetch server config and status
		protected function getServerConfig() {
			try {
				$sql = $this->remoteDB->prepare('CALL BW_Config_Get(?)');
				$sql->bindValue(1, SITENAME, PDO::PARAM_STR);
				$sql->execute();
				$config = $sql->fetchAll();
				$sql->closeCursor();
			} catch(Exception $e) {
				http_response_code(500);
				if (DEBUGMODE)
					die('Bearweb init error: Remote DB: '.$e->getMessage());
				else
					die('Bearweb error');
			}

			$this->server = array();
			foreach ($config as $x)
				$this->server[ $x['Key'] ] = $x['Value'];

			if ( array_key_exists('Closed', $this->server) && $this->server['Closed'] ) { #Use array_key_exists because this setting can be missing
				http_response_code(503);
				die('<html><h1>Server offline</h1><p>'.$server['Closed'].'</p></html>');
			}

			$this->log('Server is good to go.');
		}


		/******************************************************************************/
		//Process request: Get and record request URL and multilingual info, get user info if session signed in, fetch resource from BW_Sitemap, determine client privilege against this resource
		/******************************************************************************/
		public function processRequest() {
			$this->processURL($_GET[URL_PARAM]);
			$this->getUser();
			$this->fetchSitemap();
			$this->processSitemap();
		}

		//Process request URL, get URL and multilingual info (region and language) from request URL, record in transaction
		protected function processURL($rawurl) {
			$this->url = ltrim(trim($rawurl),'/');
			$this->region = $this->language = ''; #Default
			
			if (!Checker::url($this->url))
				throw new BW_ClientError(400,'Request URL contains invalid character.');
			
			//Multilingual determine (2 char ISO639-1 + [optional 2 char ISO3166-1Alpha2])
			if (preg_match('/^[A-Za-z]{2}\//', $this->url)) {
				$this->language = strtolower(substr($this->url,0,2));
				$this->url = substr($this->url,3);
			}
			else if (preg_match('/^[A-Za-z]{2}\-[A-Za-z]{2}\//', $this->url)) {
				$this->language = strtolower(substr($this->url,0,2));
				$this->region = strtolower(substr($this->url,3,2));
				$this->url = substr($this->url,6);
			}

			$sql = $this->localDB->prepare('UPDATE BW_Transaction SET URL = ? WHERE TransactionID = ?');
			$sql->bindValue(1, $this->url, PDO::PARAM_STR);
			$sql->bindValue(2, $this->transactionID, PDO::PARAM_STR);
			$sql->execute();
			$sql->closeCursor();

			$this->log('URL OK. Language('.$this->language.') Region('.$this->region.')');
		}

		//Fetch user info from BW_User based on session user
		protected function getUser() {
			$this->username = null; #Default: not login, visitor
			$this->user = array();
			$this->userIsAdmin = false;

			if (!$this->sessionUser) { #Session is not associated to any user (sessionUser may be '' or null)
				$this->log('No username associated with this session.');
				return;
			}

			try {
				$sql = $this->remoteDB->prepare('CALL BW_User_get(?)');
				$sql->bindValue(1, $this->sessionUser, PDO::PARAM_STR);
				$sql->execute();
				$result = $sql->fetch();
				$sql->closeCursor();
				if (!$result)
					throw new BW_WebServerError(500, 'BW_Session username mismatched with BW_User table.');
				
				$this->username = $result['Username'];
				$this->user = $result;
			} catch (PDOException $e) {
				throw new BW_DatabaseServerError(500, 'Fail to fetch user info: '.$e->getMessage());
			}

			$this->user['Group'] = explode(',',$user['Group']);
			foreach ($this->user['Group'] as &$x)
				$x = trim($x);
			unset($x);

			if (in_array('Admin', $this->user['Group']))
				$this->userIsAdmin = true;
			
			//Update user last active
			try {
				$sql = $this->remoteDB->prepare('CALL BW_User_active(?)');
				$sql->bindValue(1, $this->username, PDO::PARAM_STR);
				$sql->execute();
				$sql->closeCursor();
			} catch (PDOException $e) {
				throw new BW_DatabaseServerError(500, 'Fail to update BW_User.LastActive: '.$e->getMessage());
			}

			$this->log('User info fetched, user name = '.$this->username);
		}

		protected function fetchSitemap() {
			$sql = $this->remoteDB->prepare('CALL BW_Sitemap_get(?,?,?,?)');
			$sql->bindValue(1, SITENAME, PDO::PARAM_STR);
			$sql->bindValue(2, $this->url, PDO::PARAM_STR);
			$sql->bindValue(3, null, PDO::PARAM_NULL);
			$sql->bindValue(4, null, PDO::PARAM_NULL);
			$sql->execute();
			$this->sitemap = $sql->fetch();
			$sql->closeCursor();
			/* BW_Sitemap_get:
			 * Site: Apache param SITENAME, or NULL for ALL
			 * URL: URL w/o location info, select * using LIKE, or NULL for ALL
			 * Category: String array split using ",", or NULL for ALL
			 * Status: Char array, FIND_IN_SET, or NULL for ALL
			*/
			
			if (!$this->sitemap)
				throw new BW_ClientError(404,'URL resource not found..');

			$this->sitemap['Info'] = json_decode($this->sitemap['Info'], true);
			$this->log('Sitemap fetched. Status '.$this->sitemap['Status']);
		}

		protected function processSitemap() {
			switch($this->sitemap['Status']) {
			  case 'R': #Page removed permanently
			  case 'r': #Page removed temporarily
				if ( !isset($this->sitemap['Info']['Redirect']) || !is_string($this->sitemap['Info']['Redirect']) )
					throw new BW_WebServerError(500, 'Redirect info missed (BW_Sitemap.Info.Redirect).');
				
				$urlLocation = trim($this->language.'-'.$this->region, '-');
				if ($urlLocation)
					$urlLocation .= '/';
				$redirect = $urlLocation.$this->sitemap['Info']['Redirect'];
				
				header('Location: /'.$redirect); #Let's just throw a BW_ClientError in this case to make this quick and dirty, no special code required.
				throw new BW_ClientError($this->sitemap['Status'] == 'R' ? 301 : 302, 'Page redirected to: '.$redirect);
				
				break;
			
			  case 'A': #Auth need (privilege)
				if (!$this->username)
					throw new BW_ClientError(401, 'Access denied: Auth required. Resurce is privilege protected, please login first.');
				
				$whiteGroup = []; $whiteUser = []; 
				try {
					$whiteGroup = $this->sitemap['Info']['Whitelist']['Group'];
					$whiteUser = $this->sitemap['Info']['Whitelist']['Username'];
				} catch(Exception $e) {
					/* If no good whitelist given, whitelist remains empty */
				}
				
				if (
					!$this->userIsAdmin &&
					!in_array($this->user['UserInfo']['Username'], $whiteUser) &&
					!count(array_intersect($this->user['UserInfo']['Group'], $whiteGroup))
				) {
					throw new BW_ClientError(403,'Access denied: You don\'t have privilege to view this resource.');
				}
				break;
			
			  case 'P': #Pending page
				if (
					!$this->username ||
					(
						!$this->userIsAdmin &&
						$client['UserInfo']['Username'] != $this->sitemap['Author']
					)
				) {
					throw new BW_ClientError(403,'Access denied: pending page. Page is locked/pending, only admin and the author have the privilege to access this resource, please auth first.');
				}
				break;
			
			  default:
				/* No action required
				 * O: OK, Normal resource
				 * C: Construction
				 * D: Deprecated
				 * S: Special, SEO no index
				*/
			}
			
			//Sendresource misc headers
			if (!$this->sitemap['LastModify']) {
				header('Last-Modified: '.date('D, j M Y G:i:s').' GMT');
				header('Etag: '.base64_encode(random_bytes(24))); 
			}
			else {
				header('Last-Modified: '.date('D, j M Y G:i:s',strtotime($this->sitemap['LastModify'])).' GMT');
				header('Etag: '.trim( base64_encode($this->sitemap['LastModify']),"= \t\n\r\0\x0B") );
			}
		}


		/******************************************************************************/
		//Finalize transaction, analysis transaction, free database connections
		/******************************************************************************/
		public function __destruct() {
			$timeUsed = ( microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000000; #Script execution time in micro second
			$sql = $this->localDB->prepare('UPDATE BW_Transaction SET ExecutionTime = ?, Status = ? WHERE TransactionID = ?');
			$sql->bindValue(1, $timeUsed, PDO::PARAM_STR);
			$sql->bindValue(2, http_response_code(), PDO::PARAM_STR);
			$sql->bindValue(3, $this->transactionID, PDO::PARAM_STR);
			$sql->execute();
			$sql->closeCursor();

			$this->remoteDB = null;
			$this->localDB = null;
		}

		

		//Print error page (using the HTML page template)
		public function useErrorTemplate($errorMessage) {
			$this->log('[ERROR]Pass control to ErrorTemplate: '.$errorMessage);

			//Rollback transaction on remote database
			try {
				$this->remoteDB->rollback();
				$this->log('[DBMS][RemoteDB] Transaction rollback.');
			} catch(PDOException $e) {
				/* Transaction not init yet (error throw before execute template), no action required */
			}
			
			//Create dummy BW class for error template
//			$this->url = '_error';
			$this->sitemap = array( #Dummy page info
				'Site'		=> '', #Using error page in BW_Webpage (All site share the same error template)
				'URL'		=> '_error',
				'Category'	=> 'Error',
				'TemplateMain'	=> 'page', #Using error template
				'TemplateSub'	=> 'error',
				'Author'	=> null,
				'CreateTime'	=> null,
				'LastModify'	=> null,
				'Copyright'	=> null,
				'Status'	=> 'S', #Special page: SEO: no-index
				'Info'		=> array(
					'ErrorInfo'	=> $errorMessage #Passing error info to template
				)
			);

			$templateMain = './template/'.$this->sitemap['TemplateMain'].'.php';
			$templateSub = './template/'.$this->sitemap['TemplateMain'].'_'.$this->sitemap['TemplateSub'].'.php';
			
			//Execute error template, NOTICE: Template can access protected/private variables of BW when executing error template
			try {
				global $BW;
				include $templateMain;
				$this->log('Error template executed.');
			} catch(Exception $e) {
				ob_clean(); ob_start(); #In case there is error in the error template, clean buffer, do not show script error to client
				$this->log('Fail to execute error template: '.$e->getMessage());
				$this->log('Print orginal error message in plain text.');
				echo $errorMessage;
			}
		}
	}
?>
