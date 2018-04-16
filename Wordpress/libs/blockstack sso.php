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

		$user = file_get_contents('php://input');

		if(!isset($user) || $user === ""){
			return $this->respond(true, "invalid post parameters");
		}

		$userData = json_decode(stripslashes($user), true);

		if(json_last_error() != JSON_ERROR_NONE){
			return $this->respond(true, "invalid json");
		}

		if(strlen($userData["appPrivateKey"]) != 64){
			return $this->respond(true, "invalid key");
		}

		if(!isset($userData["profile"]["image"][0]["contentUrl"])){
			$userData["avatarUrl"] = "https://s3.amazonaws.com/onename/avatar-placeholder.png";
		}
		else{
			$userData["avatarUrl"] = $userData["profile"]["image"][0]["contentUrl"];
		}

		$userData["password"] =  hash_hmac("sha256", $userData["appPrivateKey"], $userData["appPrivateKey"]);

		return $this->respond(false, $userData);
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
