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

	/**
	 * Connects to Database
	 * @param	string	$address	Host address
	 * @param	string	$account	Username
	 * @param	string	$pwd		Password
	 * @param	string	$name		Database name
	 * @return	boolean			
	 */
	function connect($address, $account, $pwd, $name) {
		$this->_dbHandle = mysql_connect($address, $account, $pwd) or warning("Can't connect to the database");
		if ($this->_dbHandle != 0) {
			if (mysql_select_db($name, $this->_dbHandle)) {
				return 1;
			}
			else {
				warning('Database name incorrect, check the configuration file');
				return 0;
			}
		}
		else {
			warning('Connection problems to the Database, check the configuration file');
			return 0;
		}
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
			ob_start();
			warning ($output);
			ob_flush();
			$this->_SQLDebug = array();
		}
	}

	/**
	 * BlackMagic Functions
	 * @param	string	$func	Function name
	 * @param	array	$args	Query conditions
	 */
	public function __call($func, $args){
		switch (count($args)){
			case '1' : return $this->{$func.$args[0]}(); break;
			case '2' : return $this->{$func.$args[0]}($args[1]); break;
			case '3' : return $this->{$func}($args[0],$args[1]); break;
			default  : return $this->{'find'.$func}($args[0],$args[1],$args[2]); break;
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
	 * Find result by one simple argument
	 *
	 * @param string $pk Field key
	 * @param string $arg Field value
	 * @return array $aux Return results from database
	 */
	function findBy($pk = null, $arg = null){
		$args = array('conditions' => array($pk => $arg),'limit' => '1');
		$ar = $this->getQuery($this->_model,$args);
		$aux[$this->_model]=$ar[$this->_model][0];
		return $aux;
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
		$result = mysql_query($query, $this->_dbHandle);
		$ar = array();
		if ($result){
			while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){
				$ar[$this->_model]=$this->unparseId($row);
			}
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
	function findlist($args = null){
		/* Fields parser */
		$pk = $this->parseId($this->primaryKey);
		$df = $this->parseId($this->displayField);
		$in = '';
		if (isset($args['fields']) and !empty($args['fields'])){
			if (!isset($args['fields'][$this->primaryKey])){
				array_push($args['fields'], $this->primaryKey);
			}
			foreach ($args['fields'] as $fields){
				$in.= $this->parseID($fields).',';
			}
			//$in.= $this->primaryKey;
			$in = substr($in,0,-1);
		}else{
			$in = '`'.$pk.'`,`'.$df.'`';
		}
		
		$query = 'SELECT '.$in.' FROM '.$this->_table.' '.$this->parseConditions($args);
		$this->_SQLDebug['Find list '.$modelName][]=$query;
		$result = mysql_query($query, $this->_dbHandle);
		$ar = array();
		if (!empty($result)){
			while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){
				if (isset($args['fields']) and !empty($args['fields'])){
					$ar[$row[$this->parseId($args['fields'][1])]] = $row[$this->parseId($args['fields'][0])];
				}else{
					$ar[$row[$pk]] = $row[$df];	
				}
				
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
			$result = mysql_query($query, $this->_dbHandle);
			$ar = array();
			if (!empty($result)){
				while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){
					$ar[$this->_model][]=$this->unparseId($row);
				}	
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
			$pageNum = $limitNum * ($this->_controller['page']-1);
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
		$ret[$this->_model] = 	$this->flattenArray($childs[1]);
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
		/* Unbind models on the fly */
		if (isset($this->_controller['unModel']) && in_array($modelName,$this->_controller['unModel'])){ return; }
		$ar = array();
	
		/* Fields parser */
		if (isset($args['fields']) and !empty($args['fields'])){
			if ($args['fields'] != array('COUNT(*)')){
				foreach ($args['fields'] as $fields){
					$in = $this->parseID($fields).',';
				}
				$in = substr($in,0,-1);
			}else{
				$in = 'COUNT(*)';
			}
		}else{
			$in = '*';
		}
	
		/* Recursive level settings */
		$recursive_level = (isset($this->_controller['recursive']) && !empty($this->_controller['recursive']) ) ? $this->_controller['recursive'] : 1 ;
		$model = get_class_vars($modelName);
		$modelFK = $modelName.DBS.$model['primaryKey'];
		$table = Inflector::tableName($modelName);
		$query = 'SELECT '.strtoupper($in).' FROM `'.strtoupper($table).'` '.($this->parseConditions($args,$modelName));
		$this->_SQLDebug['Find '.$modelName][]=$query;
		$result = mysql_query($query, $this->_dbHandle) or die(warning(mysql_error()));
		$x = 0;
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){
			$ar[$modelName][$x]=$this->unparseId($row,$modelName);

			/* Image behavior */
			if(is_array($model['thumbs'])){
				foreach($model['thumbs'] as $imageField => $thumbOptions){
					$imageData = $ar[$modelName][$x][$imageField];
					if (!empty($imageData)){
						$ar[$modelName][$x][$imageField] = array();
						$ar[$modelName][$x][$imageField]['full'] = $imageData;
						foreach($thumbOptions as $thumbPrefix => $thumbData){
							$thumbArr = explode('/',$imageData);
							$thumbArr[count($thumbArr)-1] = $thumbPrefix."/".$thumbPrefix.".".$thumbArr[count($thumbArr)-1];
							$thumbURL = implode('/',$thumbArr);
							$ar[$modelName][$x][$imageField][$thumbPrefix] = $thumbURL;
						}
					}
				}
			}
			
			/* Related hasMany */
			if ($recursive < $recursive_level){
				$newRecursive = $recursive + 1;
				if (isset($model['hasMany']) && !empty($model['hasMany'])){
					foreach ($model['hasMany'] as $belongsModel){
						if (class_exists($belongsModel)){
							$newRecursive = $recursive + 1;
							$bmd = get_class_vars($belongsModel);
							$belongKey = $belongsModel.DBS.$bmd['primaryKey'];
							$belongValue = $ar[$modelName][$x][$belongKey];
							$belongArgs = array('conditions' => array($belongKey => $belongValue));
							$mewRec = $this->getQuery($belongsModel, $belongArgs, $newRecursive);
							if (isset($mewRec[$belongsModel]) && !empty($mewRec[$belongsModel])){
								$ar[$modelName][$x][$belongsModel]=$mewRec[$belongsModel];
							}else{
								unset ($ar[$modelName][$belongsModel]);
							}
						}
					}
				}
			}
			
			/* Related hasTooMany */
			if ($recursive < $recursive_level){
				if (isset($model['hasTooMany']) && !empty($model['hasTooMany']) && $modelName!=$model['hasTooMany']){
					$lastModelID_TOOMANY = $ar[$modelName][$x][$model['primaryKey']];
					$manyArgs_TOOMANY = array('conditions' => array($modelFK => $lastModelID_TOOMANY));
					foreach ($model['hasTooMany'] as $HTMModel => $models){
						foreach ($models as $eachTMM){
							if ($eachTMM!=$modelName){
								$getIDS = null;
								$table = Inflector::tableName($HTMModel);
								$prefixTMM = $this->getPrefix($eachTMM);
								$modelTMM = get_class_vars($eachTMM);
								$tableTMM = Inflector::tableName($eachTMM);
								$actualTMM = $prefixTMM.$modelTMM['primaryKey'];
								$queryMany = 'SELECT * FROM `'.strtoupper($table).'` '.($this->parseConditions($manyArgs_TOOMANY,$eachTMM));
								$this->_SQLDebug['Find HasTooMany '.$eachTMM][]=$queryMany;
								$resultMany = mysql_query($queryMany, $this->_dbHandle) or die(warning(mysql_error()));
								while ($rowMany = mysql_fetch_array($resultMany,MYSQL_ASSOC)){
									$getIDS[] = $rowMany;
								}
								if (!empty($getIDS) && is_array($getIDS)){
									foreach ($getIDS as $n => $eachSet){
										if (key_exists($actualTMM,$eachSet)){
											$ar[$modelName][$x][$HTMModel][$n]=$eachSet[$actualTMM];
										}
									}
								}
							}
						}
					}
				}
				
				/* Related belongsTo */
				if ($recursive < $recursive_level){
					if (isset($model['belongsTo']) && !empty($model['belongsTo'])){
						foreach ($model['belongsTo'] as $hasManyModel){
							if (class_exists($hasManyModel)){
								$newRecursive = $recursive + 1;
								$bmd = get_class_vars($hasManyModel);
								$hasKey = $hasManyModel.DBS.$bmd['primaryKey'];
								$hasValue = $ar[$modelName][$x][$hasKey];
								if (!empty($hasValue)){
									$hasArgs = array('conditions' => array($hasKey => $hasValue));
									$mewRec = $this->getQuery($hasManyModel, $hasArgs, $newRecursive);
									if (isset($mewRec[$hasManyModel]) && !empty($mewRec[$hasManyModel])){
										$ar[$modelName][$x][$hasManyModel] = $mewRec[$hasManyModel];
									}else{
										unset ($ar[$hasManyModel]);
									}
								}
							}
						}
					}
				}	
			}
			/* counter */
			$x++;
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
	 * Parse ID
	 *
	 * Parse value from human readable model name to database column format
	 * 
	 * @param	String	$val	Human readable model name
	 * @param   String 	$model 	Model name
	 * @return	String			Database oracle column format
	 */
	function parseId($val, $model = null){
		$separatedVals = explode(DBS,$val);
		$valPrimaryKey = end($separatedVals);
		$modelName = ($model) ? $model : get_class($this);
		$prefix = ($model) ? $this->getPrefix($model) : $this->getPrefix($modelName);
		$val = $prefix.$val;
		if (count($separatedVals) > 0){
			$modelName = str_replace(DBS,'',str_replace($prefix,'',Inflector::under_to_camel(str_replace($valPrimaryKey,'',$val))));
		}
		if (!empty($modelName) && class_exists($modelName)){
			$modelVars = get_class_vars($modelName);
			if ($valPrimaryKey == $modelVars['primaryKey']){
				$val = $this->getPrefix($modelName).$valPrimaryKey;
			}
		}
		if (!empty($valPrimaryKey)){
			return $val;
		}
	}
	
	/**
	 * Unparse ID
	 * 
	 * @param	mixed	$val		String or array in oracle database column format
	 * @param	String	$modelName	Model name
	 * @return	object				String or array in human readable column result
	 */
	function unparseId($val, $modelName = null){
		if (is_array($val)){
			$newval = $val;
			foreach($newval as $_k => $_v){
				$new_key = $this->unparseId($_k,$modelName);
				$newval[$new_key] = $_v;
				unset($newval[$_k]);
			}
			return $newval;
		} else {
			if (!$modelName){
				$modelName = $this->_model;
				$has = $this->hasMany;
				$belongs = $this->belongsTo;
			}else{
				$modelData = get_class_vars($modelName);
				$has = $modelData['hasMany'];
				$belongs = $modelData['belongsTo'];
			}
			$prefix = $this->getPrefix($modelName);
			if (strpos($val,$prefix) !== false){
				return str_replace($prefix,'',$val);
			}else{
				if (isset($has) && !empty($has) ){
					foreach ($has as $eachModel){
						$foreingPrefix = $this->getPrefix($eachModel);
						if (strpos($val,$foreingPrefix) !== false){
							return $eachModel.DBS.str_replace($foreingPrefix,'',$val);
						}
					}	
				}
				if (isset($belongs) && !empty($belongs) ){
					foreach ($belongs as $eachModel){
						$foreingPrefix = $this->getPrefix($eachModel);
						if (strpos($val,$foreingPrefix) !== false){
							return $eachModel.DBS.str_replace($foreingPrefix,'',$val);
						}
					}	
				}
			}
		}
	}
	
	/**
	 * Get prefix from model
	 * @param	String	$modelName	Model name
	 * @return	String			Prefix string
	 */
	function getPrefix($modelName){
		if (!empty($modelName) && class_exists($modelName)){
			$modelVars = get_class_vars($modelName);
			return DBPREFIX.$modelVars['index'].DBS;
		}
	}
	
	/**
	 * Parse Conditions
	 * 
	 * Conditions to format database query
	 * 
	 * @param	array	$conditions	Array with conditions
	 * @param	string	$model		Model name
	 * @return	object			Parsed database query
	 */
	function parseConditions($conditions, $model = null){
		$rv = '';
		if (is_array($conditions)){
			if (key_exists('conditions',$conditions)){
				$rv.="WHERE '1'='1' AND ";
				if (is_array($conditions['conditions'])){
					foreach ($conditions['conditions'] as $k2 => $v2){
						if (!empty($v2)){
							$k2 = ($model) ? $this->parseId($k2,$model) : $this->parseId($k2); 
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
						}else{
							$rv = "WHERE '1'='1'";
						}
					}
					if(count($conditions['conditions']) > 1){
						$rv = substr($rv,0,-4);
					}
				}
			}
			$rv.=(key_exists('group',$conditions)) ? " GROUP BY ".Security::sanitize($this->parseId($conditions['group'])) : "";
			$rv.=(key_exists('order',$conditions)) ? " ORDER BY ".Security::sanitize($this->parseId($conditions['order'])) : "";
			$rv.=(key_exists('limit',$conditions)) ? " LIMIT ".Security::sanitize($conditions['limit']) : "";
		}
		return $rv;
	}
    
	/**
	* Custom Query
	* 
	* @param string $query SQL Database custom query
	* @return array $ar SQL Results
	*/
	function custom($query){
		Security::sanitize($query);
		$result = mysql_query($query, $this->_dbHandle);
		$errors = mysql_error();
		if ($errors){
			warning ($errors);
			die();
		}
		$ar = array();
		if (!empty($result)){
			while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){
				$ar[]=$row;
			}
		}
		return $ar;
	}

	/**
	* Delete from Database
	* 
	* @param string $id ID from the table called in controller
	* @return boolean
	*/

	/**
	 * Delete from Database
	 * 
	 * @param int 		$id 		ID from database
	 * @param string 	$modelName 	Model name
	 * @param String 	$modelName2 Model name with Hasmany or HasTooMany
	 * @return type
	 */
	function delete($id, $modelName = null, $modelName2 = null){
		$model = ($modelName) ? $modelName : $this->_model;
		$table = ($modelName) ? Inflector::tableName($modelName) : $this->_table;
		$modelData = get_class_vars($model);
		if ($modelName2){
			$modelData2 = get_class_vars($modelName2);
		}
		
		if ($id){
			if ($modelName2){
				$idKey = $this->parseId($modelData2['primaryKey'], $modelName2);
			}else{
				$idKey = $this->parseId($modelData['primaryKey'], $model);
			}
			$query = 'DELETE FROM '.$table.' WHERE `'.$idKey.'`=\''.Security::sanitize($id).'\'';
			$this->_SQLDebug['Delete'][]=$query;
			$this->DebugSQL();
			$result = mysql_query($query, $this->_dbHandle) or die(warning(mysql_error()));

			if ($result == 0) {
				return false;
			}
			return true;
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
	function insertQuery($data,$eachModel){
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
					$columnName = Security::sanitize($this->parseId($k,$eachModel));
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
	function updateQuery($data,$eachModel,$id){
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
					$columnName = Security::sanitize($this->parseId($k,$eachModel));
					$value = Security::sanitize((string)$v);
					$uv .= $columnName.'=\''.$value.'\'';
					if(count($data[$eachModel]) > 1){ $uv.=", "; }
				}
			}
			if (count($data[$eachModel]) > 1){ $uv = substr($uv,0,-2); }
			$idKey = $this->parseId($modelData['primaryKey'],$eachModel);
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
	function save($data = null){
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
							$this->save($newData);	
						}
					}
				}else{
					// Save just one
					$eachTable = Inflector::tableName($eachModel);
					$id = $data[$eachModel][$modelData['primaryKey']];
					if (empty($id)){
						if($modelData['primaryKey']){
							// Save Insert
							$query = $this->insertQuery($data,$eachModel);
							if ($query){
								$this->_SQLDebug['Save Insert'][]=$query;
								$result = mysql_query($query, $this->_dbHandle) or die(warning(mysql_error()));
								$this->_lastInsertedId[$eachModel] = $results_ids[$eachModel][] = mysql_insert_id($this->_dbHandle);
							}
						}
					}else{
						if($modelData['primaryKey']){
							// Save update
							$query = $this->updateQuery($data,$eachModel,$id);
							if ($query){
								$this->_SQLDebug['Save Update'][]=$query;
								$result = mysql_query($query, $this->_dbHandle) or die(warning(mysql_error()));
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
						$uc2.= $this->parseId($assocPrimaryKey,$assocModelName).", ";
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
											$this->_SQLDebug['Save Insert HasTooMany'][]=$query;
											$result = mysql_query($query, $this->_dbHandle) or die(warning(mysql_error()));	
										}
									}
								}
							}
						}
					}
				}
			}
		}else{
			warning ("No data");
		}

		if ($result == 0){
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
				$result = mysql_query($realQuery[0], $this->_dbHandle);
				$count = mysql_num_rows($result);
				$totalPages = ceil($count/$this->_controller['pageItems']);
				return $totalPages;
			}
		}
	}

	/**
	 * Check if field exist in database
	 * 
	 * @param string $field Field name
	 * @param string $table Table name
	 * @return boolean
	 */
	public static function checkField($field,$table){
		$query = "SHOW columns from `".$table."` where field='".$field."'";
		$result = mysql_query($query);
		if ($result){
			while ($row = mysql_fetch_row($result)) {
				if (empty($row)){
					return false;
				}else{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get tables from database
	 * @return mixed 	Returns array with tables or false in error
	 */
	public static function getTables(){
		$result = mysql_query('SHOW TABLES FROM '.DB_NAME);
		if ($result){
			while ($row = mysql_fetch_row($result)) {
				$tables[strtoupper($row[0])]=Inflector::under_to_camel(Inflector::singularize(strtolower(substr($row[0],5))));
			}
			return $tables;
		}else{
			return false;
		}
	}

	/**
	 * Get fields from table
	 * @param string $table Table name
	 * @return array $fields Fields from table
	 */
	public static function getFields($table){
		$result = mysql_query('SHOW FIELDS FROM '.$table);
		while ($row = mysql_fetch_array($result)) { 
			$fields[$row['Field']] = $row['Type'];
		}
		return $fields;
	}
}
