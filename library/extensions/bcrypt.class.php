<?php
/**
 * Supernova Framework
 */
/**
 * Encryption handler
 * 
 * @package MVC_Controller_Auth_Bcrypt
 */

class Bcrypt {
	/**
	 * Rounds
	 * @var Integer
	 */
	private $rounds;

	/**
	 * Construct for password hashing
	 * @ignore
	 */
	public function __construct($rounds = 12) {
		if(CRYPT_BLOWFISH != 1  && ENCRYPTION_TYPE =='bcrypt') {
			warning("bcrypt is not supported in your server. See http://php.net/crypt");
		}
		$this->rounds = $rounds;
	}

	/**
	 * Hash string
	 * 
	 * @param string $input String to hash
	 * @return string $hash Hashed string of false in errors
	 */
	public function hash($input) {
		if (CRYPT_BLOWFISH == 1 && ENCRYPTION_TYPE =='bcrypt'){
			$hash = crypt($input, $this->getSalt());
			if(strlen($hash) > 13) return $hash;
			return false;
		}else{
			$hash = base64_encode(sha1($input.RANDOM_SEED, true).RANDOM_SEED); 
	        return $hash;
		}
	}

	/**
	 * Verify string
	 *
	 * @param string $input String to verify
	 * @param string $existingHash Already existing hash
	 * @return boolean
	 */
	public function verify($input, $existingHash) {
		if (CRYPT_BLOWFISH == 1 && ENCRYPTION_TYPE =='bcrypt'){
			$hash = crypt($input, $existingHash);
			return $hash === $existingHash;	
		}else{
			$hash = base64_encode(sha1($input.RANDOM_SEED, true).RANDOM_SEED);
			return $hash === $existingHash;
		}
	}

	/**
	 * Get security random salt
	 * @ignore
	 */
	private function getSalt() {
		$salt = sprintf('$2a$%02d$', $this->rounds);
		$bytes = $this->getRandomBytes(16);
		$salt .= $this->encodeBytes($bytes);
		return $salt;
	}

	/**
	 * Random state
	 * @ignore
	 */
	private $randomState;

	/**
	 * Random bytes
	 * @ignore
	 */
	private function getRandomBytes($count) {
		$bytes = '';
		if(function_exists('openssl_random_pseudo_bytes') && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) { // OpenSSL slow on Win
			$bytes = openssl_random_pseudo_bytes($count);
		}

		if($bytes === '' && is_readable('/dev/urandom') && ($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
			$bytes = fread($hRand, $count);
			fclose($hRand);
		}

		if(strlen($bytes) < $count) {
	  		$bytes = '';
			if($this->randomState === null) {
	    		$this->randomState = microtime();
	    		if(function_exists('getmypid')) {
	      			$this->randomState .= getmypid();
	    		}
	  		}

	  		for($i = 0; $i < $count; $i += 16) {
		    	$this->randomState = md5(microtime() . $this->randomState);
				if (PHP_VERSION >= '5') {
		      		$bytes .= md5($this->randomState, true);
		    	} else {
		      		$bytes .= pack('H*', md5($this->randomState));
		    	}
	  		}

	  		$bytes = substr($bytes, 0, $count);
		}
		return $bytes;
	}

	/**
	 * Encode bytes
	 * @ignore
	 */
	private function encodeBytes($input) {
		// The following is code from the PHP Password Hashing Framework
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'.RANDOM_SEED;
			
		$output = '';
		$i = 0;
		do {
			$c1 = ord($input[$i++]);
			$output .= $itoa64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16) {
				$output .= $itoa64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= $itoa64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= $itoa64[$c1];
			$output .= $itoa64[$c2 & 0x3f];
		} while (1);
		return $output;
	}
}