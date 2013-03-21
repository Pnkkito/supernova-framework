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

	/**
	 * Config File
	 * @var String
	 */
	$_configPath = ROOT . DS . "config/";
	$_configFiles = array('config','database');

	$_debug = array();

	/**
	 * Check if config file is set
	 * @ignore
	 */
	foreach ($_configFiles as $eachFile){
		if (file_exists($_configPath.$eachFile.".ini")){
			$_namesconfig = array("Database","Config","Others");
			foreach ($_namesconfig as $_name){
				if (array_key_exists($_name , $_configuration)){
					foreach ($_configuration[$_name] as $_k => $_v){
						if (!empty($_v) || $_v == 0){
							/**
							 * Call all config vars
							 * @ignore
							 */
							define($_k,$_v);
						}
					}
				}
			}
		}else{
			warning("Config file ".$eachFile.".ini is missing");
			die();	
		}
	}

	/**
	 * Check if Site URL variable is set
	 * @ignore
	 */
	if (!defined('SITE_URL')){
		/**
		 * Site URL
		 * @var String
		 */
		define("SITE_URL", (substr($_SERVER['SERVER_NAME'],-1) != "/") ? "http://".$_SERVER['SERVER_NAME']."/" : "http://".$_SERVER['SERVER_NAME']);
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
		// if(empty($_SERVER['CONTENT_TYPE'])){
		 	$type = "application/x-www-form-urlencoded";
			$_SERVER['CONTENT_TYPE'] = $type;
		// }
		Security::_removeMagicQuotes();
		Security::_unregisterGlobals();
		Security::_cleanAllVars();
		global $url;
		$url = Inflector::getBaseUrl();
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
			    warning("Controller </strong>".$controllerName."</strong> does not exist");
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
		$str = $className;
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		$strName = preg_replace_callback('/([A-Z])/', $func, $str);
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
	 * Print style on screen for debug
	 * @ignore
	 */
	function style(){
		// Sorry mom :(
		$style="<style>
			.debug{
				position: relative;
				padding: 15px;
				margin: 15px;
				border: 1px solid black;
				color: black;
				font-size: 14px;
				font-family: monospace;
				overflow: auto;
				
				text-align: left;
				line-height: 14px;
				border-radius: 10px;
				moz-border-radiuz: 10px;
				
				background-image: linear-gradient(bottom, rgb(251,255,199) 90%, rgb(227,222,157) 100%);
				background-image: -o-linear-gradient(bottom, rgb(251,255,199) 90%, rgb(227,222,157) 100%);
				background-image: -moz-linear-gradient(bottom, rgb(251,255,199) 90%, rgb(227,222,157) 100%);
				background-image: -webkit-linear-gradient(bottom, rgb(251,255,199) 90%, rgb(227,222,157) 100%);
				background-image: -ms-linear-gradient(bottom, rgb(251,255,199) 90%, rgb(227,222,157) 100%);
				
				background-image: -webkit-gradient(
					linear,
					left bottom,
					left top,
					color-stop(0.9, rgb(251,255,199)),
					color-stop(1, rgb(227,222,157))
				);
			}
			</style>
		    ";
		return $style;
	}

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
			// warning(print_r($trace,true));
			$file = $trace[0]['file'];
			$file = str_replace($_SERVER['DOCUMENT_ROOT'],'',$file);
			$line   = $trace[0]['line'];
			if (isset($trace[1]['object'])){
				$object = $trace[1]['object'];
				if (is_object($object)) { $object = get_class($object); }
			}else{
				$object = "View";
			}
			// echo style();
			// echo "<pre class='debug'><strong>DEBUG:</strong> In <strong>$object</strong> -> Line <strong>$line</strong>\n(file <strong>$file</strong>)";
			// echo "<pre class='debug' style='font-size: 80%'>";
			// print_r($str);
			// echo "</pre></pre>";
			$GLOBALS['_debug'][$object][$file][$line] = print_r($str,true); 
		}
	}

	register_shutdown_function('debugAll');

	function debugAll(){
		if (!empty($GLOBALS['_debug'])){
			echo '<div class="navbar navbar-inverse navbar-fixed-bottom">';
				echo '<div class="navbar-inner">';
					echo '<a class="brand" href="#">&nbsp;&nbsp;&nbsp;Debug</a>';
					echo '<script>$(".brand").click(function(){
						$(".fakecontainer.active").slideUp().removeClass("active");
						$(".nav a.active").removeClass("active");
					});</script>';
					echo '<ul class="nav">';
					foreach ($GLOBALS['_debug'] as $object => $debugfile){
						foreach ($debugfile as $file => $lines){
							foreach ($lines as $line => $debugstr){
								echo '<li><a href="#" rel="'.$object.$line.'" class="btn btn-inverse">'.$object.' line:'.$line.'</a></li>';
								echo "<script>
									$('.nav a[rel=\"$object$line\"]').click(function(){
										$('.fakecontainer.active').slideUp().removeClass('active');
										$('.nav a.active').removeClass('active');
										$('div[rel=\"$object$line\"]').slideToggle();
										$('div[rel=\"$object$line\"]').toggleClass('active');
										$(this).toggleClass('active');
									});
								</script>";
							}
						}
					}
					echo '</ul>';
				echo '</div>';
				foreach ($GLOBALS['_debug'] as $object => $debugfile){
					foreach ($debugfile as $file => $lines){
						foreach ($lines as $line => $debugstr){
							echo "<div class='fakecontainer' rel='".$object.$line."'>";
								echo "<p style='color: white;'>FILE: $file --- LINE: $line</p>";
								echo "<div class='well well-small' style='height: 200px; overflow: auto;'><pre style='font-size: 80%'>";
								print_r($debugstr);
								echo "</pre></div>";
							echo "</div>";
						}
					}
				}
			echo '</div>';
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
	 * Warning box
	 * @ignore
	 */
	function warning($str){
		if (DEVELOPMENT_ENVIRONMENT){
			ob_start();
			echo style();
			echo "<pre class='debug' style='background: #FEE !important; font-size: 12px;'>";
			print_r($str);
			echo "</pre>";
			ob_flush();
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
		
		if (($numerr != E_NOTICE) && (trim($menserr) != "mysql_connect():")){
			echo style();
			echo "<pre class='debug'>";
			echo "<strong>$tipoerror[$numerr]</strong> :: <strong>$menserr</strong>\nArchivo: <strong>$nombrearchivo</strong> -> Linea <strong>$numlinea</strong>\n";
			echo "</pre>";
			// guardar al registro de errores, y enviarme un e-mail si hay un error crítico de usuario
			error_log($err, 3, ROOT . DS . "logs" .DS . "errors.xml");
		}
		
		if (defined(DEVELOPMENT_EMAIL)){
			if ($numerr == E_ERROR || $numerr == E_USER_ERROR) {
				mail(DEVELOPMENT_EMAIL, "Critical Error", $err);
			}
		}
	}

	callHook();

?>