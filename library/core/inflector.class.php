<<<<<<< Updated upstream
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
	public static function getQueryString(){
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
		$val = Inflector::getModelPrefix($modelName).strtoupper(Inflector::camel_to_under(Inflector::pluralize($modelName)));
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
			$index = (isset($modelVars['index'])) ? $modelVars['index'] : '';
			$prefix = ( defined('DB_PREFIX') && DB_PREFIX != '') ? DB_PREFIX : '';
			return ( !empty($index) && !empty($prefix) ) ? $prefix.$index.DBS : '';
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
	 * Get route name from path
	 * @param  Array  $path Path array
	 * @return String       String with route name
	 */
	public static function getRoute($path){
		global $_routingData;
		if (!empty($_routingData['routes'])){
			if (isset($path[0]) && in_array($path[0], $_routingData['routes'])){
				return $path[0];
			}
		}
		return false;
	}

	/**
	 * Get action name from path
	 * @param  Array  $path Path array
	 * @return String       String with action name
	 */
	public static function getAction($path){
		$route = false;
		global $_routingData;
		if (!empty($_routingData['routes'])){
			if (in_array($path[0], $_routingData['routes'])){
				$route = $path[0];
			}
		}
		array_shift($path);
		array_shift($path);
		$actionName = empty($path[0]) ? $_routingData['defaultAction'] : $path[0];
		return ($route) ? $route."_".$actionName : $actionName;
	}

	/**
	 * Get controller name from path
	 * @param  Array  $path Path array
	 * @return String       String with controller name
	 */
	public static function getController($path){
		global $_routingData;
		if (!empty($_routingData['routes'])){
			if (in_array($path[0], $_routingData['routes'])){
				array_shift($path);
			}
		}
		return empty($path[0]) ? $_routingData['defaultController'] : $path[0];
	}

	/**
	 * Generate relative url from array
	 * TODO: Routings
	 * @param	array	$path	Parse array to form correct path
	 * @return	string			Url
	 */
	public static function generateUrl($path){
		if (is_array($path)){
			$newpath = array();	$x=0;
			foreach ($path as $k => $v){
				switch ((string)$k){
					case 'language' : $newpath[0] = $v; break;
					case 'route' : $newpath[1] = $v; break;
					case 'plugin': $newpath[2] = $v; break;
					case 'controller': $newpath[3] = $v; break;
					case 'action': $newpath[4] = $v; break;
					case 'params' : break;
					default: $newpath[$x+5]= $v ; break;
				}
				$x++;
			}
			ksort($newpath);
			$path = implode('/',$newpath);
		}else{
			$path = str_replace(' ','-',$path);	
		}
		return SITE_URL.Inflector::getBasePath().'/'.$path;
	}

	/**
	 * Transform Array to String Path (DEPRECATED)
	 * @param	array	$path	Parse array to form correct path
	 * @return	string		Parsed path
	 * @deprecated
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
		if (is_string($str) && !empty($str)){
			$str[0] = strtolower($str[0]);
			$func = create_function('$c', 'return "_" . strtolower($c[1]);');
			return preg_replace_callback('/([A-Z])/', $func, (string)$str);
		}else{
			return $str;
		}
	}
	
	/**
	* Transform Camelized string to relative path
	* @param string $str Text to inflect
	* @return array $aux2 Inflected array
	**/
	public static function camel_to_array($str){
		if (is_string($str) && !empty($str)){
			$str[0] = strtolower($str[0]);
			$func = create_function('$c', 'return "/" . strtolower($c[1]);');
			$aux = preg_replace_callback('/([A-Z])/', $func, $str);
			$aux2 = explode('/',$aux);
			return $aux2;
		}else{
			return $str;
		}
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
		switch (LANGUAGE){
			case 'es' : // Spanish inflector
						$origin = array('/([rln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeiou])s([A-Z]|_|$)/');
						break;

			default   : // English inflector
						$origin = array('/([ln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeioun])s([A-Z]|_|$)/');
						break; 
		}
		$destiny = array('\1\2','\1is','\1iz','\1','\1\2');
		return preg_replace($origin,$destiny,$str);
	}
		
	/**
	* Pluralize string
	* @param string $str Text to inflect
	* @return string $str Inflected text
	**/
	public static function pluralize($str){
		switch (LANGUAGE){
			case 'es' : // Spanish inflector
						$origin = array('/([rtbaeiou])([A-Z]|_|$)/','/([rlnd])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/'); 
						break;

			default   : // English inflector
						$origin = array('/([rtbaeioun])([A-Z]|_|$)/','/([rld])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/');
						break; 
		}
		$destiny = array('\1s\2','\1es\2','\1es','\1ces');
		return preg_replace($origin,$destiny,$str);
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

	public static function getControllerClass($controller){
		$controller = ucwords($controller).'Controller';
		$controller[0] = strtolower($controller[0]);
		return $controller;
	}

	public static function parseJsonFile($filename){
		$r = fopen($filename, 'r');
		$rout = "";
		while (!feof($r)){
			$line = fgets($r);
			$checkComment = strpos($line,'//');
			$line = str_replace("\t", '', $line);
			$line = str_replace("\n", '', $line);
			$line = str_replace(" :", ':', $line);
			$line = str_replace(": ", ':', $line);
			if ($checkComment === false){
				$rout.=trim($line);
			}
		}
		fclose($r);
		$rout = str_replace(",}", "}", $rout);
		$rout = json_decode($rout,true);
		return $rout;
	}
}
=======
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
	public static function getQueryString(){
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
		//get public directory structure eg "/top/second/third" 
        $public_directory = dirname($_SERVER['PHP_SELF']); 
        //place each directory into array 
        $directory_array = explode('/', $public_directory); 
        //get highest or top level in array of directory strings 
        $public_base = max($directory_array); 
        
        if ( DS.$public_base != WEBROOT ){
            return WEBROOT;
        }else{
            return DS.$public_base;
        }

	}
	
	public static function modRewriteOK(){
	    //get public directory structure eg "/top/second/third" 
        $public_directory = dirname($_SERVER['PHP_SELF']); 
        //place each directory into array 
        $directory_array = explode('/', $public_directory); 
        //get highest or top level in array of directory strings 
        $public_base = max($directory_array);
        
        if ( DS.$public_base != WEBROOT ){
            return false;
        }else{
            return true;
        }
	}

	/**
	 * Get Table name
	 * @param	string	$modelName	Model Name
	 * @return	object			Parse table name		
	 */
	public static function tableName($modelName){
		$prefix = DB_PREFIX;
		$val = Inflector::getModelPrefix($modelName).Inflector::camel_to_under(Inflector::pluralize($modelName));
		return (!empty($prefix)) ? strtoupper($val) : strtolower($val);
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
			$index = (isset($modelVars['index'])) ? $modelVars['index'] : '';
			$prefix = ( defined('DB_PREFIX') && DB_PREFIX != '') ? DB_PREFIX : '';
			return ( !empty($index) && !empty($prefix) ) ? $prefix.$index.DBS : '';
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
	 * Get route name from path
	 * @param  Array  $path Path array
	 * @return String       String with route name
	 */
	public static function getRoute($path){
		global $_routingData;
		if (is_array($path)){
			if (!empty($_routingData['routes'])){
				if (isset($path[0]) && in_array($path[0], $_routingData['routes'])){
					return $path[0];
				}
			}
		}else{
			if (!empty($_routingData['routes'])){
				foreach ($_routingData['routes'] as $eachRoute){
					$check = strpos($path, $eachRoute.'_');
					if ($check !== false)
						return $eachRoute;
				}
			}
		}
		return false;
	}

	/**
	 * Get action name from path
	 * @param  Array  $path Path array
	 * @return String       String with action name
	 */
	public static function getAction($path){
		$route = false;
		global $_routingData;
		if (is_array($path)){
			if (!empty($_routingData['routes'])){
				if (in_array($path[0], $_routingData['routes'])){
					$route = $path[0];
				}
			}
			array_shift($path);
			array_shift($path);
			$actionName = empty($path[0]) ? $_routingData['defaultAction'] : $path[0];
		}else{
			if (!empty($_routingData['routes'])){
				foreach ($_routingData['routes'] as $eachRoute){
					$path = str_replace($eachRoute.'_','', $path);
				}
			}
			return $path;
		}
		return ($route) ? $route."_".$actionName : $actionName;
	}

	/**
	 * Get controller name from path
	 * @param  Array  $path Path array
	 * @return String       String with controller name
	 */
	public static function getController($path){
		global $_routingData;
		if (!empty($_routingData['routes'])){
			if (in_array($path[0], $_routingData['routes'])){
				array_shift($path);
			}
		}
		return empty($path[0]) ? $_routingData['defaultController'] : $path[0];
	}

	/**
	 * Generate relative url from array
	 * TODO: Routings
	 * @param	array	$path	Parse array to form correct path
	 * @return	string			Url
	 */
	public static function generateUrl($path){
		if (is_array($path)){
			$newpath = array();	$x=0;

			//Route
			if ( !isset($path['route']) || ( isset($path['route']) && empty($path['route']) ) )
				$path['route'] = Inflector::getRoute($path['action']);
			
			foreach ($path as $k => $v){
				switch ((string)$k){
					case 'language' : $newpath[0] = $v; break;
					case 'route' : $newpath[1] = $v; break;
					case 'plugin': $newpath[2] = $v; break;
					case 'controller': $newpath[3] = $v; break;
					case 'action': $newpath[4] = Inflector::getAction($v); break;
					case 'params' : break;
					default: $newpath[$x+5]= $v ; break;
				}
				$x++;
			}
			ksort($newpath);
			$path = implode('/',$newpath);
		}else{
			$path = str_replace(' ','-',$path);	
		}
		return SITE_URL.Inflector::getBasePath().'/'.$path;
	}

	/**
	 * Transform Array to String Path (DEPRECATED)
	 * @param	array	$path	Parse array to form correct path
	 * @return	string		Parsed path
	 * @deprecated
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
		if (is_string($str) && !empty($str)){
			$str[0] = strtolower($str[0]);
			$func = create_function('$c', 'return "_" . strtolower($c[1]);');
			return preg_replace_callback('/([A-Z])/', $func, (string)$str);
		}else{
			return $str;
		}
	}
	
	/**
	* Transform Camelized string to relative path
	* @param string $str Text to inflect
	* @return array $aux2 Inflected array
	**/
	public static function camel_to_array($str){
		if (is_string($str) && !empty($str)){
			$str[0] = strtolower($str[0]);
			$func = create_function('$c', 'return "/" . strtolower($c[1]);');
			$aux = preg_replace_callback('/([A-Z])/', $func, $str);
			$aux2 = explode('/',$aux);
			return $aux2;
		}else{
			return $str;
		}
	}
	
	/**
	 * Transform Underscore string to Camelized
	 * @param	String	$str	String to parse
	 * @param	Boolean	$capitaliseFirst	Capitalize first character
	 * @return	String				Inflected string
	 */
	public static function under_to_camel($str = '', $capitaliseFirst = true) {
		if (!empty($str)){
			if($capitaliseFirst) {
				$str[0] = strtoupper($str[0]);
			}
			$func = create_function('$c', 'return strtoupper($c[1]);');
			return preg_replace_callback('/_([a-z])/', $func, $str);
		}else{
			return false;
		}
	}
	
	/**
	* Singularize string
	* @param string $str Text to inflect
	* @return string $str Inflected text
	**/
	public static function singularize($str){
		switch (LANGUAGE){
			case 'es' : // Spanish inflector
						$origin = array('/([rln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeiou])s([A-Z]|_|$)/');
						break;

			default   : // English inflector
						$origin = array('/([ln])es([A-Z]|_|$)/','/ises([A-Z]|_|$)/','/ices([A-Z]|_|$)/','/([d])es([A-Z]|_|$)/','/([rbtaeioun])s([A-Z]|_|$)/');
						break; 
		}
		$destiny = array('\1\2','\1is','\1iz','\1','\1\2');
		return preg_replace($origin,$destiny,$str);
	}
		
	/**
	* Pluralize string
	* @param string $str Text to inflect
	* @return string $str Inflected text
	**/
	public static function pluralize($str){
		switch (LANGUAGE){
			case 'es' : // Spanish inflector
						$origin = array('/([rtbaeiou])([A-Z]|_|$)/','/([rlnd])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/'); 
						break;

			default   : // English inflector
						$origin = array('/([rtbaeioun])([A-Z]|_|$)/','/([rld])([A-Z]|_|$)/', '/(is)([A-Z]|_|$)/','/(i)(z)([A-Z]|_|$)/');
						break; 
		}
		$destiny = array('\1s\2','\1es\2','\1es','\1ces');
		return preg_replace($origin,$destiny,$str);
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

	public static function getControllerClass($controller){
		$controller = ucwords($controller).'Controller';
		$controller[0] = strtolower($controller[0]);
		return $controller;
	}

	public static function parseJsonFile($filename){
		$r = fopen($filename, 'r');
		$rout = "";
		while (!feof($r)){
			$line = fgets($r);
			$line = trim(preg_replace('/\s\s+/', ' ', $line));
			$line = str_replace(" :", ':', $line);
			$line = str_replace(": ", ':', $line);
			$line = str_replace("} ", '}', $line);
			$line = str_replace(" }", '}', $line);
			$line = str_replace("{ ", '{', $line);
			$line = str_replace(" {", '{', $line);
			$line = preg_replace("@\s*//.*$@", "", $line);
			$rout.= $line;
		}
		fclose($r);
		$rout = str_replace(",}", "}", $rout);
		$result = json_decode($rout,true);
		return $result;
	}

}
>>>>>>> Stashed changes
