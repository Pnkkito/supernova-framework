<?php
/**
 * Supernova Framework
 */
/**
 * View handler
 *
 * Views are entirely for the presentation part of your application.
 * It contains no brains, no logic, no intelligence to the extent permissible
 * by the programming language used. A view is merely the appearance
 * of your application and nothing else.
 * 
 * @package MVC_View
 */
class View {
	/**
	 * Variables from controller
	 * @var Array	
	 */
	protected $variables = array();
	
	/**
	 * Controller name
	 * @var string	
	 */
	protected $_controller;
	
	/**
	 * Action name
	 * @var string	
	 */
	protected $_action;
	
	/**
	 * Url
	 * @var string
	 */
	protected $_url;
	
	/**
	 * Redirect Url
	 * @var string
	 */
	public $_redirect;
	
	/**
	 * public Controller name
	 * @var string
	 */
	public $controller;
	
	/**
	 * public Action name
	 * @var string
	 */
	public $action;
	
	/**
	 * Layout to show in view
	 * @var string
	 */
	public $_layout;
	
	/**
	 * Html elements
	 * @var object
	 */
	public $html;
	
	/**
	 * Validation errors
	 * @var string	
	 */
	public $errors;

	/**
	 * Parameters from controller
	 * @var Array
	 */
	public $params;

	/**
	 * Data
	 * @var mixed
	 */
	public $data;
	
	/**
	 * Construct
	 * @ignore
	 * @param	string	$controller	Controller name
	 * @param	string	$action		Action name
	 * @param	string	$url		Url
	 */
	function __construct($controller,$action,$url) {
		$this->_controller = $controller;
		$this->_action = $action;
		$this->controller = $controller;
		$this->action = $action;
		$this->_url = $url;
	}

	/**
	 * Set internaly variables to the View
	 * @ignore
	 * @param	string	$name	Key name
	 * @param	mixed	$value	Values
	 */
	function set($name,$value = null) {
		$this->variables[$name] = $value;
	}
	
	/**
	 * Layout name
	 * @ignore
	 */
	function layout($value) {
		$this->_layout = $value;
	}

	/**
	 * Summonning little html pieces
	 * @param	String	$snipet	Snipet
	 * @param	String	$layout	Layout name
	 */
	// function summonChunk($snipet, $layout = 'default'){
	// 	$file = ROOT . DS . 'application' . DS . 'views' . DS . 'layouts' . DS . 'chunks' . DS .  $layout."_".$snipet.'.php';
	// 	if(file_exists($file)){
	// 		extract($this->variables);
	// 		include($file);
	// 	}
	// }
	
	/**
	 * Calling elements to html
	 * 
	 * @param	string	$snipet	Snipet
	 * @param	array	$params	Values for element
	 */
	function element($snipet, $params = array()){
		$file = ROOT . DS . 'application' . DS . 'views' . DS . 'elements' . DS .  $snipet.'.php';
		if(file_exists($file)){
			extract($params);
			include($file);
		}
	}
	
	/**
	 * Set message in template
	 * @param string $msg Message string
	 * @param string $key Key name to save in Session variable
	 */
	function setMessage($msg, $key = 'message'){
		$Session = new Session;
		$Session->create($key, $msg);
	}
	
	/**
	 * Get message for template
	 * @param	String	$key	Type
	 */
	function getMessage($key = 'message'){
		if(empty($this->errors)){
			$Session = new Session;
			$msg = $Session->read($key);
			if (!empty($msg)){
				if (empty($this->_redirect)){
					$Session->destroy($key);
				}
				return $msg;
			}
		}
	}
	
	/**
	 * Display template
	 * @ignore
	 */
	function render($view = null, $type = "view", $filename = null){
		$this->arrays = new Arrays; // Static array caller
		$this->html = new Html; // HTML helper

		if ($type == "file"){
		    $relativePath = DS . 'application' . DS . 'views' . DS . $this->_controller;
		    $relativeFile = ((is_null($view)) ? $this->_action : $view).'.php';
			$viewFile = ROOT . $relativePath . DS . $relativeFile;
			$explodedFile = explode('/', $filename);
		    $file = array_pop($explodedFile);
			$pathfile = implode('/',$explodedFile);
			if(file_exists($viewFile)){
				extract($this->variables);
				ob_start();
				include($viewFile);
				$content = ob_get_contents();
				ob_end_clean();
				if ( !is_writable($pathfile) )
				{
				    warning ("Can't write the file <strong>".$file.'</strong> into <strong>'.$pathfile.'</strong>. Permission problems perhaps?');
				    include (ERRORS_PATH . '500.php');
			        die();
				}else{
				    file_put_contents($filename, $content);
				}
			}
			return;
		}

		// Check for redirection
		if (!empty($this->_redirect)){
			if(empty($this->errors)){
				header('Location:'.$this->_redirect);
			}
		}

		//Extract global vars to html helper
		$htmlvars = array('controller','errors','data','action','params');
		foreach ($htmlvars as $eachvar){
			$this->html->{$eachvar} = $this->{$eachvar};	
		}
		
		//Extract controller vars to view
		extract($this->variables);
		
		ob_start( (ini_get("zlib.output_compression") == 'On') ? "ob_gzhandler" : null );
		
		//Search for view layout
		if(file_exists(VIEW_PATH . $this->_controller . DS . ((is_null($view)) ? $this->_action : $view) . '.php')){
			include(VIEW_PATH . $this->_controller . DS . ((is_null($view)) ? $this->_action : $view) . '.php');
		} else {
			if (ENVIRONMENT == 'dev'){
				warning ("View '<strong>".$this->_action."</strong>' does not exist for controller '<strong>".$this->_controller."</strong>'");
			}
			include (ERRORS_PATH . '404.php');
			die();
		}
		$content_for_layout = ob_get_contents();
		$error = error_get_last();
		if (!empty($error)){
	    	showError($error);
	    	die();
	    }
		ob_end_clean();
		
		if (file_exists(LAYOUT_PATH . $this->_layout . '.php')) {
			include(LAYOUT_PATH . $this->_layout . '.php');
		} elseif (file_exists(LAYOUT_PATH . 'default.php')) {
			include(LAYOUT_PATH . 'default.php');
		} else {
			if (ENVIRONMENT == 'dev'){
				warning ("the layout does not exist, please check your /views/layout folder");
			}
			include (ERRORS_PATH . '404.php');
			die();
		}
	}
}
