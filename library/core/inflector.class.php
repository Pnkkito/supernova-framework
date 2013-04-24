<?php
/**
 * Supernova Framework
 */
/**
 * Inflect strings or arrays
 *
 * @package Inflector
 */
class Inflector {
	
	/**
	 * Get Query from URL
	 *
	 * @return string
	 */
	public static function getQueryUrl(){
		$url = $_SERVER['QUERY_STRING'];
		$pos = strrpos($url, "url=");
		$url = ($pos !== false) ? str_replace('url=','',$url) : '';
		return $url;
	}

	/**
	 * Get Base Path
	 *
	 * Get the static location of your application in your server
	 *
	 * @return string
	 */
	public static function getBasePath(){
		$ServerPath = $_SERVER['DOCUMENT_ROOT'];
        $ScriptPath = substr(dirname($_SERVER['SCRIPT_FILENAME']), strlen($ServerPath));
		$ScriptPath = str_replace(WEBROOT,'',$ScriptPath);
		if (substr($ScriptPath,0,1) == '/'){
			return substr($ScriptPath,1);
		}else{
			return $ScriptPath;
		}
	}

	/**
	 * Get Table name
	 * @param	string	$modelName	Model Name
	 * @return	object			Parse table name		
	 */
	public static function tableName($modelName){
		$modelVars = get_class_vars($modelName);
		$index = $modelVars['index'];
		$val = DBPREFIX.$index.DBS.strtoupper(Inflector::camel_to_under(Inflector::pluralize($modelName)));
		return $val;
	}
	
	/**
	 * Get model prefix
	 *
	 * Get the model prefix from the database and its index
	 *
	 * @param string $modelName Model name
	 * @return string
	 */
	public static function getModelPrefix($modelName){
		if (!empty($modelName) && class_exists($modelName)){
			$modelVars = get_class_vars($modelName);
			return DBPREFIX.$modelVars['index'].DBS;
		}
	}

	/**
	 * Unparse Fields
	 *
	 * Get the human readable fields from prefixed table in the model
	 * @param string $val field
	 * @param string $modelName Model name
	 * @return mixed $newval Parsed field
	 */
	public static function unparseFields($val,$modelName){
		if (is_array($val)){
			$newval = $val;
			foreach($newval as $_k => $_v){
				$new_key = Inflector::unparseFields($_k,$modelName);
				$newval[$new_key] = $_v;
				unset($newval[$_k]);
			}
			return $newval;
		} else {
			$auxData = get_class_vars($modelName);
			$has = $auxData['hasMany'];
			$belongs = $auxData['belongsTo'];
			$prefix = Inflector::getModelPrefix($modelName);
			if (strpos($val,$prefix) !== false){
				return str_replace($prefix,'',$val);
			}else{
				if (isset($has) && !empty($has) ){
					foreach ($has as $eachModel){
						$foreingPrefix = Inflector::getModelPrefix($eachModel);
						if (strpos($val,$foreingPrefix) !== false){
							return $eachModel.DBS.str_replace($foreingPrefix,'',$val);
						}
					}	
				}
				if (isset($belongs) && !empty($belongs) ){
					foreach ($belongs as $eachModel){
						$foreingPrefix = Inflector::getModelPrefix($eachModel);
						if (strpos($val,$foreingPrefix) !== false){
							return $eachModel.DBS.str_replace($foreingPrefix,'',$val);
						}
					}	
				}
			}
		}
	}

	/**
	 * Generate relative url path from array (DEPRECATED)
	 * @param	array	$path	Parse array to form correct path
	 * @return	string		Parsed path
	 */
	public static function generateUrl($path){
		//Find any route
		$routings = explode(';',ROUTES);
		$route = false;
		if (!empty($routings)){
			foreach ($routings as $routing){
				$pos = null;
				$pos = strpos($path['action'], $routing."_");
				if ($pos !== false){
					$route = $routing;
					$path['action'] = str_replace($routing.'_','',$path['action']);
					$path[$routing] = $routing;
				}
				if (array_key_exists($routing, $path)){
					$path['action'] = str_replace($routing.'_','',$path['action']);
					$path[$routing] = $routing;
					$route = $routing;
				}
			}	
		}

		if (is_array($path)){
			$x=0;
			foreach ($path as $k => $v){
				if ((string)$k != (string)$route){
					switch ((string)$k){
						case 'language' : $newpath[0] = $v; break;
						case 'plugin': $newpath[2] = $v; break;
						case 'controller': $newpath[3] = $v; break;
						case 'action': $newpath[4] = $v; break;
						case 'params' : break;
						default: $newpath[$x+5]= $v ; break;
					}
				}
				$x++;
			}
			if (!empty($route) || $route != false){ $newpath[1] = $route; }

			ksort($newpath);
			$path = implode('/',$newpath);
		}else{
			$path = str_replace(' ','-',$path);	
		}

		return SITE_URL.Inflector::getBasePath().$path;
	}

	/**
	 * Transform Array to String Path (DEPRECATED)
	 * @param	array	$path	Parse array to form correct path
	 * @return	string		Parsed path
	 */
	public static function array_to_path($path){
		return Inflector::generateUrl($path);
	}

	/**
	* Transform Camelized string to Underscore
	* @param string $str Text to inflect
	* @return string $str Inflected text
	**/
	public static function camel_to_under($str){
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
	
	/**
	* Transform Camelized string to relative path
	* @param string $str Text to inflect
	* @return array $aux2 Inflected array
	**/
	public static function camel_to_array($str){
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "/" . strtolower($c[1]);');
		$aux = preg_replace_callback('/([A-Z])/', $func, $str);
		$aux2 = explode('/',$aux);
		return $aux2;
	}
	
	/**
	 * Transform Underscore string to Camelized
	 * @param	String	$str	String to parse
	 * @param	Boolean	$capitaliseFirst	Capitalize first character
	 * @return	String				Inflected string
	 */
	public static function under_to_camel($str, $capitaliseFirst = true) {
		if($capitaliseFirst) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');
		return preg_replace_callback('/_([a-z])/', $func, $str);
	}
	
	/**
	* Singularize string
	* @param string $str Text to inflect
	* @return string $str Inflected text
	**/
	public static function singularize($str){
		//$origin = array('/([rln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeiou])s([A-Z]|_|$)/'); //Spanish inflector
		$origin = array('/([ln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeioun])s([A-Z]|_|$)/'); //English inflector
		$destiny = array('\1\2','\1is','\1iz','\1','\1\2');
		$str = preg_replace($origin,$destiny,$str);
		return $str;	    
	}
		
	/**
	* Pluralize string
	* @param string $str Text to inflect
	* @return string $str Inflected text
	**/
	public static function pluralize($str){
		// $origin = array('/([rtbaeiou])([A-Z]|_|$)/','/([rlnd])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/'); //Spanish inflector
		$origin = array('/([rtbaeioun])([A-Z]|_|$)/','/([rld])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/'); //English inflector
		$destiny = array('\1s\2','\1es\2','\1es','\1ces');
		$str = preg_replace($origin,$destiny,$str);
		return $str;
	}

	/**
	 * Slug a word
	 * @param	String	$str	Text to slug
	 * @return	String		Sluged text
	 */
	public static function slug($str){
		$str = strtolower(trim($str));
		$str = preg_replace('/[^a-z0-9-]/', '-', $str);
		$str = preg_replace('/-+/', "-", $str);
		return $str;
	}
	
	/**
	 * Youtube Code
	 * @param	String	$str	Url from Youtube
	 * @return	String	Extracted code
	 */
	public static function youtubeCode($str){
		parse_str( parse_url( $str, PHP_URL_QUERY ), $my_array_of_vars );
		return $my_array_of_vars['v'];    
		// Output: C4kxS1ksqtw
	}

	/**
	 * Get Model name from Controller name
	 * @param 	String 	$str 	Controller name
	 * @return  String  Model name
	 */
	public static function getModelFromController($controllerName){
		return ucfirst(Inflector::singularize($controllerName));
	}

	public static function getControllerFromModel($modelName){
		$modelName[0] = strtolower($modelName[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');
		$strName = preg_replace_callback('/([A-Z])/', $func, $modelName);
		$name = strtolower($strName);
		return $name;
	}
}