<?php
/**
 * Supernova Framework
 */
/**
 * Authentication handler
 * 
 * @package MVC_Controller_Auth
 */
class Auth {
	/**
	* Login user data into a Session with the name AuthUser
	* 
	* @param mixed $data User data
	* @return boolean Return success
	*/
	function login($data){
		$Session = new Session();
		$Session->create('AuthUser',$data);
		return true;
	}

	/**
	* Logout user data from a Session with the name AuthUser
	* 
	* @return boolean Return success
	*/
	function logout(){
		$Session = new Session();
		$Session->destroy('AuthUser');
		return true;
	}

	/**
	* Compare some fields in the database with the data sended
	* 
	* @param string $model Model name
	* @param string $field Field name
	* @param mixed $data Data for comparation
	* @return boolean Return true if comparation success
	*/
	function checkField($model,$field,$data){
		$userModel = new $model();
		$userData = $this->userModel->findBy($field,$data);
		if (!empty($userData)){
			return true;
		}else{
			return false;
		}
	}

	/**
	* Get user data from Session with name AuthUser
	* 
	* @return boolean Return data, else return false (not logged)
	*/
	function user(){
		$Session = new Session();
		$AuthUser = $Session->read('AuthUser');
		if (!empty($AuthUser)){
			return $AuthUser;
		}else{
			return false;
		}
	}

	/**
	* Check for user type from Session with name AuthUser
	* 
	* @param string $field Field name
	* @param string $type Type id or name
	* @return boolean 
	*/
	function isTypeLogged($field,$type){
		$Session = new Session();
		$AuthUser = $Session -> read ('AuthUser');
		if (!empty($AuthUser)){
			if ($AuthUser[$field]==$type){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	* Password Hashing
	* 
	* @param string $password Password string
	* @return mixed $hash Hassed password or false on errors 
	*/
	function passwordHash($password){
		$Bcrypt = new Bcrypt();
		$hash = $Bcrypt->hash($password);
		if ($Bcrypt->verify($password, $hash)){
			return $hash;
		}else{
			return false;
		}
	}

	/**
	 * Password Verification
	 *
	 * @param string $password Current Hashed Password
	 * @param string $password2 Verification password
	 * @return boolean Return true if got correct verification
	 */
	function passwordVerify($password,$password2){
		$Bcrypt = new Bcrypt();
		$hash = $Bcrypt->hash($password);
		if ($Bcrypt->verify($password2, $hash)){
			return true;
		}else{
			return false;
		}
	}

}