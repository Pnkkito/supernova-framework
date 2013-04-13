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
	 * Check if DEVELOPMENT_ENVIRONMENT variable is set
	 * @ignore
	 */
	if (!defined('DEVELOPMENT_ENVIRONMENT')){
		/**
		 * DEVELOPMENT_ENVIRONMENT
		 * @var Boolean
		 */
		define('DEVELOPMENT_ENVIRONMENT', true);
	}

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
				die();	
			}
		}
	}

	/**
	 * Check if Environment variable is set
	 * @ignore
	 */
	if (DEVELOPMENT_ENVIRONMENT == true) {
		ini_set('display_errors','on');
		ini_set('error_reporting', E_ALL ^ E_NOTICE);
		ini_set('log_errors', 'On');
		ini_set('error_log', ROOT.DS.'logs'.DS.'fatal_error.log');
		set_error_handler("errorHandler");
	} else {
		ini_set('display_errors','Off');
		ini_set('error_reporting', 0);
		ini_set('log_errors', 'Off');
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
				$dispatch = new $controller($model,$controllerName,$action,$url,$admin);
				if ((int)method_exists($controller, $action)) {
				    if(method_exists($controller, 'beforeParse')){
					$dispatch->beforeParse();	
				    }
				    call_user_func_array(array($dispatch,$action),$queryString);
				}
			}else{
				die("Controller </strong>".$controllerName."</strong> does not exist");
			}
		}
	}

	/**
	 * Autoload any classes that are required
	 * @ignore
	 */
	function supernova_autoloader($className){
		$root_path = ROOT . DS;
		$app_path=$root_path.'application' . DS;
		$library_path=$root_path.'library' . DS . 'extensions' . DS;
		$className[0] = strtolower($className[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		$strName = preg_replace_callback('/([A-Z])/', $func, $className);
		$name = strtolower($strName);
		$file = $library_path.$name.'.class.php';
		$controllerFile = $app_path. 'controllers' . DS . $name . '.php';
		$modelFile = $app_path. 'models' . DS . $name . '.php';
		$appFile = $app_path.'app'.'.controller.php';
		if (file_exists($file)){
			require_once($file);
		}else if (file_exists($controllerFile)){
			require_once($controllerFile);
		}else if (file_exists($modelFile)){
			require_once($modelFile);
		}else if (file_exists($appFile)){
		  	require_once($appFile);
		}
	}

	/**
	 * Autoload classes
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
		if (DEVELOPMENT_ENVIRONMENT){
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
			ob_start();
			echo "<div class='alert' style='margin: 0;'>";
			echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
			echo "<h4>Debug</h4>";
			echo "<p>In <strong>$object</strong> -> Line <strong>$line</strong><br/>(file <strong>$file</strong>)</p>";
			echo "<pre>";
			print_r($str);
			echo "</pre></div>";
			ob_flush();
			ob_end_clean();
		}
	}

	/**
	 * Warning box
	 * @ignore
	 */
	function warning($str){
		if (DEVELOPMENT_ENVIRONMENT == true){
			ob_start();
			echo "<div class='alert alert-error' style='margin: 0;'>";
			echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
			echo "<h4>Warning</h4>";
			echo "<p>";
			print_r($str);
			echo "</p></div>";
			ob_flush();
			ob_end_clean();
		}
	}

	/**
	 * Mail Debug
	 *
	 * Returns the line and place where debug is called
	 * and prints their values and send them to Development_Email
	 * 
	 * @param	mixed	$mixed	Mixed var
	 */
	function maildebug($mixed){
		if (DEVELOPMENT_ENVIRONMENT){
			if (defined(DEVELOPMENT_EMAIL)){
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
				$header = "<html><head><title>Mail Debug</title></head><body>";
				$body = "<pre class='debug'><strong>DEBUG:</strong> In <strong>$object</strong> -> Line <strong>$line</strong>\n(file <strong>$file</strong>)";
				$body.= "<pre>".print_r($mixed,true)."</pre>";
				$footer = "</body></html>";
				$message = $header.$body.$footer;
				$email = DEVELOPMENT_EMAIL;
				$asunto = 'Mail Debug';
				$cabeceras = "Content-type: text/html\r\n";
				mail($email,$asunto,$message,$cabeceras);
			}
		}
	}

	/**
	 * Error Handler
	 * @ignore
	 */
	function errorHandler($numerr, $menserr, $nombrearchivo, $numlinea, $vars){
		// marca de tiempo para la entrada del error
		$fh = date("Y-m-d H:i:s (T)");
	    
		// definir una matriz asociativa de cadena de error
		// en realidad las únicas entradas que deberíamos
		// considerar son E_WARNING, E_NOTICE, E_USER_ERROR,
		// E_USER_WARNING y E_USER_NOTICE
		$tipoerror = array (
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
		
		// conjunto de errores por el cuál se guardará un seguimiento de una variable
		$errores_usuario = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
		
		$err = "<error datetime=\"$fh\">\n";
		$err .= "\t<errornum>" . $numerr . "</errornum>\n";
		$err .= "\t<type>" . $tipoerror[$numerr] . "</type>\n";
		$err .= "\t<msg>" . $menserr . "</msg>\n";
		$err .= "\t<scriptname>" . $nombrearchivo . "</scriptname>\n";
		$err .= "\t<scriptlinenum>" . $numlinea . "</scriptlinenum>\n";
	    
		if (in_array($numerr, $errores_usuario)) {
			$err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
		}
		$err .= "</error>\n\n";
		
		if (($numerr != E_NOTICE) && (strpos($menserr, "PDO::__construct():") === false)){
			echo "<div class='alert' style='margin: 0;'>";
			echo "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
			echo "<h4>$tipoerror[$numerr]</h4>";
			echo "<p><strong>$menserr</strong><br/>Archivo: <strong>$nombrearchivo</strong> -> Linea <strong>$numlinea</strong></p>";
			echo "</div>";
			// guardar al registro de errores, y enviarme un e-mail si hay un error crítico de usuario
			@error_log($err, 3, ROOT . DS . "logs" .DS . "errors.xml");
		}
		
		if (defined(DEVELOPMENT_EMAIL)){
			if ($numerr == E_ERROR || $numerr == E_USER_ERROR) {
				mail(DEVELOPMENT_EMAIL, "Critical Error", $err);
			}
		}
	}

	parseConfig();
	callHook();

