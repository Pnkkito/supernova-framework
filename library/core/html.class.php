<?php
/**
 * Supernova Framework
 */
/**
 * Template handler
 * 
 * @package MVC_View_Html
 */
class Html {
	/**
	 * Javascript objects
	 * @var Array	
	 */
	private $js = array();
	
	/**
	 * Controller name in case Form use
	 * @var String
	 */
	private $_formController = null;
	
	/**
	 * Controller and module name loaded in the instance
	 * @var Array
	 */
	protected $cm = array();
	
	/**
	 * Validation errors
	 * @var Array	
	 */
	public $errors = array();
	
	/**
	 * Post Data
	 * @var mixed	
	 */
	public $data;

	/**
	 * Scripts for views
	 * @var Array
	 */
	public $scripts;

	/**
	 * Translate class
	 * @var object	
	 */
	public $translater;


	function __construct(){
		$this->translater = new Translate;
	}
	/**
	* Create tinyURL url
	* 
	* @param string $url url
	* @return string $url Return tinuURL
	*/
	function tinyUrl($url) {
		$url = preg_replace_callback('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', array(get_class($this), '_fetchTinyUrl'), $url);
		return $url;
	}

	/**
	 * Create tinyUrl with CURL
	 * @ignore
	 * @param	string	$url	Url
	 */
	private function _fetchTinyUrl($url) { 
		$ch = curl_init(); 
		$timeout = 10; 
		curl_setopt($ch,CURLOPT_URL,'http://tinyurl.com/api-create.php?url='.$url[0]); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
		$data = curl_exec($ch); 
		curl_close($ch); 
		return '<a href="'.$data.'" target = "_blank" >'.$data.'</a>'; 
	}

	/**
	 * Setter
	 * Sets Controller and Model values for use in html class
	 * @ignore
	 */
	function __set($val, $var){
		$this->cm[$val] = $var;
	}
	
	/**
	 * Getter
	 * @ignore
	 */
	function __get($var){
		return $var;
	}
	
	/**
	 * Create Form
	 * @param	String	$type	Create, Submit or End forms
	 * @param	String	$name	Form name
	 * @param	Array	$extras	Extra elements
	 * @return	object		Form element
	 */
	function form($type, $name = null, $extras = array() ){
		switch($type){
			case 'create':
					if (!$name){
						$name = ucfirst($this->cm['controller']).ucfirst($this->cm['action']);
						$path = $this->cm;
						$this->_formController = $this->cm['controller'];
					}else{
						$path = Inflector::camel_to_array($name);
						$this->_formController = Inflector::pluralize($path[0]);
					}
					$data = "<form name='".$name."' action='".Inflector::array_to_path($path)."' enctype='multipart/form-data' method='POST'>";
					break;
			case 'submit':	if (!$name){
						$name = 'Submit';
					}
					if (!empty($extras)){
						$extrasParsed = '';
						foreach ($extras as $k => $v){
							$extrasParsed.=$k."='".$v."'";
						}
					}
					$data = "<div><input type='submit' class='btn btn-large' value='$name' $extrasParsed /></div>";
					break;
			case 'end':	$data = "</form>";
					$this->_formController = null;
					break;
		}
		return $data;
	}
	
	/**
	* Create Link
	* 
	* @param string $text
	* @param array $path
	* @param array $extras
	* @return string $data link element
	*/
	function link($text,$path, $extras = array()) {
		$path = (is_array($path)) ? Inflector::array_to_path($path) : $path;
		// $path = ( strpos($path,'http://') !== false) ? $path : SITE_URL.Inflector::getBasePath().$path;
		$ext = '';
		$confirmMessage = null;
		if(!empty($extras)){
			if (isset($extras['prompt']) && !empty($extras['prompt'])){
				$confirmMessage = $extras['prompt'];
				unset ($extras['prompt']);
			}
			foreach($extras as $key => $extra){
				if($key != ''){
					$ext .= "$key = '$extra'";
				}
			}
			
		}
		if ($confirmMessage) {
			$data = '<a href="'.$path.'" '.$ext.' onclick="javascript:return confirm(\''.$confirmMessage.'\')">'.utf8_decode($text).'</a>';
		} else {
			$data = '<a href="'.$path.'" '.$ext.'>'.$text.'</a>';	
		}
		return $data;
	}
	
	/**
	* Create Image
	* 
	* @param string $image image name in /img/
	* @param array $args if key in array is 'url', the image turn into a link image
	* @return string $data image element
	*/
	function image($image, $args = null){
		if (is_array($args)){
			$ea = "";
			foreach ($args as $k => $v){
				if ($k!='url'){
					$ea.= " $k='$v'";
				}
			}
		}
		if (isset($args['url'])){
			$data = '<a href="'.Inflector::array_to_path($args['url']).'">';
			$data.= '<img src="'.SITE_URL.Inflector::getBasePath().'img/'.$image.'" '.$ea.'/>';
			$data.= '</a>';
		}else{
			$data.= '<img src="'.SITE_URL.Inflector::getBasePath().'img/'.$image.'" '.$ea.'/>';	
		}
		return $data;
	}
	
	/**
	 * Create Input
	 * @param	String	$name	Database name
	 * @param	String	$title	Label name
	 * @param	Array	$items	Extra items for input
	 * @return	object		Input element
	 */
	function input($name, $title = '', $items = array('type' => 'text')){
		if(!is_array($name)){
			$explodeName = explode(".",$name);
			if (count($explodeName)>1){
				$model = $explodeName[0];
				$name = $explodeName[1];
			}else{
				$model = ucfirst(Inflector::singularize((!$this->_formModel)?$this->cm['controller']:$this->_formController));
			}	
		} else {
			foreach($name as $n){
				$explodeName = explode(".",$n);
				if (count($explodeName)>1){
					$modelArr[] = $explodeName[0];
					$nameArr[] = $explodeName[1];
				}else{
					$nameArr[] = $n;
					$modelArr[] = ucfirst(Inflector::singularize((!$this->_formModel)?$this->cm['controller']:$this->_formController));
				}	
			}
		}

		
		
		$data ="<div>";
		$ext='';
		
		$errorClass = '';
		if(!is_array($name)){
			if(!empty($this->errors[$name])){
				foreach($this->errors[$name] as $errorKey => $val){
					if($val){
						$errorClass[] = $errorKey;
					} 
				}
				if(!empty($errorClass)){
					$errorClass[] = 'error';
					$errorClass = implode(' ',$errorClass);
				} else $errorClass = "";
				if(!empty($items['class'])){
					$items['class'] = $items['class']." ".$errorClass;
				} else {
					$items['class'] = $errorClass;
				}
			}	
		} else {
			foreach($name as $n){
				if(!empty($this->errors[$n])){
					foreach($this->errors[$n] as $errorKey => $val){
						if($val){
							$errorClass[] = $errorKey;
						} 
					}
					if(!empty($errorClass)){
						$errorClass[] = 'error';
						$errorClass = implode(' ',$errorClass);
					} else $errorClass = "";
					if(!empty($items['class'])){
						$items['class'] = $items['class']." ".$errorClass;
					} else {
						$items['class'] = $errorClass;
					}
				}
			}
		}

		
		
		$value='';
		// foreach ($items as $k => $v){
		// 	$$k = $v;
		// }
		extract($items);
		
		if(empty($type)){
			$type="text";
		}
		
		$check = ($type == 'checkbox') ? '[]' : '';

		/* Adding extra items to the input */
		$ext = '';
		if(!empty($items)){
			foreach($items as $key => $extra){
				if($key != '' && $extra != $type){
					$ext .= "$key = '$extra'";
				}
			}
			
		}
		if(!is_array($name)){
			if (isset($this->data[$model][$name])){
				$value = $this->data[$model][$name];
			}	
		}
		

		if($type != 'textarea'){
			if($type != 'daterange'){
				if($type != 'submit' && $type != 'hidden' ){
				//	if (!empty($title) && $type != 'checkbox'){
					$data .= "<label for='$name'>$title</label>";
				//	}
				}
				//$data .= ($type == 'checkbox') ? '<p class="checkboxtext">'.$title.'</p>' : '';
				$data .= "<input type='$type' name='data[$model][$name]$check' value='$value' id='$name' $ext/>";
				if($this->errors[$name]){
					$errorArr = array_unique($this->errors[$name]);
					$data .= "<span class='errorSpan'>".implode('<br />',$errorArr)."</span>";
				}
			} else {
				if(is_array($name)){
					$data = "";
					foreach($name as $k => $n){
						if (isset($this->data[$model][$n])){
							$value[$k] = $this->data[$model][$n];
						}
						$idStr[] = "#".str_replace('.', '', $n);
						$data .="<div>";
						$data .= "<label for='$n'>".$title[$k]."</label>";
						$data .= "<input type='datetime' name='data[".$modelArr[$k]."][".$nameArr[$k]."]' value='".$value[$k]."' id='".str_replace('.', '', $n)."' $ext/>";
						if($this->errors[$n]){
							$errorArr = array_unique($this->errors[$n]);
							$data .= "<span class='errorSpan'>".implode('<br />',$errorArr)."</span>";
						}
						$data .= "</div>";
					}
					$data.= "
							<script>
								$.datepicker.regional['es'] = {
									closeText: 'Cerrar',
									prevText: '<Anterior',
									nextText: 'Siguiente>',
									currentText: 'Hoy',
									monthNames: ['Enero','Febrero','Mаrzo','Abril','Mаyo','Junio',
									'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
									monthNamesShort: ['Ene','Feb','Mаr','Abr','Mаy','Jun',
									'Jul','Ago','Sep','Oct','Nov','Dic'],
									dayNames: ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
									dayNamesShort: ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'],
									dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
									weekHeader: '',
									dateFormat: 'dd/mm/yy',
									firstDay: 1,
									isRTL: false,
									showMonthAfterYear: false,
									yearSuffix: ''
								};
								$.datepicker.setDefaults($.datepicker.regional['es']); 
					
								 $( '".$idStr[0]."' ).datepicker({
						            defaultDate: '+1w',
						            changeMonth: true,
						            onClose: function( selectedDate ) {
						                $( '".$idStr[1]."' ).datepicker( 'option', 'minDate', selectedDate );
						            }
						        });
						        $( '".$idStr[1]."' ).datepicker({
						            defaultDate: '+1w',
						            changeMonth: true,
						            onClose: function( selectedDate ) {
						                $( '".$idStr[0]."' ).datepicker( 'option', 'maxDate', selectedDate );
						            }
						        });

							</script>
							";
							return $data;
				}
			}

		} else {
			if (!empty($title)){
				$data .= "<label for='$name'>$title</label>";
			}
			$data .= "<textarea name='data[$model][$name]$check' $ext>$value</textarea>";
			if($this->errors[$name]){
				$errorArr = array_unique($this->errors[$name]);
				$data .= "<span class='errorSpan'>".implode('<br />',$errorArr)."</span>";
			}
		}
		$data .="</div>";
		
		if ($type == 'datetime'){
			$data.= "
			<script>
				$.datepicker.regional['es'] = {
					closeText: 'Cerrar',
					prevText: '<Anterior',
					nextText: 'Siguiente>',
					currentText: 'Hoy',
					monthNames: ['Enero','Febrero','Mаrzo','Abril','Mаyo','Junio',
					'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
					monthNamesShort: ['Ene','Feb','Mаr','Abr','Mаy','Jun',
					'Jul','Ago','Sep','Oct','Nov','Dic'],
					dayNames: ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'],
					dayNamesShort: ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'],
					dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
					weekHeader: '',
					dateFormat: 'dd/mm/yy',
					firstDay: 1,
					isRTL: false,
					showMonthAfterYear: false,
					yearSuffix: ''
				};
				$.datepicker.setDefaults($.datepicker.regional['es']);
				
				
				$(\"input[type='datetime']\").datepicker();
				
			</script>
			";
			
			//$(\"input[type='date']\").datetimepicker('setDate', (new Date()));
		}
		
		return $data;
	}
	
	/**
	 * Create Ajax Input (adding and removing dinamicly)
	 * @param	String	$name	Database name
	 * @param	String	$title	Label name
	 * @param	Array	$items	Extra items for input
	 * @return	object		Input element
	 */
	function ajaxInput($name, $title = '', $items = array('type' => 'text')){
		$parseName = explode('.',$name);
		if (count($parseName) > 0){
			$model = $parseName[0];
			$name = $parseName[1];
		}else{
			if (!$this->_formModel){
				$model = ucfirst(Inflector::singularize($this->cm['controller']));
			}else{
				$model = ucfirst(Inflector::singularize($this->_formController));;
			}	
		}
		
		$data ="<div rel='ajax".$model.$name."' style='clear:both; float:left;'>";
		
		$ext='';
		$value='';
		foreach ($items as $k => $v){
			$$k = $v;
		}
		if(empty($type)){
			$type="textbox";
		}
		if ($type == 'checkbox'){
				$check = '[]';
		}else{
			$check = '';
		}
		
		/* Adding extra items to the input */
		$ext = '';
		if(!empty($items)){
			foreach($items as $key => $extra){
				if($key != '' && $extra != $type){
					$ext .= "$key = '$extra'";
				}
			}
			
		}
		
		$inputVar = "<div style='float:left;'>";
		if($type != 'submit' && $type != 'hidden' ){
			if (!empty($title)){
				$inputVar.= "<label for='$name'>$title</label>";
				}
			}
		$inputVar.= "<input style='width:580px !important;' type='$type' name='data[$model][][$name]$check' $ext/><a style='float:left;' href='#' rel='".$model.$name."DEL' >Remove</a>";
		$inputVar.= "</div>";
		//if($type != 'textarea'){
		//	if($type != 'submit' && $type != 'hidden' ){
		//		if (!empty($title)){
		//		$data .= "<label for='$name'>$title</label>";
		//		}
		//	}
		//	
		//	$data .= "<input type='$type' name='data[$model][$name]$check' id='$name' $ext/>";
		//} else {
		//	if (!empty($title)){
		//		$data .= "<label for='$name'>$title</label>";
		//	}
		//	$data .= "<textarea name='data[$model][][$name]$check' $ext>$value</textarea>";
		//}
		$data .="</div>";
		
		/* Ajax script */
		$data .="
		<a href='#".$model.$name."ADD' rel='".$model.$name."ADD' />Add</a>
		<script>
			$('a[rel=\"".$model.$name."ADD\"]').live('click', function(e){
				e.preventDefault();
				$('div[rel=\"ajax".$model.$name."\"]').append(\"".$inputVar."\");
			});
			
			$('a[rel=\"".$model.$name."DEL\"]').live('click', function(e){
				e.preventDefault();
				$(this).parent('div').remove();
			});
		</script>
		";
		
		return $data;
	}	
	
	/**
	 * Create automagic table (normal or for forms)
	 * @param	Array	$data	Array from find result
	 * @param	Array	$options
	 * @return	object		Formed html table
	 */
	function table($data = array(), $options = array()){
		$fields = ($options['fields']) ? $options['fields'] : null;
		$fielded = ($options['fields']) ? true : false;
		$type = ($options['type']) ? $options['type'] : false;
		$actions = ($options['actions']) ? $options['actions'] : false;
		$class = ($option['class']) ? $options['class'] : 'table';  
		if (!empty($data)){
			$modelName = array_keys($data);
			$modelName = $modelName[0];
			$modelData = get_class_vars($modelName);
			$modelFK = $modelData['primaryKey'];
			
			if (!$this->_formController){
				$controller = $this->cm['controller'];
			}else{
				$controller = $this->_formController;
			}
			
			//Find any route
			$routings = explode(';',ROUTES);
			if (!empty($routings)){
				foreach ($routings as $routing){
					$pos = strpos($this->cm['action'], $routing."_");
					if ($pos !== false){
						$route = $routing;
					}
				}
			}

			$out ="<table class='".$class."'>";
			
			/* Head */
			$out.="<thead>";
			$out.="<tr>";
			
			if ($type){
				$out.="<th style='width: 20px;'>&nbsp</th>";
			}
			
			if (!empty($fields)){
				$keyFields = array_keys($fields);
			}else{
				if (!empty($data) && is_array($data)){
					foreach ($data[$modelName] as $level){
						$fields = array_keys($level);
						$keyFields = array_keys($level);
					}
				}
			}
			
			if (!empty($fields)){
				foreach ($fields as $k => $fieldName){
					$out.="<th>";
					$out.= $fieldName;
					$out.="</th>";
				}
			}
			
			if ($actions){
				$out.="<th>";
				$out.="Actions";
				$out.="</th>";
			}
			
			$out.="</tr>";
			$out.="</thead>";
			
			/* Body */
			$out.="<tbody>";
			if (!empty($data)){
				foreach ($data[$modelName] as $level){
					$out.="<tr>";
					if ($type){
						$out.="<td style='width: 20px;'>";
						if ($type == 'radio'){
							$out.=$this->input('Radio','',array('type' => 'radio','value' => $level[$modelFK]));
						}
						if ($type == 'checkbox'){
							$out.=$this->input('Checkbox','',array('type' => 'checkbox','value' => $level[$modelFK]));
						}
						$out.="</td>";
					}

					if (!$fielded){
						foreach ($level as $k => $v){
							$checkID = strpos($k,'_ID');
							if ($checkID !== false){
								$modelName2 = str_replace('_ID','', $k);
								$modelData = get_class_vars($modelName2);
								$v = $level[$modelName2][0][$modelData['displayField']];
							}
							if (in_array($k,$keyFields)){
								$out.="<td>";
								if ($v !== null){
									if (is_array($v) && isset($v['thumb'])){
										$out.='<img src="/'.$v['thumb'].'" />';
									}else{
										$out.=$v;
									}
								}else{
									$out.="--";
								}
								$out.="</td>";
							}
						}
					}else{
						foreach ($fields as $k => $v){

							$checkID = strpos($k,'_ID');
							if ($checkID !== false){
								$modelName2 = str_replace('_ID','', $k);
								$modelData = get_class_vars($modelName2);
								$level[$k] = $level[$modelName2][0][$modelData['displayField']];
							}

							if (array_key_exists($k,$level)){
								$out.="<td>";
								if ($level[$k] !== null){
									if (is_array($level[$k]) && isset($level[$k]['thumb'])){
										$out.='<img src="/'.$level[$k]['thumb'].'" />';
									}else{
										$out.=$level[$k];
									}
								}else{
									$out.="--";
								}
								$out.="</td>";
							}
						}
					}

					if ($actions){
						$out.="<td>";
						if (!is_array($actions)){
							$out.=$this->link('<i class="icon-edit"></i> Edit', array('controller' => $controller, 'action' => 'edit', $route => true, $level[$modelFK]),array('class' => 'btn btn-mini'));
							$out.="&nbsp;";
							$out.=$this->link('<i class="icon-remove"></i> Delete', array('controller' => $controller, 'action' => 'delete', $route => true, $level[$modelFK]),array('class' => 'btn btn-mini'));	
						}else{
							foreach ($actions as $name => $action){
								$out.=$this->link($name, array('controller' => $controller, 'action' => $action, $route => true, $level[$modelFK]));
							}
						}
						$out.="</td>";
					}
					$out.="</tr>";
				}
			}
			$out.="</tbody>";
			$out.="</table>";
		}
		return $out;
		
	}
	
	/**
	 * Formats date
	 * @param	String	$date	Date to format
	 * @param	String	$format	Date format
	 */
	public function date($date, $format = null){
		$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
		$days = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
		$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
		$dateFormat = ($format) ? $format : "l d \d\e F \d\e\l Y";
		$time = ($date) ? strtotime($date) : time();

		$returnDate = date($dateFormat, $time);
		
		
		$returnDate = str_replace($days, $dias, $returnDate);
		$returnDate = str_replace($months, $meses, $returnDate);
		return $returnDate;
	}
	
	/**
	 * Create Select element
	 * @param	String	$name	Database name
	 * @param	String	$title	Title for label
	 * @param	Array	$values	Options for select
	 * @return	object		Select html element
	 */
	function select($name, $title = '', $values){
		//Parsing Name
		$explodeName = explode(".",$name);
		if (count($explodeName)>1){
			$model = $explodeName[0];
			$name = $explodeName[1];
		}else{
			$model = ucfirst(Inflector::singularize((!$this->_formModel)?$this->cm['controller']:$this->_formController));
		}

		$output ="<div class='grid_16' style='clear:both; float:left;'>";
		$output.= "<label for='$name'>$title</label>";
		$output2 = $output;
		$options = '';

		$selected = (array_key_exists('selected', $values))?$values['selected']:null;
		if (empty($selected)){
			if (isset($this->data[$model][$name]) && !empty($this->data[$model][$name])){
				$selected = $this->data[$model][$name];
			}
		}

		$checkboxed = (array_key_exists('type', $values) && $values['type']=='checkbox') ? true : false;
		
		if (array_key_exists('options', $values) && !empty($values['options'])){
			if (array_key_exists('empty', $values) && !empty($values['empty'])){
				if (is_bool($values['empty'])){
					$options.="<option values=''> --- </options>";
				}else{
					$options.="<option values=''> ".$values['empty']." </options>";
				}
			}
			foreach ($values['options'] as $k => $v){
				if (!$checkboxed){
					if (is_array($selected)){
						$find = array_search($k, $selected);
						$options.="<option ".(($find !== false)?"selected":"")." value='$k'>$v</option>";
					}else{
						$options.="<option ".(($selected == $k)?"selected":"")." value='$k'>$v</option>";
					}
				}else{
					if (is_array($selected)){
						$find = array_search($k, $selected);
						if ($find !== false){
							$output2.=$this->input($model.'.'.$name,$v,array('value' => $k,'type' => 'checkbox', 'checked' => 'checked' ));	
						}else{
							$output2.=$this->input($model.'.'.$name,$v,array('value' => $k,'type' => 'checkbox'));	
						}
					}else{
						if ($selected == $k){
							$output2.=$this->input($model.'.'.$name,$v,array('value' => $k,'type' => 'checkbox', 'checked' => 'checked' ));
						}else{
							$output2.=$this->input($model.'.'.$name,$v,array('value' => $k,'type' => 'checkbox'));
						}
					}	
				}
			}
		}

		$extras = '';
		$multipleValues = false;

		if (array_key_exists('type', $values)){
			if ($values['type']=='multiple'){
				$multipleValues = true;
				$extras.=" multiple ";
			}
		}

		if (array_key_exists('class', $values)){
			$extras.=" class='".$values['class']."' ";
		}

		if (array_key_exists('name', $values)){
			$extras.=" name='".$values['name']."' ";
		}

		$output.= "<select $extras name='data[$model][$name]";
		$output.= ($multipleValues) ? "[]" : "";
		$output.= "'>";
		$output.= $options;
		$output.= "</select>";

		$output.= "</div>";

		if ($checkboxed){
			return $output2;
		}else{
			return $output;
		}
	}

	/**
	 * Include JavaScript file in html
	 * @param	String	$fileName	Filename without extension
	 */
	function includeJs($fileName) {
		$data = '<script src="'.SITE_URL.Inflector::getBasePath().'js/'.$fileName.'.js"></script>';
		return $data;
	}

	/**
	 * Include JavaScript in html
	 * @param 	String 	$script 	Script
	 * @param 	String 	$position 	Where to include the script (start,end,head)
	 */
	function includeScript($script, $position = "end"){
		$script = "<script>".$script."</script>";
		$positions = array('head','start','end');
		foreach ($positions as $eachPosition){
			$this->scripts[$eachPosition] = ($position == $eachPosition) ? $script : '';
		}
	}

	/**
	 * Include CSS in html
	 * @param	String	$fileName	Filename without extension
	 * @param	String	$media		CSS type
	 */
	function includeCss($fileName, $media ='screen') {
		$data =  '<link rel="stylesheet" href="'.SITE_URL.Inflector::getBasePath().'css/'.$fileName.'.css" type="text/css" media="'.$media.'">';
		return $data;
	}
	
	/**
	 * Create BreadCrumbs
	 * @param	Object	$steps	Steps
	 */
	function breadcrumbs($steps = array()){
		if(empty($steps)){
			global $url ;
			$urlArray = explode("/",$url);

			//Find any route
			$routings = explode(';',ROUTES);
			if (!empty($routings)){
				$route = false;
				foreach ($routings as $routing){
					$pos = strpos($this->cm['action'], $routing."_");
					if ($pos !== false){
						$route = true;
					}
				}
			}

			if($route){
				$breadcrumb[0] = $urlArray[1];
				$breadcrumb[1] = $urlArray[2];
			} else{
				$breadcrumb[0] = $urlArray[0];
				$breadcrumb[1] = $urlArray[1];
			}
			$ret = '<div class="breadcrumb">';
			foreach($breadcrumb as $key => $bread){
				$link='';
				for($i = 0; $i <= $key; $i++ ){
					$link.=$breadcrumb[$i].'/';
				}
				$ret .= $this->link($bread, $link)."";
			}
			$ret .= '</div>';
		} else {
			$ret = '<div class="breadcrumb">';
			$i = 0;
			foreach($steps as $bread){
				
				foreach($bread as $k2 => $c){
					if($i < count($steps)-1){
						$ret .= $this->link($k2, $c)."&raquo;";	
					} else {
						$ret .= $k2;
					}
				}
				$i++;
			}
			$ret .= '</div>';
		}
		return $ret;		
	}
	
	/**
	 * Method to obtain Youtube frame
	 *
	 * @param string $url Youtube URL
	 * @param string $type Youtube ID type
	 * @return object 	Youtube iframe
	 */
	public function parseYoutube($url ="", $type = 'id'){
		if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
			$video_id = $match[1];
		}
		if(empty($ext)){
			$args['width'] = 480;
			$args['height'] = 315;
		} else {
			$args = $ext;

		}
		foreach($args as $key => $arg){
			$extras .= $key.'="'.$arg.'" ';
		}
		if($type != 'image'){
			if($type== 'id'){
				return $video_id;
			} else {
				return '<iframe '.$extras.' src="http://www.youtube.com/embed/'.$video_id.'?wmode=opaque"></iframe>';		
			}
		} else {
			return 'http://img.youtube.com/vi/'.$video_id.'/2.jpg';
		}	
	}

	function translate($str = ''){
		return $this->translater->__($str);
	}
}
