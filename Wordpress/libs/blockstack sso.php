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

	public function createAuthReq(){
		// this function is to be called on the page that the login redirects to for authentication

		$bsrequest = $_GET["bsrequest"];

		if(!isset($bsrequest)){
			return $this->respond(true, "bsrequest is not set");
		}

		$hashedToken = hash_hmac("sha256", $bsrequest, $this->secret);
		$url = "http://" . $_SERVER['SERVER_NAME'] . ":5000/?token=" . $bsrequest . '&hashedToken=' . $hashedToken;
		$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
		$file = file_get_contents($url, false, $context);
		$fileJSON = json_decode($file, true);

		if(json_last_error() != JSON_ERROR_NONE){
			return $this->respond(true, "invalid json response from node server");
		}

		if(!isset($fileJSON["verified"]) || !isset($fileJSON["encrypted"])){
			return $this->respond(true, "invalid response from node server");
		}

		if(!$fileJSON["verified"]){
			return $this->respond(true, "invalid jwt signiture");
		}

		$_SESSION["encryptedToken"] = json_encode($fileJSON["encrypted"]);
		$_SESSION["oldReq"] = $bsrequest;

		return $this->respond(false, $bsrequest);
	}

	public function auth(){
		$name = $_GET["name"];
		echo $name;
		$id = $_GET["id"];
		echo $id;
		$hash = $_GET["verificationHash"];
		echo $hash;
		$token = $_SESSION["oldReq"];
		echo $token;

		if(!isset($token) || !isset($id) || !isset($hash) || !isset($token)){
			return $this->respond(true, "invalid post parameters");
		}

		$password =  hash_hmac("sha256", $name . $id, $this->secret);
		$hashedToken = hash_hmac("sha256", $token, $this->secret);

		if($hash != $hashedToken){
			return $this->respond(true, "hash doesn't match token");
		}

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
