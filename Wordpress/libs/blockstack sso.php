<?php
/*
	This class intergrates blockstack with php.
	Author: Saul Boyd (avikar.io)
	License: GPL (http://www.gnu.org/copyleft/gpl.html)
*/

class Blockstack_sso {
	private $secret = "secret";

	public function __construct(){
		session_start();
	}

	public function auth(){
		// this function is to be called to verify and obtain the blockstack data

		$name = $_GET["name"];
		$key = $_GET["key"];

		if(!isset($key) || !isset($name)){
			return $this->respond(true, "invalid get parameters");
		}

		if(strlen($name) >= 30){
			return $this->respond(true, "name too long");
		}

		if(strlen($key) != 64){
			return $this->respond(true, "invalid key");
		}

		$password =  hash_hmac("sha256", $key, $key);
		$id =  substr(hash_hmac("sha256", $key, $this->secret), 0, 29);

		$profile = 	array(
			"name" => $name,
			"id" => $id,
			"password" => $password
		);

		return $this->respond(false, $profile);
	}

	private function respond($error, $data){
		return json_encode(
			array(
				"error" => $error,
				"data" => $data
			)
		);
	}

	private function decodeToken($token){
		// Decodes the token and returns it in an array
		$authParts = explode('.', $token);

		if(count($authParts) != 3){
			return false;
		}

		$authParts[0] = json_decode(base64_decode($authParts[0]), true);
		$authParts[1] = json_decode(base64_decode($authParts[1]), true);

		if(json_last_error() != JSON_ERROR_NONE){
			return false;
		}

		return $authParts;
	}
}
?>
