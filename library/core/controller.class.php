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
	protected $data, $get, $errors;
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
<<<<<<< HEAD
<<<<<<< HEAD
	function __construct($model, $controller, $action, $route, $url) {
		$this->route = $route;
=======
=======
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
	function __construct($model, $controller, $action, $url) {
		//For public actions
>>>>>>> 585760b57e1db7d012b8a3dc70593b19af28d8f9
		$this->controller = $controller;
		$this->action = str_replace($route.'_','',$this->action);
		
		//Internal
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;
		$this->_url = $url;

		$this->$model = new $model; //Call the modtemplate->_elname

		/* Converting $_POST values into "data" for model */
		if (!empty($_POST) || !empty($_FILES)){
			$this->data = $_POST['data'];

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
				$aux = array_keys($_FILES['data']['name']);
				$fileModel = $aux[0];
				if($fileModel===0){
					$fileModelKeys = array_keys($_FILES['data']['name'][0]);
					$fileModel = $fileModelKeys[0];
				}
				$fileModelData = get_class_vars($fileModel);
				if (isset($retFiles) && !empty($retFiles)){
					if (!isset($retFiles[0])){
						// For one file
						foreach($retFiles as $key => $eachFile){
							if(strpos($eachFile['type'],'image') !== false){
								if($fileModelData['thumbs']){
									foreach($fileModelData['thumbs'] as $thumbOptions){
										$this->Resize->createThumbs($thumbOptions, $eachFile);
									}
								}
							}
							$this->data[$fileModel][$key] = $eachFile['file'];
						}
					}else{
						// For many files
						foreach($retFiles as $keyOne => $eachFiles){
							foreach($eachFiles as $key => $eachFile){
								if(strpos($eachFile['type'],'image') !== false){
									if($fileModelData['thumbs']){
										foreach($fileModelData['thumbs'] as $thumbOptions){
											$this->Resize->createThumbs($thumbOptions, $eachFile);
										}
									}
								}
								$this->data[$fileModel][$keyOne][$key] = $eachFile['file'];
							}
						}
					}
					
				}
				unset ($_FILES);
			}
			
			// This data validation
			if (isset($this->$model->validate)){
				if (!empty($this->$model->validate)){
					$validation = $this->$model->validate;
					$this->errors =	$this->Validator->validate($validation, $this->data, $model);
					$this->$model->errors = $this->errors;
				}
			}
		}else{
			$this->data = null;
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
		$this->_template->data = $this->data;
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
	 * @ignore
	 */
	function __destruct() {
	 	$this->_template->render();
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
		$output.= "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
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
			$url = Inflector::array_to_path($url);
		}
		$this->_template->_redirect = $url;
		header('Location:'.$url);
	}
	
}

