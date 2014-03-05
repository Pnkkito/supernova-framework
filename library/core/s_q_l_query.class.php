<?php
/**
 * Supernova Framework
 */
/**
 * SQL Querys Handler 
 *
 * SQL Query contain all search functions for database querys and parsing
 *
 * @package MVC_Model_SQLQuery
 */
class SQLQuery {
	/**
	 * DB Handle
	 * @var object Manage active conection to database
	 */
	protected $_dbHandle;
	
	/**
	 * Model Name
	 * @var String
	 */
	protected $_model;
	
	/**
	 * Table name
	 * @var String
	 */
	protected $_table;
	
	/**
	 * Controller Vars
	 * @var Array This contain vars from called controllers (nested ones not work)
	 */
	protected $_controller = array();
	
	
	/**
	 * Pagination Vars
	 * @var object For pagination purposes.
	 */
	public $_totalPages;
	
	/**
	 * Store last inserted ID from Model
	 * @var Int
	 */
	public $_lastInsertedId;
    
	/**
	 * Store all SQL querys
	 * @var Array
	 */
	protected $_SQLDebug = array();

	// function __construct(){
		// $this->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	// }

	/**
	 * Connects to Database
	 * @param	string	$address	Host address
	 * @param	string	$username	Username
	 * @param	string	$password	Password
	 * @param	string	$database	Database name
	 * @return	boolean			
	 */
	public static function connect($address = DB_HOST, $username = DB_USERNAME, $password = DB_PASSWORD, $database = DB_DATABASE, $dbdriver = DB_DRIVER) {
		try {
			$dbn = $dbdriver.":host=".$address.";dbname=".$database;
			self::$sql = new PDO($dbn , $username, $password);
			self::$sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$sql->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			switch ($e->getCode()){
				case '0' : warning('Conection failed :: Check your conection parameters'); break;
				case '2002': warning('Conection failed :: Database <strong>Host</strong> incorrect'); break;
				case '1044': warning('Conection failed :: Database <strong>Username</strong> incorrect'); break;
				case '1045': warning('Conection failed :: Database <strong>Password</strong> incorrect'); break;
				case '1049': warning('Conection failed :: Database <strong>Name</strong> incorrect'); break;
			}
			return false;
		}
		return true;
	}
 
	/**
	 * SQL on destruct
	 * @ignore
	 */
	function __destruct() {
		// Debug all sql consults
		// $this->DebugSQL();
	}

	/**
	 * Returns SQL Querys on screen
	 * @ignore
	 */
	function DebugSQL(){
		if (!empty($this->_SQLDebug)){
			$output = '';
			foreach ($this->_SQLDebug as $title => $each){
				$output.= "<strong>".$title."</strong><br/>";
				foreach ($each as $e){
					$output.= $e."<br/>";
				}
			}
			$this->_SQLDebug = array();
		}
	}

	function getScopes($args){
		if (array_key_exists('scopes', $args)){
			if (is_array($args['scopes'])){
				foreach ($args['scopes'] as $eachScopeName){
					$instance = new $_model;
					if ($_model->scope($eachScopeName)){
						$args['conditions'][] = $_model->scope($eachScopeName);
					}else{
						trigger_error("Scope '</strong>".$eachScopeName."</strong>' not found in Model </strong>".$_model."</strong>", E_USER_ERROR);
					}
				}
				unset ($args['scopes']);
				return $args;
			}
		}
	}

	/**
	 * BlackMagic Functions
	 * @param	string	$func	Function name
	 * @param	array	$args	Query conditions
	 */
	public function __call($func, $args){
		$functionName = $func;
		switch (count($args)){
			case '1' : 	$functionName = $this->{$func.$args[0]}(); break;
			case '2' :	$args[1] = $this->getScopes($args[1]);
						$functionName = $this->{$func.$args[0]}($args[1]);
						break;
			case '3' : 	$functionName = $this->{$func}($args[0],$args[1]);break;
			case '4' : 	$functionName = $this->{'find'.$func}($args[0],$args[1],$args[2]); break;
		}
		if (is_string($functionName) && !empty($functionName)){
			return (function_exists($functionName)) ? $functionName : trigger_error("Function '</strong>".$functionName."</strong>' does not exist", E_USER_ERROR);	
		}
	}
    
	/**
	 * BlackMagic Setter
	 * @param	string	$var	Key set from controller
	 * @param	mixed	$val	Value set from controller
	 */
	function __set($var,$val){
		$this->_controller[$var] = $val;
	}
	
	/**
	 * BlackMagic Getter
	 * @param	mixed	$var	Values set from controller
	 */
	function __get($var){
		return $var;	
	}
    
	/**
	* Count elements from result
	* @param array $args Conditions
	* @return array $ar Return count from database
	*/
	function findcount($args = null){
		$args['fields'] = array('COUNT(*)');
		return $this->getQuery($this->_model,$args);
	}
    
	/**
	* Find All results by one simple argument
	* 
	* @param string $pk Field key
	* @param string $arg Field value
	* @return array $ar Return results from database
	*/
	function findAllBy($pk = null, $arg = null){
		$args = array('conditions' => array($pk => $arg));
		$ar = $this->getQuery($this->_model,$args);
		return $ar;
	}
	
	/**
	* Find First
	* 
	* Get the first element from the results
	* 
	* @param array $args Conditions from controller
	* @return array $ar Return results from database
	*/
	function findfirst($args = null){
		$args['limit'] = 1;
		$query = 'SELECT * FROM '.$this->_table.' '.$this->parseConditions($args);
		$ar = array();
		foreach ($this->_dbHandle->query($query) as $row){
			$ar[$this->_model]=$this->unparseId($row);
		}
		return $ar;
	}
    
	/**
	* Find List
	* 
	* Get a list of values from the database
	* 
	* @param array $args Conditions from controller
	* @return array $ar Return list with primaryKey => displayName
	*/
	public static function getList($args = null){
		/* Fields parser */
		$model = get_called_class();
		$table = Inflector::tableName($model);
		$pk = $model::$primaryKey;
		$df = $model::$displayField;
		$in = '';
		if (isset($args['fields']) and !empty($args['fields'])){
			if ( !isset( $args['fields'][$pk] ) ){
				array_push( $args['fields'] , $pk );
			}
			foreach ($args['fields'] as $fields){
				$in.= $fields.',';
			}
			$in = substr($in,0,-1);
		}else{
			$in = '`'.$pk.'`,`'.$df.'`';
		}
		
		$query = 'SELECT '.$in.' FROM '.$table.' '.self::parseConditions($args);
		$ar = array();
		foreach (self::$sql->query($query) as $row){
			if (isset($args['fields']) and !empty($args['fields'])){
				$ar[$row[$args['fields'][1]]] = $row[$args['fields'][0]];
			}else{
				$ar[$row[$pk]] = $row[$df];	
			}
		}
		return $ar;
	}
    
	/**
	 * Search results
	 * 
	 * @param string $column Field name
	 * @param string $needle Value to search
	 * @param array $args Extra conditions
	 * @return array $ar Results from the database
	 */
	function findsearch($column = null, $needle = null, $args = array()){
		$limit = (isset($args['limit'])) ? $args['limit'] : 0;
		$order = (isset($args['order'])) ? $args['order'] : false;
		if ($column && $needle){
			$orderStr = ($order)?'ORDER BY '.Security::sanitize($order).' DESC':'';
			$limitStr =($limit!=0)?" LIMIT 0, ".$limit:'';
			$query = 'SELECT * FROM '.$this->_table.' WHERE '.$this->parseId($column).' LIKE \'%'.Security::sanitize($needle).'%\' '.$orderStr.$limitStr;
			$ar = array();
			foreach ($this->_dbHandle->query($query) as $row){
				$ar[$this->_model][]=$this->unparseId($row);
			}	
			/* Pagination */
			$totalPages = $this->totalPages($query);
			if ($totalPages > 1){
				$ar['totalPages'] = $totalPages;
				$ar['currentPage'] = $this->_controller['page'];
			}
			return $ar;
		}
	}

	/**
	* Find All
	* 
	* Get all values from the database, an his parents and sons
	* 
	* @param array $args Conditions from controller
	* @return array $ar Return results from database
	*/
	function findall($args = null){
		if (key_exists('pageItems', $this->_controller)){
			$limitNum = $this->_controller['pageItems'];
			$pageNum = (isset($this->_controller['page'])) ? ($limitNum * ($this->_controller['page']-1)) : 0;
			$args['limit'] = ($pageNum !== false && $pageNum >= 0)?$pageNum.','.$limitNum:$limitNum;
		}
		$args['order'] = (key_exists('asort', $this->_controller))?$this->_controller['asort']:null;
		$args['order'] = (key_exists('dsort', $this->_controller))?$this->_controller['dsort']:null;
		if (empty($args['order'])){ unset($args['order']); }
		$ar = $this->getQuery($this->_model,$args);
		return $ar;
	}
	
	
	/**
	* Find Tree
	* 
	* Get a tree of values from the database
	* 
	* @param array $args Conditions from controller
	* @return array $ar Return list with primaryKey => displayName
	*/
	function findtree($args = null){
		//TODO: When "order by" is given, screw tree format
		$parentKey = $this->parentKey;
		$pk = $this->parseId($this->primaryKey);
		$df = $this->parseId($this->displayField);
		$ar = $this->getQuery($this->_model,$args);
		$items = $ar[$this->_model];
		$childs = array();
		foreach($items as &$item) $childs[$item[$parentKey]][] = &$item;
		unset($item);
		foreach($items as &$item) if (isset($childs[$item['ID']])){
			$item['CHILDS'] = $childs[$item['ID']];
		}
		$ret[$this->_model] = $this->flattenArray($childs[1]);
		return $ret;
	}
	
	/**
	 * Flatten Array
	 *
	 * Turns a Multi-dimensional array into a uni-dimensional array
	 * 
	 * @param	array	$array		to flatten
	 * @param	int	$treeLevel	tree level for recursivity
	 * @return	array	$out		flattened array
	 */
	function flattenArray($array, $treeLevel = 0){
		$sent = false;
		foreach($array as $key => $child){
			$child['LEVEL'] = $treeLevel;
			$out[] = $child;
			unset($out[key($out)]['CHILDS']);
			if(!empty($child['CHILDS'])){
				$treeLevel++;
				$aux = $this->flattenArray($child['CHILDS'],$treeLevel);
				foreach ($aux as $v){
					$out[] = $v;	
				}
				$treeLevel--;
			} 
		}
		return $out;
	}
	
	/** 
	* Get query for related models
	* @ignore
	*/
	function getQueryMore($model, $arrayQuery, $recursive){
		if (class_exists($model)){
			$modelVars = get_class_vars($model);
			$key = $model.DBS.$modelVars['primaryKey'];
			$value = $arrayQuery[$key]; //$ar[$modelName][$counter][$key];
			$args = array('conditions' => array($key => $value));
			$tmp = $this->getQuery($model, $args, $recursive + 1);
			if (isset($tmp[$model]) && !empty($tmp[$model])){
				return $tmp[$model];
			}
		}
		return null;
	}

	/**
	* Get query for has too many models
	* @ignore
	*/
	function getQueryHasTooMany($HTMModel,$models){
		$tmp = array();
		foreach ($models as $eachTMM){
			if ($eachTMM!=$modelName){
				$table = Inflector::tableName($HTMModel);
				$prefixTMM = $this->getPrefix($eachTMM);
				$modelTMM = get_class_vars($eachTMM);
				$tableTMM = Inflector::tableName($eachTMM);
				$actualTMM = $prefixTMM.$modelTMM['primaryKey'];
				$queryMany = 'SELECT * FROM `'.$table.'` '.($this->parseConditions($manyArgs_TOOMANY,$eachTMM));
				$this->_SQLDebug['Find HasTooMany '.$eachTMM][]=$queryMany;
				foreach ($this->_dbHandle->query($queryMany) as $row){
					foreach ($row as $n => $eachSet){
						if (key_exists($actualTMM,$eachSet)){
							$tmp[$n]=$eachSet[$actualTMM];
						}
					}
				}
			}
		}
		return $tmp;
	}

	/**
	* Get Query
	* 
	* Make a SELECT with the especified parameters
	* 
	* @param string	$modelName 	Model name
	* @param array 	$args 		Conditions from controller
	* @param int 	$recursive 	Actual level recursivity
	* @return array $ar 		Return results from database
	*/
	function getQuery($modelName, $args, $recursive = 1){
		$ar = array();

		/* Unbind models on the fly */
		if (isset($this->_controller['unModel']) && in_array($modelName,$this->_controller['unModel'])){ return; }
	
		/* Fields parser */
		$in = $this->fieldParser($args);
	
		/* Recursive level settings */
		$recursive_level = (isset($this->_controller['recursive']) && !empty($this->_controller['recursive']) ) ? $this->_controller['recursive'] : 1 ;
		
		$model = get_class_vars($modelName);
		$modelFK = $modelName.DBS.$model['primaryKey'];
		$table = Inflector::tableName($modelName);
		$query = 'SELECT '.$in.' FROM `'.$table.'` '.($this->parseConditions($args,$modelName));
		$this->_SQLDebug['Find '.$modelName][]=$query;
		$counter = 0;
		if (!empty($this->_dbHandle)){
			foreach ($this->_dbHandle->query($query) as $row){
				$ar[$modelName][$counter]=$this->unparseId($row,$modelName);
				/* Image behavior */
				if(is_array($model['thumbs'])){
					foreach($model['thumbs'] as $imageField => $thumbOptions){
						$imageData = $ar[$modelName][$counter][$imageField];
						if (!empty($imageData)){
							$ar[$modelName][$counter][$imageField] = array();
							$ar[$modelName][$counter][$imageField]['full'] = $imageData;
							foreach($thumbOptions as $thumbPrefix => $thumbData){
								$thumbArr = explode('/',$imageData);
								$thumbArr[count($thumbArr)-1] = $thumbPrefix."/".$thumbPrefix.".".$thumbArr[count($thumbArr)-1];
								$thumbURL = implode('/',$thumbArr);
								$ar[$modelName][$counter][$imageField][$thumbPrefix] = $thumbURL;
							}
						}
					}
				}
				
				/* Related hasMany */
				if (isset($model['hasMany']) && !empty($model['hasMany'])){
					foreach ($model['hasMany'] as $hasManyModel){
						$arrayQuery = $ar[$modelName][$counter];
						$ar[$modelName][$counter][$hasManyModel] = $this->getQueryMore($hasManyModel,$arrayQuery, $recursive);
					}
				}
				
				/* Check recursivity */
				if ($recursive < $recursive_level){
					/* Related BelongsTo */
					if (isset($model['belongsTo']) && !empty($model['belongsTo'])){
						foreach ($model['belongsTo'] as $belongsToModel){
							$arrayQuery = $ar[$modelName][$counter];
							$ar[$modelName][$counter][$belongsToModel] = $this->getQueryMore($belongsToModel,$arrayQuery, $recursive);
						}
					}

					/* Related hasTooMany */
					if (isset($model['hasTooMany']) && !empty($model['hasTooMany']) && $modelName!=$model['hasTooMany']){
						$lastModelID_TOOMANY = $ar[$modelName][$counter][$model['primaryKey']];
						$manyArgs_TOOMANY = array('conditions' => array($modelFK => $lastModelID_TOOMANY));
						foreach ($model['hasTooMany'] as $HTMModel => $models){
							$ar[$modelName][$counter][$HTMModel] = $this->getQueryHasTooMany($HTMModel,$models);
						}
					}	
				}
				
				$counter++;
			}
		}
		
		/* Pagination */
		$totalPages = $this->totalPages($query);
		if ($totalPages > 1){
			$ar['totalPages'] = $totalPages;
			$ar['currentPage'] = $this->_controller['page'];
		}
	
		/* Return all the array */
		return $ar;
	}	
	
	/**
	* Custom Query
	* 
	* @param string $query SQL Database custom query
	* @return array $ar SQL Results
	*/
	function custom($query){
		Security::sanitize($query);
		$ar = array();
		foreach ($this->_dbHandle->query($query) as $row){
			$ar[]=$row;
		}
		return $ar;
	}

	/**
	 * Delete from Database
	 * 
	 * @param int 		$id 		ID from database
	 * @param string 	$modelName 	Model name
	 * @param String 	$modelName2 Model name with Hasmany or HasTooMany
	 * @return type
	 */
	public static function delete($id){
	    $model = get_called_class();
		$table = Inflector::tableName($model);
		if ($id){
		    $primaryKey = self::getTablePrimaryKey($table);
		    $query = 'DELETE FROM '.$table.' WHERE `'.$primaryKey.'`=\''.Security::sanitize($id).'\'';
		    self::$sql->exec($query);
			$errors = self::$sql->errorInfo();
			if ($errors[1] == 0){
				return true;
			}
		}
		return false;
	}

	/**
	 * insertQuery
	 * @internal
	 * @param array $data Data from controller
	 * @param string $eachModel Model name
	 * @return string Formed query for sql insertion
	 */
	public static function insertQuery($data,$eachModel){
		$eachTable = Inflector::tableName($eachModel);
		$uc = "";
		$uv = "";
		if (SQLQuery::checkField(Inflector::getModelPrefix($eachModel).'CREATED',$eachTable)){
			$data[$eachModel]['CREATED'] = Time::timestampToSQL( time() );
		}
		if (SQLQuery::checkField(Inflector::getModelPrefix($eachModel).'MODIFIED',$eachTable) && SQLQuery::checkField(Inflector::getModelPrefix($eachModel).'CREATED',$eachTable)){
			$data[$eachModel]['MODIFIED'] = $data[$eachModel]['CREATED'];
		}
		if (is_array($data[$eachModel])){
			foreach ($data[$eachModel] as $k => $v){
				if (!empty($k) && (!empty($v) || $v==0)){
					$columnName = Security::sanitize($k);
					$value = Security::sanitize((string)$v);
					$uc .= $columnName;
					$uv .= '\''.$value.'\'';
					if(count($data[$eachModel]) > 1){
						$uc.=", ";
						$uv.=", ";
					}
				}
			}
			if (count($data[$eachModel]) > 1){
				$uc = substr($uc,0,-2);
				$uv = substr($uv,0,-2);
			}
			$query = 'INSERT INTO '.$eachTable.' ('.$uc.') VALUES ('.$uv.')';
			return $query;
		}else{
			return false;
		}
	}

	/**
	 * updateQuery
	 * @internal
	 * @param array $data Data from controller
	 * @param string $eachModel Model name
	 * @param int $id updated id
	 * @return string Formed query for sql updating
	 */
	public static function updateQuery($data,$eachModel,$id){
		$eachTable = Inflector::tableName($eachModel);
		$modelData = get_class_vars($eachModel);
		unset($data[$eachModel][$modelData['primaryKey']]);
		$uv = "";
		if (SQLQuery::checkField(Inflector::getModelPrefix($eachModel).'MODIFIED',$eachTable)){
			$data[$eachModel]['MODIFIED'] = Time::timestampToSQL( time() );
		}
		if (is_array($data[$eachModel])){
			foreach ($data[$eachModel] as $k => $v){
				if (!empty($k) && (!empty($v) || $v==0)){
					$columnName = Security::sanitize($k);
					$value = Security::sanitize((string)$v);
					$uv .= $columnName.'=\''.$value.'\'';
					if(count($data[$eachModel]) > 1){ $uv.=", "; }
				}
			}
			if (count($data[$eachModel]) > 1){ $uv = substr($uv,0,-2); }
			$idKey = $modelData['primaryKey'];
			$query = 'UPDATE '.$eachTable.' SET '.$uv.' WHERE '.$idKey.'=\''.Security::sanitize($id).'\'';
			return $query;
		}else{
			return false;
		}
	}

	/**
	* Save to Database
	* 
	* @param array $data Formated data from controlller
	* @return boolean
	*/
	public static function save($data = null){
		$results_ids = array();
		$hasTooMany = false;
		if ($data){
			$modelName = array_keys($data);
			foreach ($modelName as $eachModel){
				// Check for hasTooMany
				$modelData = get_class_vars($eachModel);
				if (isset($modelData['hasTooMany']) && !empty($modelData['hasTooMany'])){
					$hasTooMany = $modelData['hasTooMany'];
				}

				// Saving data
				if (isset($data[$eachModel]['0'])){
					// Save more than one
					if (is_array($data[$eachModel])){
						foreach ($data[$eachModel] as $eachData){
							$newData[$eachModel] = $eachData;
							$eachModel::save($newData);	
						}
					}
				}else{
					// Save just one
					$eachTable = Inflector::tableName($eachModel);
					$id = (isset($data[$eachModel][$modelData['primaryKey']])) ? $data[$eachModel][$modelData['primaryKey']] : '';
					if (empty($id)){
						if($modelData['primaryKey']){
							// Save Insert
							$query = self::insertQuery($data,$eachModel);
							if ($query){
								self::$sql->exec($query);
								$errors = self::$sql->errorInfo();
								if ($errors[1] != 0){
									return false;
								}
								self::$lastInsertedId[$eachModel] = $results_ids[$eachModel][] = self::$sql->lastInsertId();
							}
						}
					}else{
						if($modelData['primaryKey']){
							// Save update
							$query = self::updateQuery($data,$eachModel,$id);
							if ($query){
								self::$sql->exec($query);
								$errors = self::$sql->errorInfo();
								if ($errors[1] != 0){
									return false;
								}
								$results_ids[$eachModel][] = $id;
							}
						}
					}
				}
			}
			/* If "hasTooMany" Exists */
			if (!empty($results_ids) && $hasTooMany){
				foreach ($hasTooMany as $modelTooMany => $assocModels){
					$table = Inflector::tableName($modelTooMany);
					$assocModelData=array();
					// Load values for each model
					foreach ($assocModels as $eachAssocModel){
						$aux = get_class_vars($eachAssocModel);
						$assocModelData[$eachAssocModel] = $aux['primaryKey'];
						unset($aux);
					}

					// Get Column names
					$uc2 = '';
					foreach ($assocModelData as $assocModelName => $assocPrimaryKey){
						$uc2.= $assocPrimaryKey.", ";
					}
					$uc2 = substr($uc2,0,-2);

					foreach ($results_ids as $primaryModel => $values){
						foreach ($values as $eachPrimaryValue){
							// Delete all old related primaryModel
							$this->delete($eachPrimaryValue,$modelTooMany,$primaryModel);

							// Save related hasTooMany
							if (!empty($data[$modelTooMany])){
								foreach ($data[$modelTooMany] as $secondaryModel => $valuesTooMany){ 
									foreach ($valuesTooMany as $each_id){
										if($eachPrimaryValue != 0 && $each_id != 0 && $eachPrimaryValue != '' && $each_id != ''){
											$uv2 = $eachPrimaryValue.", ".$each_id;
											$query = 'INSERT INTO '.$table.' ('.$uc2.') VALUES ('.$uv2.')';
											self::$sql->exec($query);
											$errors = self::$sql->errorInfo();
											if ($errors[1] != 0){
												return false;
											}	
										}
									}
								}
							}
						}
					}
				}
			}
		}else{
			return false;
		}
		return true;
	}
	
	/**
	 * Get last inserted ID
	 * @return int
	 */
	function lastId(){
		return $this->_lastInsertedId[$this->_model];
	}
	
	/**
	* Get total pages for Pagination
	*
	* The items number per page, get it from model
	* Ex: $this->Model->pageItems = 20;
	* 
	* @param string $query actual database query
	* @return int $totalPages
	*/
	function totalPages($query) {
		if (key_exists('pageItems', $this->_controller)){
			if ($query){
				$realQuery = explode('LIMIT', $query);
				if (!empty($this->_dbHandle)){
					$count = $this->_dbHandle->exec($realQuery[0]);
					$totalPages = ceil($count/$this->_controller['pageItems']);
					return $totalPages;
				}
			}
		}
	}

	public static function checkField($field,$table){
	    if (!file_exists(ROOT.'/cache/'.$table.'.php')) return false;
		$file = file_get_contents(ROOT.'/cache/'.$table.'.php');
	    $rows = json_decode($file);
	    $found = false;
	    foreach ($rows as $row){
	        if ( $row->Field == $field )
	            $found = true;
	    }
		return $found;
	}
	
	public static function getTables(){
		$query = 'SHOW TABLES FROM '.DB_DATABASE;
		$tables = array();
		if (!empty(self::$sql)){
			foreach (self::$sql->query($query) as $row){
				$tables[$row['Tables_in_'.DB_DATABASE]]=Inflector::under_to_camel(Inflector::singularize(strtolower($row['Tables_in_'.DB_DATABASE])));
			}
		}
		return $tables;
	}

	public static function getTablePrimaryKey($table){
	    if (!file_exists(ROOT.'/cache/'.$table.'.php')) return false;
	    $file = file_get_contents(ROOT.'/cache/'.$table.'.php');
	    $rows = json_decode($file);
	    $primaryKey = false;
	    foreach ($rows as $row){
	        if ( $row->Key == 'PRI' )
	            $primaryKey = $row->Field;
	    }
        return $primaryKey;
    }
    
    public static function getTableDisplayField($table){
        if (!file_exists(ROOT.'/cache/'.$table.'.php')) return false;
        $file = file_get_contents(ROOT.'/cache/'.$table.'.php');
	    $rows = json_decode($file);
	    return $rows[1]->Field;
    }
    
    public static function getTableForeingKeys($table){
        if (!file_exists(ROOT.'/cache/'.$table.'.php')) return false;
        $file = file_get_contents(ROOT.'/cache/fk_'.$table.'.php');
        $rows = json_decode($file);
        return $rows;
    }
    
    public static function storeTableRelationsInCache($table){
        if (!file_exists(ROOT.'/cache/'.$table.'.php')) return false;
        $file = file_get_contents(ROOT.'/cache/'.$table.'.php');
        $rows = json_decode($file);
        $foreingKeys = array();
        foreach ($rows as $row){
            $fieldName = $row->Field;
            $fieldName = explode('_',$fieldName);
            $tableFK = Inflector::pluralize($fieldName[0]);
            if( file_exists(ROOT.'/cache/'.$tableFK.'.php') ){
                $tableFKPK = self::getTablePrimaryKey($tableFK);
                if (isset($fieldName[1]) && $tableFKPK == $fieldName[1]){
                    $foreingKeys[ucfirst($fieldName[0])] = $row->Field;
                }
            }
        }
        file_put_contents(ROOT.'/cache/fk_'.$table.'.php',json_encode($foreingKeys));
        return $foreingKeys;
    }
    
    public static function storeTableInCache($table){
        // Get table fields
        $query = "DESCRIBE `".DB_DATABASE."`.`{$table}`";
        $result = self::fetchQuery($query);
		// Store schema in cache
		file_put_contents(ROOT.'/cache/'.$table.'.php',json_encode($result));
		return $result;
    }
    
    //
    //
    // Static functions query models
    //
    //
    
    public static function fetchQuery($query){
        if (!empty(self::$sql)){
			$sqlQuery = self::$sql->prepare($query);
			$sqlQuery->execute();
			$result = $sqlQuery->fetchAll();
		}else{
		    trigger_error('No conection to database');
		}
		return $result;
    }
    
    public static $sql;
    public static $lastInsertedId;
    
    public static function __callStatic($func, $args){
        return call_user_func_array(array( self , $func ), $args );
	}
	
	public static function fieldParser($args){
		$arr = array();
		if (isset($args['fields']) and !empty($args['fields'])){
			if ($args['fields'] != array('COUNT(*)')){
				foreach ($args['fields'] as $fields){
					$arr[] = $fields;
				}
				return explode(',',$arr);
			}
			return 'COUNT(*)';
		}
		return '*';
	}
	
	public static function parseConditions($conditions){
		$rv = " WHERE '1'='1' ";
		if (is_array($conditions)){
			if (key_exists('conditions',$conditions)){
				if (is_array($conditions['conditions'])){
					foreach ($conditions['conditions'] as $k2 => $v2){
						if (!empty($v2)){
							$rv.=" AND ";
							if (!is_array($v2)){
								/* Buscamos por algun operador de condicion */
								if (substr($k2,-2) == '>=' || substr($k2,-2) == '<='){
									$rv.=" `".substr(Security::sanitize($k2),0,-3)."` ".substr($k2,-2)." '".Security::sanitize($v2)."'";
								}else if (substr($k2,-1) == '>' || substr($k2,-1) == '<' ){
									$rv.=" `".substr(Security::sanitize($k2),0,-2)."` ".substr($k2,-1)." '".Security::sanitize($v2)."'";
								}else if(substr($k2,-1) == '!'){
									$rv.=" `".trim(substr(Security::sanitize($k2),0,-1))."` ".substr($k2,-1)."= '".Security::sanitize($v2)."'";
								}else{
									$rv.=" `".Security::sanitize($k2)."`='".Security::sanitize($v2)."'";
								}
							}else{
								$k2 = str_replace('!',' NOT ',$k2);
								$rq=" ".Security::sanitize($k2)." IN (";
								foreach ($v2 as $v3){
									$rq.= $v3.',';
								}
								$rq= substr($rq,0,-1).")";
								$rv.=$rq;
							}
							if(count($conditions['conditions']) > 1){
								$rv.=" AND ";
							}
						}
					}
					if(count($conditions['conditions']) > 1){
						$rv = substr($rv,0,-4);
					}
				}
			}
			$rv.=(key_exists('group',$conditions)) ? " GROUP BY ".Security::sanitize($conditions['group']) : "";
			$rv.=(key_exists('order',$conditions)) ? " ORDER BY ".Security::sanitize($conditions['order']) : "";
			$rv.=(key_exists('limit',$conditions)) ? " LIMIT ".Security::sanitize($conditions['limit']) : "";
		}
		return $rv;
	}
	
	public static function find($args = null){
	    $model = get_called_class();
		$table = Inflector::tableName($model);
	    $modelVars = get_class_vars($model);
		$primaryKey = $modelVars['primaryKey'];
		$in = self::fieldParser($args);
		$query = 'SELECT '.$in.' FROM `'.$table.'` '.self::parseConditions($args);
		$result[$model] = self::fetchQuery($query); 
		return $result;
	}
	
	/**
	 * Find result by one simple argument
	 *
	 * @param string $pk Field key
	 * @param string $arg Field value
	 * @return array $aux Return results from database
	 */
	public static function findBy($pk = null, $arg = null){
	    $model = get_called_class();
		$table = Inflector::tableName($model);
		$args = array('conditions' => array($pk => $arg),'limit' => '1');
		$findOne = self::find($args);
		$result[$model] = $findOne[$model][0];
		return $result;
	}

}
