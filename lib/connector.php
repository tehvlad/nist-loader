<?php

error_reporting(0);

class mysqlConnectorException extends Exception {}

class mysqlConnector {

	private $_sqlHost = '';
	private $_sqlUsername = '';
	private $_sqlDbName = '';
	private $_sqlPass = '';		
	private $_dataSources = array();
	private $_miner;	
	public $_debug;

	private $_afected_rows = 0;
	
	private function makeConn($_host, $_db, $_user, $_pass) {
		if (empty($_host)) {
            throw new mysqlConnectorException('Host server not defined');
        }
		if (empty($_db)) {
            throw new mysqlConnectorException('Database name not defined');
        }
		
		if (empty($_user)) {
            throw new mysqlConnectorException('Database username not defined');
        }
		
		if (empty($_pass)) {
            throw new mysqlConnectorException('Database password not defined');			
        }
		
		
		$this->_miner = new mysqli($_host,  $_user, $_pass, $_db);
		if ($this->_miner->connect_errno) {
			throw new mysqlConnectorException("Connection creation error (" . $this->miner->connect_errno .")");	
		}
	
	}
	
	public function test() {
		
        var_dump(get_object_vars($this));
    }
	
	public function mysqlConnector($_host, $_db, $_user, $_pass) {
	
		$this->_miner = $this->makeConn($_host, $_db, $_user, $_pass);
		
		if ($this->_miner->connect_errno) {
			throw new mysqlConnectorException($_miner->connect_error);						
		}		
		
		$this->_dataSources['root'] = array();
		$this->_dataSources['root'] = array( 'host' =>$_host, 'user' => $_user, 'pass' => $_pass, 'db' => $_db);

		// Dispose, no need of extra dbs
		
/*
		$_extraSources = $this->queryArray('root', 'SELECT * FROM datasource where activo = 1', null);
		$c = count($_extraSources);
		if ($c > 0) {
			for($i = 0; $i < $c; $i++) {
				$index = $_extraSources[$i]['descriptor'];
				$this->_dataSources[$index] = array( 
					'host' => $_extraSources[$i]['servidor'], 
					'user' => $_extraSources[$i]['usuariodb'], 
					'pass' => $_extraSources[$i]['passwd'], 
					'db' => $_extraSources[$i]['nombredb']);
			}
		}
	*/

		$this->_debug = '<pre> ' . print_r($this->_dataSources , true) . " -> $c" . '</pre>';
		
		return true;
		
		
	}
		
	private function extractParam($_source, $_sourceName, $_wildCard = 0) {
	
	
		if ($_wildCard == 0) {
			if ($_source == 'g' ) {
				return $_GET[$_sourceName];
			} else if ($_source == 'p' ) {
				return $_POST[$_sourceName];
			} else if ($_source == 's' ) {
				return $_SESSION[$_sourceName];
			} else if ($_source == 'c' ) {
				return $_COOKIE[$_sourceName];
			} else if ($_source == 'f' ) {
				return $_sourceName;
			} else if ($_source == 'd' ) {				
				// $dataItemName = 'data[' . $_sourceName . ']';
			  return $_REQUEST['data'][ $_sourceName];
			} else if ($_source == 'r' ) {	
				return $_REQUEST['data'][ $_sourceName];
			} 

			else {
				return NULL;
			}
		} else {
			
			if ($_source == 'g' ) {
				$temp = $_GET[$_sourceName];
			} else if ($_source == 'p' ) {
				$temp =  $_POST[$_sourceName];
			} else if ($_source == 's' ) {
				$temp =  $_SESSION[$_sourceName];
			} else if ($_source == 'c' ) {
				$temp =  $_COOKIE[$_sourceName];
			} else if ($_source == 'f' ) {
				$temp =  $_sourceName;
			} else if ($_source == 'd' ) {				
				//$dataItemName = 'data[' . $_sourceName . ']';
				$temp = $_REQUEST['data'][$_sourceName];
			}  else {
				return NULL;
			}			
			
			if ($_wildCard  == 1) {
				$final = '%' . $temp;
			} else if ($_wildCard  == 2) {
				$final 	= $temp . '%';
			} else if ($_wildCard  == 3) {
				$final 	='%' . $temp . '%';
			} else {
				$final 	= $temp;
			}			
			return $final;		
			
		}
	
	}
		
	private function processParams($_params) {
		
		$dataParams = array();	

			// Nothing to do
		if (strlen($_params) == 0 ) {
			$dataParams['datatypes'] = '';
			$dataParams['counter'] = 0;
			
			return $dataParams;
		}	

		
		$items = explode(";", $_params);				
		$c= -1;
		
		$stringError = "";		
		$paramsCounter = count($items);		
		$dataParams['counter'] = $paramsCounter;
		
		
		
		
		$dataTypes = "";
		
		for($i = 0; $i < $paramsCounter; $i++){
			$subItems = explode(":", $items[$i]);					
			$count = count($subItems);			
			
			// Source
			// Type, Value
			// Name
			// WildCard
			
			if ( $count == 3 ) {			
				// El parametro tiene 3 partes: Fuente, Tipo, Nombre
				
				$value = $this->extractParam($subItems[0], $subItems[2]);	
				$setParams = array(  $subItems[0], $subItems[1], $subItems[2], $value);						
				$dataTypes .= $subItems[1];
				
			} else if ( $count == 2 ) {			
				// El parametro tiene 2 partes: Fuente, Valor				
				$value = $this->extractParam($subItems[0], $subItems[1]);	
				$setParams = array(  $subItems[0], 's', $subItems[1],  $value);	
				$dataTypes .= 's';				
				
			} else if ( $count == 4 ) {
				// El paramtro tiene 4 partes: Fuente, Tipo, Nombre, Comodin: 0 Sin comodin 1 al principio, 2 al final, 3 principio y final %
				$value = $this->extractParam($subItems[0], $subItems[2], $subItems[3]);	
				$setParams = array(  $subItems[0], $subItems[1], $subItems[2], $value);	
				$dataTypes .= $subItems[1];
			} else {
				$stringError .= ' DataParam error at param ' . $i . ': Too few params ['  . $items[$i] . ']';
				$stringError .= '<pre>' . print_r($subItems, true) . '</pre> ' . $count;
				$dataTypes .= 's';	
				$value = '';
			}
			
			$dataParams[$i] = $value;		
		
		}
		
		$dataParams['datatypes'] = $dataTypes;
		
		if (strlen($stringError) > 0 ) { $dataParams['error'] = $stringError; }
		return $dataParams;
	
	}
		
	private function setSource($source) {
	
		if( !isset($this->_dataSources[$source]) ) {
            throw new mysqlConnectorException('Datasource not found -> [' . $source . ']');			
		} else {
			
			$this->_sqlHost = $this->_dataSources[$source]['host'];
			$this->_sqlDbName = $this->_dataSources[$source]['db'];
			$this->_sqlUsername= $this->_dataSources[$source]['user'];
			$this->_sqlPass = $this->_dataSources[$source]['pass'];
		}
	
	
	}
	
	public function execScript($source, $_sqlScript) {
		$this->setSource($source);	
		$mysqli = new mysqli($this->_sqlHost,  $this->_sqlUsername, $this->_sqlPass, $this->_sqlDbName);
		$mysqli->set_charset("utf8"); 
		$mysqli->query("SET lc_time_names = 'es_MX'");
		if (!$mysqli->multi_query($_sqlScript)) {
			throw new mysqlConnectorException('SQL Query -(' .  $mysqli->error . ')');	
		    //return "Multi query failed: (" . $mysqli->errno . ") " . $mysqli->error;
		 }
		 $_afected_rows = $mysqli->affected_rows; 
		 return true;
	}

	public function execQuery($source, $_sql, $_params) {

			$this->setSource($source);				
			$mysqli = new mysqli($this->_sqlHost,  $this->_sqlUsername, $this->_sqlPass, $this->_sqlDbName);
			if ($mysqli ->connect_errno) {
				throw new mysqlConnectorException("Connection creation error (" . $mysqli->connect_error .")");	
			}		
			$mysqli->set_charset("utf8"); 
			$mysqli->query("SET lc_time_names = 'es_MX'");
			
			//Data extraction for parameters
			$_queryParams = $this->processParams($_params);		
			if (isset($_queryParams['error'])) {
				throw new mysqlConnectorException('Error -> ' . $_queryParams['error']);	
			}		
			// Creation of new array to process as a query;		
			$bindValueArray[] = &$_queryParams['datatypes'];
			$numItems = $_queryParams['counter'];
			
			if ($numItems == 0 ) {
				$dummy = '';
				$bindValueArray[] = &$dummy;
			} else {
				for($i =0; $i<$numItems; $i++) { $bindValueArray[] = &$_queryParams[$i]; }
			}
			
			if( $_consulta = $mysqli -> prepare( $_sql ) ) {

				if ($numItems > 0 ) { call_user_func_array(array($_consulta, 'bind_param'), $bindValueArray); }			
				$testQuery = $_consulta->execute();

				$_afected_rows = $mysqli->affected_rows; 
				
				if ($testQuery === false) {
					throw new mysqlConnectorException('SQL Query -(' .  $_consulta->error . ')');	
				}			
			
			} else {
				throw new mysqlConnectorException('SQL Error -(' . $mysqli->errno . ":" . $mysqli->error );	
			}
			
			return $_consulta->insert_id;
	}		
	
	public function queryArray($source, $_sql, $_params, $nomenclatura = MYSQLI_ASSOC) {
	
		$this->setSource($source);		
		$dataArray = array();		
		
		$mysqli = new mysqli($this->_sqlHost,  $this->_sqlUsername, $this->_sqlPass, $this->_sqlDbName);
		//$mysqli = &$this->_miner;
		if ($mysqli ->connect_errno) {
			throw new mysqlConnectorException("Connection creation error (" . $mysqli->connect_error .")");	
		}		
		$mysqli->set_charset("utf8"); 
		//Data extraction for parameters
		$_queryParams = $this->processParams($_params);		
		if (isset($_queryParams['error'])) {
			throw new mysqlConnectorException('Error -> ' . $_queryParams['error']);	
		}				
		// Creation of new array to process as a query;		
		$bindValueArray[] = &$_queryParams['datatypes'];
		$numItems = $_queryParams['counter'];
		
		if ($numItems == 0 ) {
			$dummy = '';
			$bindValueArray[] = &$dummy;
		} else {
			for($i =0; $i<$numItems; $i++) {
				$bindValueArray[] = &$_queryParams[$i];
			}
		}
			
		if( $_consulta = $mysqli -> prepare( $_sql ) ) {
			
			if ($numItems > 0 ) { call_user_func_array(array($_consulta, 'bind_param'), $bindValueArray); }			
			
			$testQuery = $_consulta->execute();	
			$_afected_rows = $mysqli->affected_rows; 
					
			if ($testQuery === false) {
				throw new mysqlConnectorException('SQL Query -(' .  $_consulta->error . ')');	
			} else {
							
				$res = $_consulta->get_result();
				while($row = $res->fetch_array($nomenclatura)) {
					array_push($dataArray, $row);
				}
			
			}
		
		} else {
			throw new mysqlConnectorException('SQL Error -(' . $mysqli->errno. ":" . $mysqli->error );	
		}
		
		return $dataArray;
	
	}

	
	public function queryJson($_con, $_sql, $_params, $nomenclatura = MYSQLI_ASSOC) {
	
		if ($nomenclatura == 'ampCore' ) {
			$dataArray = $this->queryArray($_con, $_sql, $_params);
			
			$json = "[";
			
						
			$first = true;
			foreach($dataArray as $key => $row) {
			
				$c = -1;
								
				if (!$first) {  $json .= ","; }
				
				$json .= "{";
				
				foreach($row as $value) {
					$c++;
					if ($c > 0) { $json .= ", "; }
					$json .= "'$c':";
					
					if (is_int($value)) {
						$json .= $value;
					} else {
						//$json .= "'" . htmlspecialchars($value, ENT_QUOTES) . "'" ;
						$json .= json_encode($value);
					}
				}
				
				$first = false;
				$json .= "}";
			}
			
			$json .= "]";

			//$dataArray = $this->queryArray($_con, $_sql, $_params);

		} else {
			$dataArray = $this->queryArray($_con, $_sql, $_params, $nomenclatura);
			$json = json_encode($dataArray);
		}
				
		return $json;
	}


	function get_affected_rows() {
		return $_afected_rows;
	}


	
	function __destruct() {
      //mysqli_close ($this->_miner);
    }

	

}



?>
