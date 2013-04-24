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
	*/
	function get($filename){
		$file = ROOT . DS . 'application' . DS . 'views' . DS . 'arrays' . DS . $filename . '.php';
		if(file_exists($file)){
			include($file);
			return $values;
		}
	}
}
