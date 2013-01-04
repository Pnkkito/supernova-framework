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
	 * Message to show in view
	 * @var string
	 */
	public $_message;
	
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
			// foreach ($params as $name => $eachparams){
			// 	$$name = $eachparams;
			// }
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
		$this->_message = $msg;
		$Session->create($key, $this->_message);
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
	function render(){
		if (!empty($this->_redirect)){
			if(empty($this->errors)){
				ob_flush();
				header('Location:'.$this->_redirect);
			}
		}

		$this->arrays = new Arrays;

		$this->html = new Html;
		$this->html->controller = $this->controller;
		$this->html->errors = $this->errors;
		$this->html->data= $this->data;
		$this->html->action = $this->action;
		$this->html->params = $this->params;
		extract($this->variables);
		
		ob_flush();
		if(ini_get("zlib.output_compression") == 'On'){
			ob_start("ob_gzhandler");
		}else{
			ob_start();	
		}
		if(file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php')){
			include(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');
		} else {
			warning ("View <strong>".$this->_action."</strong> does not exist for controller <strong>".$this->_controller."</strong>");
			die();
		}
		$content_for_layout = ob_get_contents();
		ob_end_clean();
		
		if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . "layouts" . DS . $this->_layout . '.php')) {
			include(ROOT . DS . 'application' . DS . 'views' . DS . "layouts" . DS . $this->_layout . '.php');
		} elseif (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . "layouts" . DS . 'default.php')) {
			include(ROOT . DS . 'application' . DS . 'views' . DS . "layouts" . DS . 'default.php');
		} else {
			include (ROOT . DS . 'application' . DS . 'views' . DS . '404.php');
		}	
	}
}