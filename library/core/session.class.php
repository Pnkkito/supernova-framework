<?php
/**
 * Supernova Framework
 */
/**
 * Session handler
 *
 * Session handler manage al session variables controller by the application
 *
 * @package MVC_Controller_Sessions
 */
class Session{
	/**
	 * Create session
	 * 
	 * @param	string	$key	Key for session
	 * @param	string	$value	Value for session
	 */
	function create($key, $value){
		$_SESSION[$key] = $value;
	}
	
	/**
	 * Destroy session
	 * @param	string	$key	Key value to destroy
	 */
	function destroy($key){ //sessiondestrooooooooooiiiii!!!!!
		$_SESSION[$key] = null;
		unset($_SESSION[$key]);
	}
	
	/**
	 * Read session
	 * @param	string	$key	Key value to read
	 * @return	string		Value for key
	 */
	function read($key){ 
		return $_SESSION[$key];
	}
}

