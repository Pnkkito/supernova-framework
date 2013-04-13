<?php
/**
 * Supernova Framework
 */
/**
 * Model handler
 *
 * Models perform business logic. Business logic, in this case,
 * refers to items that are fundamental to the overall system design.
 * For example, if a library application was constructed,
 * the logic which ensured a library card owner only rented up to 5 books at a time,
 * would be part of a model. Again, as part of the business logic,
 * models also tend to control access to application storage, such as database(s),
 * performing CRUD (creation, updating and deletion) tasks on the stored data.
 *
 * @package MVC_Model
 */
class Model extends SQLQuery {
	/**
	 * Model Name
	 * @var String
	 */
	protected $_model;
	
	/**
	 * Model table
	 * @var String 
	 */
	protected $_table;
	
	/**
	 * Construct
	 * @ignore
	 */
	function __construct(){
		if ($this->connect()){
			$this->_model = get_class($this);
			$this->_table = Inflector::tableName($this->_model);
		}
	}
	
	/**
	 * Get models called from model
	 * @param	String	$name	Model name
	 * @return	object		New Object model
	 */
	function __get($name){
		return $this->raise($name);
	}
	
	/**
	 * Calls functions from models in models
	 * @param	String	$func	Function name
	 * @param	Mixed	$args	Arguments
	 * @return	object		Results
	 */
	function __call($func,$args){
		return SQLQuery::__call($func,$args);
	}
	
	/**
	 * Raise model name
	 * @param String $modelName Model Name
	 * @return object
	 */
	public static function raise($modelName){
		if (class_exists($modelName)){
			return new $modelName;
		}
	}

}
