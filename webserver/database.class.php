<?php
	//Base database util
	class Database {
		
		private $db; #Database connection resource
		
		//Constructor function, connect to database
		function __construct($dbname,$dbhost,$dbuser,$dbpass) {
			try {
				$this->db = new PDO(
					'mysql:dbname='.$dbname.';host='.$dbhost.';charset=UTF8',
					$dbuser,
					$dbpass,
					array(
						PDO::ATTR_PERSISTENT			=> false,
						PDO::MYSQL_ATTR_USE_BUFFERED_QUERY	=> false
					)
				);
				$this->db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500,'Cannot connect to database. '.$e->getMessage());
			}
		}
		
		//Execute procedure / query
		protected function call($procedure,$param,$return=false) {
			try {
				$this->bindParamType($param);
				$sql = $this->db->prepare('CALL '.$procedure);
				foreach($param as $key=>$value)
					$sql->bindParam(':'.$key,$value[0],$value[1]);
				$sql->execute();
				$data = $return ? $sql->fetchAll(PDO::FETCH_ASSOC) : null;
				$sql->closeCursor();
				return $data;
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500,'Fail to execute procedure "'.$procedure.'". '.$e->getMessage().'.');
			}
		}
		
		protected function query($query,$param,$return=false) {
			try {
				$this->bindParamType($param);
				$sql = $this->db->prepare($query);
				foreach($param as $key=>$value)
					$sql->bindParam(':'.$key,$value[0],$value[1]);
				$sql->execute();
				$data = $return ? $sql->fetchAll(PDO::FETCH_ASSOC) : null;
				$sql->closeCursor();
				return $data;
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500,'Fail to execute query "'.$query.'". '.$e->getMessage().'.');
			}
		}
		
		private function bindParamType(&$param) {
			foreach ($param as $key => &$value) switch (gettype($value)) {
				case 'boolean':	$value = [$value,PDO::PARAM_BOOL]; break;
				case 'integer':	$value = [$value,PDO::PARAM_INT]; break;
				case 'double':	$value = [strval($value),PDO::PARAM_STR]; break;
				case 'string':	$value = [$value,PDO::PARAM_STR]; break;
				case 'NULL':	$value = [$value,PDO::PARAM_NULL]; break;
				case 'resource':$value = [$value,PDO::PARAM_LOB]; break;
				default:	throw new BW_DatabaseServerError(500,'Param type not supported.');
			}
		}
		
		//Transcation control
		protected function begin() {
			try {
				$this->db->beginTransaction();
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500,'Fail to begin transaction: '.$e->getMessage());
			}
		}
		protected function commit() {
			try {
				$this->db->commit();
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500,'Fail to commit transaction: '.$e->getMessage());
			}
		}
		protected function cancel() {
			try {
				$this->db->rollback();
			} catch(PDOException $e) {
				throw new BW_DatabaseServerError(500,'Fail to rollback transaction: '.$e->getMessage());
			}
		}
		
	}
	
	
	//Bearweb database util
	class BearwebDatabase extends Database {
		
		private $taskOrder = 0;
		
		function __construct() {
			parent::__construct(DB_NAME,DB_HOST,DB_USERNAME,DB_PASSWORD);
			parent::begin();
			writeLog('Database interface created. Transaction session initialized.');
		}
		
		final public function call($procedure,$param,$return=false) {
			writeLog('Executing procedure: '.$procedure);
			
			//Create SQL command
			$paramSet = array();
			foreach ($param as $key => $value)
				array_push($paramSet,':'.$key);
			$sendProce = $procedure.'('.implode(',',$paramSet).')';
			writeLog('Send: '.$sendProce);
			
			//Call the procedure
			$data = parent::call($sendProce,$param,$return);
			writeLog('Procedure executed.');
			return $data;
		}
		
		final public function query($query,$param,$return=false) {
			throw new BW_DatabaseServerError(500,'SQL query not allowed for security reason. Using store procedure!');
		}
		
		/*
		Framework level:
		The BW will init a transaction and commit when the script ends.
		Therefore, framework level db transaction (eg. log) will ALWAYS be writen.
		Application level:
		The application should init a savepoint at the beginning of a work cluster.
		If OK, commit (forget the savepoint); otherwise, rollback to that savepoint.
		*/
		
		
		final public function begin() {
			parent::query('SAVEPOINT X'.$this->taskOrder,array());
			writeLog($this->taskOrder.': Database application transaction init.');
		}
		final public function commit() {
			$this->taskOrder++;
			writeLog($this->taskOrder.': All changes to database buffered.');
		}
		final public function cancel() {
			parent::query('ROLLBACK TO X'.$this->taskOrder,array());
			writeLog($this->taskOrder.': All changes to database discarded.');
		}
		
		function __destruct() {
			parent::commit();
		}
		
	}
?>