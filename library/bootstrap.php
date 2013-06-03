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
	session_start();

	$_debug = array();

	/**
	 * Check if Site URL variable is set
	 * @ignore
	 */
	if (!defined('SITE_URL')){
		/**
		 * Site URL
		 * @var String
		 */
		define("SITE_URL", "http://".$_SERVER['SERVER_NAME'].(substr($_SERVER['SERVER_NAME'],-1) != "/") ? "/" : "");
	}

	/**
	 * Define Paths
	 * @ignore
	 */
	define('VIEW_PATH', ROOT . DS . 'application' . DS . 'views' . DS);
	define('LAYOUT_PATH', VIEW_PATH . 'layouts' . DS);
	define('ERRORS_PATH', VIEW_PATH . 'errors' . DS);
	define('APP_PATH', ROOT . DS .'application' . DS);
	define('LIBRARY_PATH', ROOT . DS . 'library' . DS);
	define('CORE_PATH', LIBRARY_PATH . DS . 'core' . DS);
	define('EXTENSIONS_PATH', LIBRARY_PATH . DS . 'extensions' . DS);
	
	/**
	 * Check if config file is set
	 * @ignore
	 */
	function parseConfig(){
		/**
		 * Config File
		 * @var String
		 */
		$_configPath = ROOT . DS . "config" . DS;
		$_configFiles = array('config','database');
		foreach ($_configFiles as $eachFile){
			$filename = $_configPath.$eachFile.".ini";
			if (file_exists($filename)){
				$_configuration = parse_ini_file($filename, true);
				foreach ($_configuration as $eachConfig => $values){
					foreach ($values as $_k => $_v){
						if (!empty($_v) || $_v == 0){
							/**
							 * Call all config vars
							 * @ignore
							 */
							define($_k,$_v);
						}
					}
				} 	
			}else{
				warning("Config file ".$eachFile.".ini is missing");
				include ($errors_path . '500.php');
				die();
			}
		}
	}

	/**
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
	    include (ERRORS_PATH . '500.php');
		die();
	}

	/**
	 * Shutdown Handler
	 * @ignore
	 */
	function shutDownFunction() {
	    $error = error_get_last();
	    if (!empty($error)){
	    	showError($error);
	    }
	}

	/**
	 * Main call function
	 * @ignore
	 */
	function callHook() {
		$_SERVER['CONTENT_TYPE'] = "application/x-www-form-urlencoded";
		Security::_removeMagicQuotes();
		Security::_unregisterGlobals();
		Security::_cleanAllVars();
		global $url;
		$url = Inflector::getQueryUrl();
		$urlArray = array();
		$urlArray = explode("/",$url);
		
		$routes = explode(";",ROUTES);
		$routeSearch = array_search($urlArray[0],$routes);

		if($routeSearch !== false){
			$controller = empty($urlArray[1]) ? DEFAULT_CONTROLLER : $urlArray[1];
			array_shift($urlArray);
			$action = (count($urlArray) <= 1 ) ? $routes[$routeSearch]."_".DEFAULT_ACTION : $routes[$routeSearch]."_".$urlArray[1] ;
			unset($urlArray[1]);
		} else {
			$controller = empty($urlArray[0]) ? DEFAULT_CONTROLLER : $urlArray[0];
			array_shift($urlArray);
			$action = empty($urlArray[0]) ? DEFAULT_ACTION : $urlArray[0];
		}
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
				}
				die();
			}
		}
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
		$name = convertName($className);
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
	}

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
		if (strpos($error['file'], "library/bootstrap.php") === false){
			echo "<p>In <strong>".$error['file']."</strong> -> Line <strong>".$error['line']."</strong><br/></p>";
		}
		echo "</div>";
	}

	parseConfig();
	callHook();
