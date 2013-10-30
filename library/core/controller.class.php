<?php
/**
 * Supernova Framework
 */
/**
 * Controller handler
 *
 * A controller handles feedback from the user and logic specific to the application.
 * A controllers job is to receive input from a user,
 * via controls found on the user interface of a view, to process it,
 * communicating with a model if necessary, and then to send output to the user,
 * usually by populating a view with relevant content.
 * 
 * @package MVC_Controller
 */
class Controller {
	/**
	 * @ignore
	 */
	protected $_model,$_controller,$_action;
	/**
	 * @ignore
	 */
	protected $_template,$_layout,$_url;
	/**
	 * @ignore 
	 */
	protected $post, $get, $errors;
	/**
	 * @ignore
	 */
	private $_admin,$_islogged;
	
	/**
	 * @ignore
	 */
	public $controller,$action,$Session;

	/**
	 * @ignore
	 */
	public $route;

	/**
	 * @ignore
	 */
	public $tbkPost;
	
	/**
	 * Getter any class
	 * @ignore
	 */
	function __get($class){
		return (class_exists($class)) ? new $class : trigger_error("Model or Extension '</strong>".$class."</strong>' does not exist", E_USER_ERROR);
	}

	/**
	 * @ignore
	 */
	function __construct($model, $controller, $action, $route, $url) {
		$this->route = $route;
		$this->controller = $controller;
		$this->action = str_replace($route.'_','',$this->action);
		
		//Internal
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;
		$this->_url = $url;

		$this->$model = new $model; //Call the modtemplate->_elname

		/* Converting $_POST values into "post" for model */
		if (!empty($_POST) || !empty($_FILES)){
			$this->post = $_POST['post'];

			// Transbank Post
			if (array_key_exists('TBK_ORDEN_COMPRA', $_POST)){
				foreach ($_POST as $k => $v){
					$this->tbkPost[$k] = $v;
				}
			}

			// Servipag XML2 Post
			if (array_key_exists('XML', $_POST)){
				$this->xmlPost = $_POST['XML'];
			}

			// Servipag XML4 Post
			if (array_key_exists('xml', $_POST)){
				$this->xmlPost = $_POST['xml'];
			}

			// Files
			if(!empty($_FILES)){
				$retFiles = $this->Behavior->file($_FILES);
				$aux = array_keys($_FILES['post']['name']);
				$fileModel = $aux[0];
				if($fileModel===0){
					$fileModelKeys = array_keys($_FILES['post']['name'][0]);
					$fileModel = $fileModelKeys[0];
				}
				$fileModelpost = get_class_vars($fileModel);
				if (isset($retFiles) && !empty($retFiles)){
					if (!isset($retFiles[0])){
						// For one file
						foreach($retFiles as $key => $eachFile){
							if(strpos($eachFile['type'],'image') !== false){
								if(isset($fileModelpost['thumbs']) && !empty($fileModelpost['thumbs'])){
									foreach($fileModelpost['thumbs'] as $thumbOptions){
										$this->Resize->createThumbs($thumbOptions, $eachFile);
									}
								}
							}
							$this->post[$fileModel][$key] = $eachFile['file'];
						}
					}else{
						// For many files
						foreach($retFiles as $keyOne => $eachFiles){
							foreach($eachFiles as $key => $eachFile){
								if(strpos($eachFile['type'],'image') !== false){
									if(isset($fileModelpost['thumbs']) && !empty($fileModelpost['thumbs'])){
										foreach($fileModelpost['thumbs'] as $thumbOptions){
											$this->Resize->createThumbs($thumbOptions, $eachFile);
										}
									}
								}
								$this->post[$fileModel][$keyOne][$key] = $eachFile['file'];
							}
						}
					}
					
				}
				unset ($_FILES);
			}
			
			// This post validation
			if (isset($this->$model->validate)){
				if (!empty($this->$model->validate)){
					$validation = $this->$model->validate;
					$this->errors =	$this->Validator->validate($validation, $this->post, $model);
					$this->$model->errors = $this->errors;
				}
			}
		}else{
			$this->post = null;
		}
		unset ($_POST);
		
		$parse_id = str_replace($this->route.'/','',$this->_url);
		$parse_id = str_replace($controller.'/','',$parse_id);
		$action2 = str_replace($this->route.'_','',$action);
		$parse_id = str_replace($action2.'/','',$parse_id);
		
		/* Check for parameters in the url
		*  Return them into the model for sql purposes */
		$params = $_SERVER['QUERY_STRING'];
		$params = explode('/',$params);
		
		foreach ($params as $eachParam){
			$check = explode(':',$eachParam);
			if (in_array($check[0],array('page','asort','dsort'))){
				$this->$model->$check[0] = $check[1];
			}
		}

		$this->_template = new View($controller,$action,$parse_id);
		$this->_template->errors = $this->errors;
		$this->_template->post = $this->post;
		// $this->_template->params = $params;
	}
	
	/**
	 * Set variables to the view
	 * @param	mixed	$name	Key for value or array with values
	 * @param	mixed	$value	Values
	 */
	function set($name,$value = null) {
		if (is_array($name)){
			foreach ($name as $_k => $_v){
				$this->_template->set($_k,$_v);
			}
		}else{
			$this->_template->set($name,$value);
		}
	}
	
	/**
	 * Set layout template
	 * @param	String	$layout	Layout name
	 */
	function layout($layout){
		$this->_template->_layout = $layout;
	}

	/**
	 * Call render if need it
	 * @param 	String 	$view 	View name
	 * @param 	String 	$type 	Type of view (null and "file" are the options)
	 * @param 	String 	$file 	If type "file" is choosen, this is the destination file to save the output
	 */
	function render($view = null, $type = null, $file = null){
		$this->_template->render($view,$type,$file);
	}
	
	/**
	 * Set message to the template
	 * @param	String	$msg	Message
	 * @param	String	$class	Class name
	 */
	function setMessage($message, $class = 'notice'){
		$output = "<div class='alert alert-$class'>";
		$output.= "<button type='button' class='close' post-dismiss='alert'>&times;</button>";
		$output.= $message;
		$output.= "</div>";
		$this->_template->setMessage($output);
	}
	
	/**
	 * Redirect to url
	 * @param	mixed	$url	Url or array width controller and action
	 */
	function redirect($url = null){
		if (is_array($url)){
			$url = Inflector::generateUrl($url);
		}
		$this->_template->_redirect = $url;
		ob_start();
		header('Location:'.$url);
		ob_flush();
		die();
	}

	/**
	 * @ignore
	 */
	function __destruct() {
		if ($this->_template)
			$this->_template->render();
	}
	
}

