<?php
/**
 * Supernova Framework
 */
/**
 * Validator class
 *
 * This class validates the criteria defined in the model.
 *
 * @package MVC_Model_Validator
 */
class Validator {
	/**
	 * Contains validation errors
	 * @ignore
	 */
	var $errors = array();
	
	/**
	 * Model name
	 * @var String
	 */
	private $_model;

	/**
	 * Primary key name
	 * @var String
	 */
	private $_pk;

	/**
	 * ID name
	 * @var String
	 */
	private $_id;

	/**
	 * Model data
	 * @var Mixed
	 */
	private $_modelData;

	/**
	 * Key name
	 * @var String
	 */
	private $_key;

	/**
	 * Validates defined criteria
	 * @param	array	$valArr	Array containing the validation criteria
	 * @param	array	$data 	Array with data to validate
	 * @param	string	$modelName Model to validate
	 */
	function validate($valArr = array(), $data, $modelName){
		$this->_model = $modelName;
		$this->_modelData = get_class_vars($modelName);
		$this->_pk = $this->_modelData['primaryKey'];
		$this->_id = (isset($data[$this->_model][$this->_pk]) && !empty($data[$this->_model][$this->_pk])) ? $data[$this->_model][$this->_pk] : null;
		if (is_array($data) && !empty($data)){
			if(!empty($valArr)){
				foreach($data[$this->_model] as $keyItem => $validateItem){
					if(!empty($valArr[$keyItem]) && is_array($valArr[$keyItem])){
						foreach($valArr[$keyItem] as $validateKey => $validateFn){
							$this->_key = $keyItem;
							$this->errors[$keyItem][(!is_array($validateFn)) ? $validateFn : $validateKey] = (!is_array($validateFn)) ? $this->{$validateFn}($validateItem) : $this->{$validateKey}($validateItem, $validateFn);
						}
					}
				}
			}
		}

		$errorCount = 0;
		foreach($this->errors as $field => $errorFields){
			foreach($errorFields as $errorKey => $errorValue){
				$errorCount = ($errorValue) ? $errorCount + 1 : $errorCount;
			}
		}
	
		return ($errorCount > 0) ? $this->errors : false;

	}
	
	/**
	 * Validates not empty fields
	 * @param	string	$var	String to validate
	 * @return	mixed $ret	Error
	 */
	function notEmpty($var){
		return (trim($var) == '') ? "Field can't be empty" : '';
	}
	
	/**
	 * Validates numeric fields
	 * @param	string	$var	String to validate
	 * @return	mixed $ret	Error
	 */
	function numeric($var){
		return (!filter_var($var, FILTER_VALIDATE_INT)) ? "Field need to be numeric" : '';
	}
	
	/**
	 * Validates length for string
	 * @param string $var String
	 * @param array $options Option array with min and max values
	 * @return mixed Error
	 */
	function length($var,$options){
		$min = (isset($options['min'])) ? $options['min'] : null;
		$max = (isset($options['max'])) ? $options['max'] : null;

		if ($min && $max){
			return (strlen($var) >= $min && strlen($var) <= $max) ? '' : 'Execeed minimal and maximal values';
		}else{
			if ($min){
				return (strlen($var) >= $min) ? '' : 'Exceed minimal values';
			}
			if ($max){
				return (strlen($var) <= $max) ? '' : 'Exceed maximal values';
			}
			return 'error';
		}
	}
	
	/**
	 * Validates email fields
	 * @param	string	$var	String to validate
	 * @return	mixed $ret	Error
	 */
	function email($var){
		return (!filter_var($var, FILTER_VALIDATE_EMAIL)) ? "Email not valid" : '';
	}
	
	/**
	 * Validates file extensions
	 * @param string $var File name
	 * @param array $types Array with file types
	 * @return mixed Error
	 */
	function file($var, $types){
		if(!empty($var)){
			$varArr = explode('.',$var);
			$extension = end($varArr);
			if(!in_array($extension, $types)){
				return 'File type not allowed';	
			}
		} else {
			return '';	
		}
	}
	
	/**
	 * Validates alphanumeric fields
	 * @param	array	$var	string to validate
	 * @return	mixed $ret	Error
	 */
	function alphanumeric($var){
		return (!preg_match('/^[A-Za-z0-9_]+$/',$var)) ? "Field should be only alphanumeric" : '';
	}
	
	/**
	 * Validates url fields
	 * @param	array	$var	string to validate
	 * @return	mixed $ret	Error
	 */
	function url($var){
		return (!filter_var($var, FILTER_VALIDATE_URL)) ? "Invalid URL" : '';
	}
	
	
	/**
	 * Validates Verifier Digit for Chilean rut
	 * @ignore
	 */
	function dv($r){
		$s=1;for($m=0;$r!=0;$r/=10)$s=($s+$r%10*(9-$m++%6))%11;
		return chr($s?$s+47:75);
	}

	/**
	 * Validates chilean rut fields
	 * @param	array	$var	string to validate
	 * @return	mixed $ret	Error
	 */
	function rut($var){
		if (!empty($var)){
			/* Limpiando puntos */
			$var = str_replace('.','',$var);
			/* Separando digito verificador */
			$aux = explode('-',$var);
			$rut = $aux[0];
			$digito_v = strtoupper($aux[1]);
			$verifica = $this->dv($rut);
			return ($digito_v == $verifica) ? '' : "Rut Invalido";
		}else{
			return 'Rut invalido';
		}
	}
	
	/**
	 * Validates Unique
	 * @param	mixed	$var	Value to evaluate
	 * @return	mixed $ret	Error
	 */
	function unique($var){
		$modelObj = new $this->_model;
		$find = $modelObj->findBy($this->_key,$var);
		if (!empty($find)){
			return ($find[$this->_model][$this->_pk] == $this->_id) ? '' : "Field need to be unique";
		}else{
			return '';
		}
	}
	
}