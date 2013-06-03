<?php
/**
 * Supernova Framework
 */
/**
 * Arrays handler
 * 
 * @package MVC_View_Arrays
 */
class Arrays{

	/**
	* Get static array values from file
	* 
	* @param string $filename Filename with array $values inside
	* @return mixed $values Return tinuURL
	* @deprecated
	*/
	function get($filename){
		$file = ROOT . DS . 'application' . DS . 'views' . DS . 'arrays' . DS . $filename . '.php';
		if(file_exists($file)){
			include($file);
			return $values;
		}
	}

	/**
	 * Get static values for array from app.json
	 * @param 	String 	$key 	Variable name to call from app.json
	 * @return 	Mixed 			Return values from array key
	 */
	function getAppValues($key){
		$file = ROOT . DS . 'config' . DS . 'app.json';
		if (file_exists($file)){
			$data = file_get_contents($file);
			$parsedData = json_decode($data);
			if (isset($parsedData[$key])){
				return $parsedData[$key];
			}else{
				trigger_error("Value '</strong>".$key."</strong>' does not exist in app.json", E_USER_ERROR);
			}
		}
	}

	/**
	 * Get static values for array from app.json
	 * @param 	String 	$key 	Variable and filename to call from config folder
	 * @return 	Mixed 			Return values from array key
	 */
	function getFileValues($key){
		$file = ROOT . DS . 'config' . DS . $key . '.json';
		if (file_exists($file)){
			$data = file_get_contents($file);
			$parsedData = json_decode($data);
			if (isset($parsedData[$key])){
				return $parsedData[$key];
			}else{
				trigger_error("'</strong>".$key.".json</strong>' does not exist in config folder", E_USER_ERROR);
			}
		}
	}
	
}
