<?php
class Translate {

    private $language	= 'es';
	private $lang 		= array();
	
	public function __construct(){
        if($_SESSION['lang']){
            $this->language = $_SESSION['lang'];
        } else {
            $this->language = '';
        }
		
	}
	
    private function findString($str) {
        if (array_key_exists($str, $this->lang[$this->language])) {
			return $this->lang[$this->language][$str];
        }
        return $str;
    }
    
	private function splitStrings($str) {
        return explode('=',trim($str));
    }
	
	public function __($str) {	
        if (file_exists('../public/locale/'.$this->language.'.txt')) {
            $strings = array_map(array($this,'splitStrings'),file('../public/locale/'.$this->language.'.txt'));
            foreach ($strings as $k => $v) {
				$this->lang[$this->language][$v[0]] = $v[1];
            }
            return $this->findString($str);
        }
        else {
            return $str;
        }
    }
}
?>