<?php
/**
 * Supernova Framework
 */
/**
 * Security handler
 * 
 * @package Security
 */

class Security {

	/**
	 * Clean all GPC
	 * @param array &$target 
	 * @param string $etiquetas 
	 * @param int $limit 
	 * @return array
	 * @ignore
	 */
 	public static function _limpia_gpc(&$target, $etiquetas, $limit= 3) {
 		if ($target){
	        foreach ($target as $key => $value) {
	            if (is_array($value) && $limit > 0) {
	                Security::_limpia_gpc($value, $etiquetas, $limit - 1);
	            } else {
	                $target[$key] = preg_replace($etiquetas, "", $value);
	            }
	        }
	        return $target;
    	}
    }

	/**
	 * Clean vars
	 * @ignore
	 */
	public static function _cleanAllVars(){
		if (isset($_SERVER['QUERY_STRING']) && strpos(urldecode($_SERVER['QUERY_STRING']), chr(0)) !== false)
		    die();

		if (@ ini_get('register_globals')) {
		    foreach ($_REQUEST as $key => $value) {
		        $$key = null; 
		        unset ($$key);
		    }
		}
		$etiquetas = array (
		    '@<script[^>]*?>.*?</script>@si',
		    '@&#(\d+);@e',
		    '@\[\[(.*?)\]\]@si',
		    '@\[!(.*?)!\]@si',
		    '@\[\~(.*?)\~\]@si',
		    '@\[\((.*?)\)\]@si',
		    '@{{(.*?)}}@si',
		    '@\[\+(.*?)\+\]@si',
		    '@\[\*(.*?)\*\]@si'
		);
		Security::_limpia_gpc($_GET, $etiquetas);
		Security::_limpia_gpc($_POST, $etiquetas);
		Security::_limpia_gpc($_COOKIE, $etiquetas);
		Security::_limpia_gpc($_REQUEST, $etiquetas);

		foreach (array ('PHP_SELF', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'QUERY_STRING') as $key) {
		    $_SERVER[$key] = isset ($_SERVER[$key]) ? htmlspecialchars($_SERVER[$key], ENT_QUOTES) : null;
		}
		unset ($etiquetas, $key, $value);
	}

	/**
	 * Check for Magic Quotes and remove them
	 *
	 * Uses {link removeMagicQuotes()} to do the magic
	 * 
	 * @ignore
	 */
	public static function _stripSlashesDeep($value) {
		if (!empty($value)){
			$value = is_array($value) ? array_map('Security::_stripSlashesDeep', $value) : stripslashes($value);
		}
		return $value;
	}
	
	/**
	 * Remove magic quotes
	 * @ignore
	 */
	public static function _removeMagicQuotes() {
		if ( get_magic_quotes_gpc() ) {
			$_GET    = Security::_stripSlashesDeep($_GET   );
			$_POST   = Security::_stripSlashesDeep($_POST  );
			$_COOKIE = Security::_stripSlashesDeep($_COOKIE);
		}
	}
	
	/**
	 * Check register globals and remove them
	 * @ignore
	 */
	public static function _unregisterGlobals() {
		if (ini_get('register_globals')){
			$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
			foreach ($array as $value) {
				foreach ($GLOBALS[$value] as $key => $var) {
					if ($var === $GLOBALS[$key]) {
						unset($GLOBALS[$key]);
					}
				}
			}
		}
	}

	/**
	 * Sanitize data
	 *
	 * Returns sanitized data
	 * 
	 * @param	string	$data	Unsanitized string
	 * @return	string		Sanitized string
	 */
	public static function sanitize($data){
		if (!is_array($data)){
			$data = trim($data);
			if(get_magic_quotes_gpc()){
				$data = stripslashes($data);
			}
			// Deprecated : PDO scapes automaticly
			// $data = mysql_real_escape_string($data);
		}else{
			foreach ($data as $key => $dat){
				$data[$key] = Security::sanitize($dat);
			}
		}
		return $data;
	}

}