<?php
/**
 * Supernova Framework
 *
 */
/**
 *
 * @package MVC
 * 
 */
	/**
	 * Define global paths
	 * @ignore
	 */
	define('WEBROOT', ROOT . DS . 'public' . DS); // Do not touch this ;)
	define('CONFIG_PATH', ROOT . DS . 'config' . DS);
	define('LIBRARY_PATH', ROOT . DS . 'library' . DS);
	define('APP_PATH', ROOT . DS .'application' . DS);
	define('VIEW_PATH', APP_PATH . 'views' . DS);
	define('LAYOUT_PATH', VIEW_PATH . 'layouts' . DS);
	define('ERRORS_PATH', VIEW_PATH . 'errors' . DS);
	define('CORE_PATH', LIBRARY_PATH . DS . 'core' . DS);
	define('EXTENSIONS_PATH', LIBRARY_PATH . DS . 'extensions' . DS);
	define('ENVIRONMENT', 'dev');
	/**
	 * Calls to inflector class // Important, Do not touch this ;)
	 */
	require_once(CORE_PATH.'inflector.class.php');

	/**
	 * Check if Site URL variable is set
	 * @ignore
	 */
	if (!defined('SITE_URL')){
		define("SITE_URL", "http://".$_SERVER['SERVER_NAME'].(substr($_SERVER['SERVER_NAME'],-1) != "/") ? "/" : "");
	}

	/**
<<<<<<< HEAD
<<<<<<< HEAD
	 * Site friendly name
	 */
	if (!defined('SITE_NAME')){
		define("SITE_NAME", "Lightweight PHP MVC Framework");
	}
=======
	 * Define Paths
	 * @ignore
	 */
=======
	 * Define Paths
	 * @ignore
	 */
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
	define('VIEW_PATH', ROOT . DS . 'application' . DS . 'views' . DS);
	define('LAYOUT_PATH', VIEW_PATH . 'layouts' . DS);
	define('ERRORS_PATH', VIEW_PATH . 'errors' . DS);
	define('APP_PATH', ROOT . DS .'application' . DS);
	define('LIBRARY_PATH', ROOT . DS . 'library' . DS);
	define('CORE_PATH', LIBRARY_PATH . DS . 'core' . DS);
	define('EXTENSIONS_PATH', LIBRARY_PATH . DS . 'extensions' . DS);
<<<<<<< HEAD
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9

	/**
	 * Check if config file is set
	 * @ignore
	 */
	$filename = CONFIG_PATH."config.json";
	if (file_exists($filename)){
		$configData = Inflector::parseJsonFile($filename);
		foreach ($configData as $_k => $_v){
			if (!empty($_v) || $_v == 0 && !defined($_k)){
				define($_k,$_v);
			}
		}
	}else{
		warning("Config file ".$filename." is missing");
		include (ERRORS_PATH . '500.php');
		die();
	}

	/**
	 * Set PHP Ini settings
	 */
	ini_set('display_errors', (ENVIRONMENT == "dev") ? 'On' : 'Off');
	ini_set('error_reporting',(ENVIRONMENT == "dev") ? 1 : 0);
	ini_set('log_errors', 	  (ENVIRONMENT == "dev") ? 'On' : 'Off');
	
	/**
	 * Set error handler and shutdown handler
	 */
	set_error_handler("errorHandler");
	register_shutdown_function('shutdownFunction');

	/**
	 * Check for 'rewrite' apache module enabled
	 * Rewrite will help to don't reach those important files inside your framework ;)
	 */
	if (array_search('mod_rewrite', apache_get_modules()) === false){
		warning("Please enable the 'rewrite' module in your Apache (command example: a2enmod rewrite )");
		include (ERRORS_PATH . '500.php');
		die();
	} 

	/**
	 * Check for 'Allowoverride' apache status
	 */
	if ( !isset($_SERVER['SupernovaCheck']) ){
		warning("Please set 'AllowOverride All' variable in your Apache website configuration");
		include (ERRORS_PATH . '500.php');
		die();
	}

	/**
	 * Session handler
	 */
	session_start();

	function loadDatabase(){
		$filename = CONFIG_PATH."database.json";
		if (file_exists($filename)){
			$databaseData = Inflector::parseJsonFile($filename);
			if (isset($databaseData[ENVIRONMENT])){
				foreach ($databaseData[ENVIRONMENT] as $db_k => $db_v){
					define('DB_'.strtoupper($db_k),$db_v);
				}
			}else{
<<<<<<< HEAD
				warning("Database environment not right");
=======
				warning("Config file ".$eachFile.".ini is missing");
				include ($errors_path . '500.php');
				die();
<<<<<<< HEAD
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
			}
		}
	}

	/**
<<<<<<< HEAD
<<<<<<< HEAD
	 * Check if Database vars are set
	 * @ignore
	 */
	function checkDBVars(){
		$db_vars=array('driver','host','database','username','password','prefix');
		foreach ($db_vars as $each_db_vars){ if(!defined('DB_'.strtoupper($each_db_vars))){ define('DB_'.strtoupper($each_db_vars),''); }}
		define('DBS','_'); unset($db_vars);
=======
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
	 * PHP INI Environment variables
	 */
	ini_set('display_errors','Off');
	ini_set('error_reporting', 0);
	ini_set('log_errors', 'Off');

	/**
	 * Define DB Variables just in case not setted in database.json
	 */
	$db_vars=array('NAME','PASS','HOST','USER','DRIVER');
	foreach ($db_vars as $each_db_vars){
		if(!defined('DB_'.$each_db_vars)){ define('DB_'.$each_db_vars,''); }
	}
	define('DBS','_');
	define('DBPREFIX','');
	unset($db_vars);

	/**
	 * Set error handler
	 */
	set_error_handler("errorHandler");
	register_shutdown_function('shutdownFunction');
	
	/**
	 * Error Handler
	 * @ignore
	 */
	function errorHandler($type, $message, $file, $line, $str){
		if (ENVIRONMENT == "dev") {
			showError(array('type' => $type, 'message' => $message, 'file' => $file, 'line' => $line, 'str' => $str));
	    }
	}

	/**
	 * Shutdown Handler
	 * @ignore
	 */
	function shutDownFunction() {
	    $error = error_get_last();
	    if (!empty($error)){
	    	showError($error);
	    	include (ERRORS_PATH . '500.php');
			die();
	    }
<<<<<<< HEAD
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
	}

	/**
	 * Main call function
	 * @ignore
	 */
	function callHook() {
		// Set default content type for Forms
		$_SERVER['CONTENT_TYPE'] = "application/x-www-form-urlencoded";

		// Set security restrictions for Sql Inyections
		Security::_removeMagicQuotes(); Security::_unregisterGlobals(); Security::_cleanAllVars();

		$routingFile = CONFIG_PATH."routing.json";
		if (file_exists($routingFile)){
			global $_routingData; $_routingData = Inflector::parseJsonFile($routingFile);
		}else{
			warning("Routing file ".$routingFile." is missing");
			include (ERRORS_PATH . '500.php');
			die();
		}
<<<<<<< HEAD
		
		// Get url array
		global $url; $url = Inflector::getQueryString();
		$path = array(); $path = explode("/",$url);

		$route = Inflector::getRoute($path);
		$controller = Inflector::getController($path);
		$action = Inflector::getAction($path);
	
		$modelName = Inflector::getModelFromController($controller);
		$controllerClass = Inflector::getControllerClass($controller);
		
		if (class_exists($controllerClass)){
			$dispatch = new $controllerClass($modelName,$controller,$action,$route,$url);
			if (method_exists($controllerClass, $action)) {
			    if(method_exists($controllerClass, 'beforeParse')){ $dispatch->beforeParse(); }
			    call_user_func_array(array($dispatch,$action),$path);
			    if(method_exists($controllerClass, 'afterParse')){ $dispatch->afterParse(); }
			}else{
				if (ENVIRONMENT == 'dev'){
					trigger_error("Action '</strong>".$action."</strong>' in Controller '</strong>".$controller."</strong>' does not exist".((isset($route) && !empty($route) ? " in '<strong>".$route."</strong>' route" : "")), E_USER_ERROR);
=======
		array_shift($urlArray);
		$queryString = $urlArray;	
		$controllerName = $controller;
		if ($controllerName!=WEBROOT){
			$controller = ucwords($controller);
			$model = Inflector::singularize($controller);
			$controller .= 'Controller';
			$controller[0] = strtolower($controller[0]);
			if (class_exists($controller)){
				$dispatch = new $controller($model,$controllerName,$action,$url);
				if ((int)method_exists($controller, $action)) {
				    if(method_exists($controller, 'beforeParse')){
						$dispatch->beforeParse();	
				    }
				    call_user_func_array(array($dispatch,$action),$queryString);
				    if(method_exists($controller, 'afterParse')){
						$dispatch->afterParse();	
				    }
				}
			}else{
				if (ENVIRONMENT == 'dev'){
					trigger_error("Controller '</strong>".$controllerName."</strong>' does not exist", E_USER_ERROR);
<<<<<<< HEAD
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
				}
				die();
			}
		}else{
			if (ENVIRONMENT == 'dev'){
				trigger_error("Controller '</strong>".$controller."</strong>' does not exist", E_USER_ERROR);
			}
			die();
		}

	}
	

	function convertName($className){
		$className[0] = strtolower($className[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		$strName = preg_replace_callback('/([A-Z])/', $func, $className);
		$name = strtolower($strName);
		return $name;
	}

	function convertName($className){
		$className[0] = strtolower($className[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		$strName = preg_replace_callback('/([A-Z])/', $func, $className);
		$name = strtolower($strName);
		return $name;
	}

	/**
	 * Autoload any classes that are required
	 * @ignore
	 */
	function supernova_autoloader($className){
<<<<<<< HEAD
<<<<<<< HEAD
		$name = Inflector::getControllerFromModel($className);
=======
		$name = convertName($className);
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
=======
		$name = convertName($className);
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
		$corefile = CORE_PATH.$name.'.class.php';
		$extensionfile = EXTENSIONS_PATH.$name.'.class.php';
		$controllerFile = APP_PATH. 'controllers' . DS . $name . '.php';
		$modelFile = APP_PATH. 'models' . DS . $name . '.php';
		$appFile = APP_PATH.'app.controller.php';
		if (file_exists($corefile)){
			require_once($corefile);
		}else if (file_exists($extensionfile)){
			require_once($extensionfile);
		}else if (file_exists($controllerFile)){
			require_once($controllerFile);
		}else if (file_exists($modelFile)){
			require_once($modelFile);
		}else if (file_exists($appFile)){
		  	require_once($appFile);
		}
	}

	/**
	 * Autoload register function
	 * @ignore
	 */
	spl_autoload_register('supernova_autoloader');

	/**
	 * Debug
	 *
	 * Returns the line and place where debug is called
	 * and prints their values in screen
	 * 
	 * @param	mixed	$str	Mixed var
	 */
	function debug($str){
		if (ENVIRONMENT == "dev"){
			$trace = debug_backtrace();
			$file = $trace[0]['file'];
			$file = str_replace($_SERVER['DOCUMENT_ROOT'],'',$file);
			$line   = $trace[0]['line'];
			if (isset($trace[1]['object'])){
				$object = $trace[1]['object'];
				if (is_object($object)) { $object = get_class($object); }
			}else{
				$object = "View";
			}
			echo "<div class='alert' style='margin: 0;'>";
			echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
			echo "<h4>Debug</h4>";
			echo "<p>In <strong>$object</strong> -> Line <strong>$line</strong><br/>(file <strong>$file</strong>)</p>";
			echo "<pre>";
			print_r($str);
			echo "</pre></div>";
		}
	}

	/**
	 * Warning box
	 * @ignore
	 */
	function warning($str){
		if (ENVIRONMENT == "dev"){
			echo "<div class='alert alert-error' style='margin: 0;'>";
			echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
			echo "<h4>Warning</h4>";
			echo "<p>";
			print_r($str);
			echo "</p></div>";
		}
<<<<<<< HEAD
<<<<<<< HEAD
	}

	/**
	 * Error Handler
	 * @ignore
	 */
	function errorHandler($type, $message, $file, $line, $str){
		showError(array('type' => $type, 'message' => $message, 'file' => $file, 'line' => $line, 'str' => $str));
	}

	/**
	 * Shutdown Handler
	 * @ignore
	 */
	function shutDownFunction() {
	    $error = error_get_last();
	    if (!empty($error)){
	    	showError($error);
	    	include (ERRORS_PATH . '500.php');
	    }
	}

	function showError($error){
		ob_clean();
		if (ENVIRONMENT == "dev") {
			$errorType = array (
				E_ERROR              => 'Error',
				E_WARNING            => 'Warning',
				E_PARSE              => 'Parsing Error',
				E_NOTICE             => 'Notice',
				E_CORE_ERROR         => 'Core Error',
				E_CORE_WARNING       => 'Core Warning',
				E_COMPILE_ERROR      => 'Compile Error',
				E_COMPILE_WARNING    => 'Compile Warning',
				E_USER_ERROR         => 'User Error',
				E_USER_WARNING       => 'User Warning',
				E_USER_NOTICE        => 'User Notice',
				E_STRICT             => 'Runtime Notice',
				E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
			);

			$errorcolor = (in_array($error['type'],array(E_ERROR,E_CORE_ERROR,E_USER_ERROR))) ? "alert-error" : "";
			echo "<div class='alert ".$errorcolor."' style='margin: 0;'>";
			echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
			echo "<h4>".$errorType[$error['type']]."</h4>";
			echo "<h5>".$error['message']."</h5>";
			// if (strpos($error['file'], "library/") === false){ //Exclude library in errors
				echo "<p>In <strong>".$error['file']."</strong> -> Line <strong>".$error['line']."</strong><br/></p>";
			// }
			echo "</div>";
		}
=======
	}

=======
	}

>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
	function showError($error){
		$errorType = array (
			E_ERROR              => 'Error',
			E_WARNING            => 'Warning',
			E_PARSE              => 'Parsing Error',
			E_NOTICE             => 'Notice',
			E_CORE_ERROR         => 'Core Error',
			E_CORE_WARNING       => 'Core Warning',
			E_COMPILE_ERROR      => 'Compile Error',
			E_COMPILE_WARNING    => 'Compile Warning',
			E_USER_ERROR         => 'User Error',
			E_USER_WARNING       => 'User Warning',
			E_USER_NOTICE        => 'User Notice',
			E_STRICT             => 'Runtime Notice',
			E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
		);

		$errorcolor = (in_array($error['type'],array(E_ERROR,E_CORE_ERROR,E_USER_ERROR))) ? "alert-error" : "";
		echo "<div class='alert ".$errorcolor."' style='margin: 0;'>";
		echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		echo "<h4>".$errorType[$error['type']]."</h4>";
		echo "<h5>".$error['message']."</h5>";
		// if (strpos($error['file'], "library/") === false){
			echo "<p>In <strong>".$error['file']."</strong> -> Line <strong>".$error['line']."</strong><br/></p>";
		// }
		echo "</div>";
<<<<<<< HEAD
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
	}

	loadDatabase();
	checkDBVars();
	callHook();
