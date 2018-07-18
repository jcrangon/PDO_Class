<?php
class PDOcnx{
	private $_driver;
	private $_serveur;
	private $_base;
	private $_port;
	private $_user;
	private $_pass;
	private $_charset;
	private $_options=array();
	
	private $_PDO_object;
	private $_is_connected;
	private $_PDOError;
	private $_logStmt;
	private $_loggit;
	
	public function __construct(array $dbdata){
		//hydratation de la classe
		$this->hydrate($dbdata);
		// log
		$this->setLoggit(1); // 1 - active le log / 0 - désactive le log
		// initialisations
		$this->init_PDOobject();
		$this->init_logStmt();
		$this->init_is_connected();
		$this->init_PDOError();
	}
	
		//Initers
	protected function init_PDOobject(){
		$this->_PDO_object=null;;
	}
	protected function init_logStmt(){
		if($this->_loggit===1){$this->_logStmt="** PDOcnx Object Instanciation **\n";}
	}
	protected function init_is_connected(){
		$this->_is_connected=false;
	}
	protected function init_PDOError(){
		$this->_PDOError="";
	}
	protected function init_loggit($status){
		$this->_loggit=$status;
	}
	
		//setters
	public function setDriver($driver){
		$this->_driver=$driver;
	}
	public function setServeur($serveur){
		$this->_serveur=$serveur;
	}
	public function setBase($base){
		$this->_base=$base;
	}
	public function setPort($port){
		$this->_port=$port;
	}
	public function setUser($user){
		$this->_user=$user;
	}
	public function setPass($pass){
		$this->_pass=$pass;
	}
	public function setCharset($charset){
		$this->_charset=$charset;
	}
	public function setOptions($options){
		$this->_options=$options;
	}
	public function setLoggit($status){
		$this->_loggit=$status;
	}
	
	//hydrate
	public function hydrate(array $donnees)
	{
		foreach ($donnees as $key => $value)
		{
			// On récupère le nom du setter correspondant à l'attribut.
			$method = 'set'.ucfirst($key);

			// Si le setter correspondant existe.
			if (method_exists($this, $method))
			{
				// On appelle le setter.
				$this->$method($value);
			}
		}
	}
	
	//getters
	public function getDriver(){
		return $this->_driver;
	}
	public function getServeur(){
		return $this->_serveur;
	}
	public function getBase(){
		return $this->_base;
	}
	public function getPort(){
		return $this->_port;
	}
	public function getUser(){
		return $this->_user;
	}
	public function getPass(){
		return $this->_pass;
	}
	public function getCharset(){
		return $this->_charset;
	}
	public function getOptions(){
		return $this->_options;
	}

	public function isConnected(){
		return $this->_is_connected;
	}
	public function getPDOobject(){
		return $this->_PDO_object;
	}
	public function getPDOError(){
		return $this->_PDOError;
	}
	public function getLogStmt(){
		$temp= $this->_logStmt;
		$this->_logStmt="";
		return $temp;
	}
	public function getLoggit(){
		return $this->_loggit;
	}
	
	// Methods
	public function connexion(){
		if($this->_is_connected===false){
			try{
				$dsn= $this->_driver .':host='. $this->_serveur .';port='.$this->_port.';dbname='. $this->_base.';charset='.$this->_charset;
				$this->_PDO_object = new PDO(
					$dsn,
					$this->_user,
					$this->_pass,
					$this->_options
				);
				if($this->_loggit===1){$this->_logStmt.="** Connection Successful **\n";}
				$this->_is_connected=true;
			}
			catch(PDOException $e){
				$this->_PDOError=$e->getMessage();
				// permet ensuite de recuperer $db->getPDOErr() et le passer au logger
				if($this->_loggit===1){$this->_logStmt.="Connection error : \n".$e->getMessage();}
			}
		}
	}
	
	
	public function get_handle(){
		if($this->_is_connected===true){
			if($this->_loggit===1){$this->_logStmt.="** Handle transmission **\n";}
			return $this->getPDOobject();
		}
		else{
			if($this->_loggit===1){$this->_logStmt.="** Handle transmission Failed - no conexion found **\n";}
			return false;
		}
	}


	public function closecnx(){
		$this->_PDO_object=null;
		$this->_is_connected=false;
		if($this->_loggit===1){$this->_logStmt.="** Connection has been closed **\n";}
	}
	
	
	
	protected function bind(&$stmt,$tabparam){
		foreach($tabparam as $param){
			if(isset($param[2])){
				$stmt->bindvalue($param[0],$param[1],$param[2]);
			}
			else{
				switch (true) {  
					case is_int($param[1]):  
					$type = PDO::PARAM_INT;  
					break;  
					case is_bool($param[1]):  
					$type = PDO::PARAM_BOOL;  
					break;  
					case is_null($param[1]):  
					$type = PDO::PARAM_NULL;  
					break; 
					default:  
					$type = PDO::PARAM_STR;
				}
				$stmt->bindvalue($param[0],$param[1],$type);
			}
		}
	}
	
	protected function getFieldsInfos($tab){
		if(!is_array($tab)){
			return false;
		}
		$i=0;
		$tabresult=array();
		foreach($tab as $k=>$v){
			$tabresult[0][]=$k;
			$i++;
		}
		$tabresult[1]=$i;
		return $tabresult;
	}
	
	protected function getStmt($query,$tabparam){
		$stmt=$this->_PDO_object->prepare($query);
		if(sizeof($tabparam)>0){
			$this->bind($stmt,$tabparam);
		}
		$stmt->execute();
		return $stmt;
	}
	
	public function free_result(&$stmt){
		return $stmt->closeCursor();
	}
	
	public function query($query, $tabparam, $fetchtype=PDO::FETCH_ASSOC,$stmtOnly=false){
		if($this->_is_connected===false){$this->connexion();}
		try{
			$query = trim(str_replace("\r", " ", $query));
			$query=preg_replace("/\s+|\t+|\n+/", " ", $query);
			
			$cleanquery = explode(" ",$query);
			$querytype = strtolower($cleanquery[0]);
			
			if($this->_loggit===1){$this->_logStmt.="req =  ".$query;}
			
			if ($querytype === 'select' || $querytype === 'show') {
				$stmt=$this->getStmt($query,$tabparam);
				if($stmtOnly===true){
					return $stmt;
				}
				$data["result"]= $stmt->fetchAll($fetchtype);
				$data["rowcount"]=$stmt->rowCount();
				$data["closing"]=$this->free_result($stmt);
				if($fetchtype===PDO::FETCH_ASSOC && $data["rowcount"]!==0){
					$tabfields=$this->getFieldsInfos($data["result"][0]);
					if($tabfields!==false){
						$data["fieldnames"]=$tabfields[0];
						$data["fieldcount"]=$tabfields[1];
					}
				}
				return $data;
			}
			elseif ($querytype === 'insert' || $querytype === 'update' || $querytype === 'delete') {
				$stmt=$this->getStmt($query,$tabparam);
				$data["rowcount"]=$stmt->rowCount();
				return $data;
			}
			elseif ($querytype === 'create') {
				$stmt=$this->_PDO_object->prepare($query);
				$data["result"]= $stmt->execute();
				return $data;
			}
			else{
				return NULL;
			}
		}
		catch(PDOException $e){
			$this->_PDOError=$e->getMessage();
			return false;
			if($this->_loggit===1){$this->_logStmt.="Query() error : \n".$e->getMessage();}
		}
	}
	
	public function execute($query,$tabparam,$fetchtype=PDO::FETCH_ASSOC,$stmtOnly=false){
		if($this->_is_connected===false){$this->connexion();}
		try{
			$query = trim(str_replace("\r", " ", $query));
			$query=preg_replace("/\s+|\t+|\n+/", " ", $query);
			
			$cleanquery = explode(" ",$query);
			$querytype = strtolower($cleanquery[0]);
			
			if ($querytype === 'select' || $querytype === 'show') {
				$stmt=$this->_PDO_object->prepare($query);
				$stmt->execute($tabparam);
				if($stmtOnly===true){
					return $stmt;
				}
				$data["result"]= $stmt->fetchAll($fetchtype);
				$data["rowcount"]=$stmt->rowCount();
				$data["closing"]=$this->free_result($stmt);
				if($fetchtype===PDO::FETCH_ASSOC && $data["rowcount"]!==0){
					$tabfields=$this->getFieldsInfos($data["result"][0]);
					if($tabfields!==false){
						$data["fieldnames"]=$tabfields[0];
						$data["fieldcount"]=$tabfields[1];
					}
				}
				return $data;
			}
			elseif ($querytype === 'insert' || $querytype === 'update' || $querytype === 'delete') {
				$stmt=$this->_PDO_object->prepare($query);
				$data["result"]= $stmt->execute($tabparam);
				$data["rowcount"]=$stmt->rowCount();
				return $data;
			}
			elseif ($querytype === 'create') {
				$stmt=$this->_PDO_object->prepare($query);
				$data["result"]= $stmt->execute();
				return $data;
			}
			else{
				return NULL;
			}
		}
		catch(PDOException $e){
			$this->_PDOError=$e->getMessage();
			return false;
			if($this->_loggit===1){$this->_logStmt.="Execute() error : \n".$e->getMessage();}
		}
	}
	
	public function column_query($query, $tabparam,$colIndex){
		if($this->_is_connected===false){$this->connexion();}
		try{
			$restab=array();
			$query = trim(str_replace("\r", " ", $query));
			$query=preg_replace("/\s+|\t+|\n+/", " ", $query);
			
			$cleanquery = explode(" ",$query);
			$querytype = strtolower($cleanquery[0]);
			
			if($this->_loggit===1){$this->_logStmt.="req =  ".$query;}
			
			if ($querytype === 'select' || $querytype === 'show') {
				$stmt=$this->getStmt($query,$tabparam);
				while($data=$stmt->fetch(PDO::FETCH_BOTH)){
					$restab[]=$data[$colIndex];
				}
				unset($data);
				$this->free_result($stmt);
				return $restab;
			}
			else{
				return NULL;
			}
		}
		catch(PDOException $e){
			$this->_PDOError=$e->getMessage();
			return false;
			if($this->_loggit===1){$this->_logStmt.="Query() error : \n".$e->getMessage();}
		}
	}
	
	public function column_execute($query, $tabparam,$colIndex){
		if($this->_is_connected===false){$this->connexion();}
		try{
			$restab=array();
			$query = trim(str_replace("\r", " ", $query));
			$query=preg_replace("/\s+|\t+|\n+/", " ", $query);
			
			$cleanquery = explode(" ",$query);
			$querytype = strtolower($cleanquery[0]);
			
			if($this->_loggit===1){$this->_logStmt.="req =  ".$query;}
			
			if ($querytype === 'select' || $querytype === 'show') {
				$stmt=$this->_PDO_object->prepare($query);
				$stmt->execute($tabparam);
				while($data=$stmt->fetch(PDO::FETCH_BOTH)){
					$restab[]=$data[$colIndex];
				}
				unset($data);
				$this->free_result($stmt);
				return $restab;
			}
			else{
				return NULL;
			}
		}
		catch(PDOException $e){
			$this->_PDOError=$e->getMessage();
			return false;
			if($this->_loggit===1){$this->_logStmt.="Query() error : \n".$e->getMessage();}
		}
	}
	
	public function column_from_dataset($dataset,$colIndex){
		$restab=array();
		foreach($dataset as $v){
			$restab[]=$v[$colIndex];
		}
		return $restab;
	}
	
	public function lastId(){
		return $this->_PDO_object->lastInsertId();
	}
	
	public function beginTn(){
		if($this->_loggit===1){$this->_logStmt.="Begin Transaction \n";}
		return $this->_PDO_object->beginTransaction();  
	} 
	public function endTn(){ 
		if($this->_loggit===1){$this->_logStmt.="End Of Transaction \n";}
		return $this->_PDO_object->commit();  
	}
	public function cancelTn(){
		if($this->_loggit===1){$this->_logStmt.="Rolling Back! \n";}
		return $this->_PDO_object->rollBack();  
	}
	
	public function debugDump($stmt){  
		return $stmt->debugDumpParams();  
	} 
	
}




?>
