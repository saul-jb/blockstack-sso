<?php
/*
	This class intergrates blockstack with php.
	Author:	Saul Boyd (avikar.io)
	License: GPL (http://www.gnu.org/copyleft/gpl.html)
*/

class Blockstack {
	public function beforeAuth(){
		// This function is to be called before the javascript authentication script
		// This function prepares and checks the data ready for the client
		// Returns true if no errors are found

		// get the jwt token and test it
		$respToken = $_GET["authResponse"];


		if(!isset($respToken)){
			return $this->respond(true, "authResponse is not set");
		}

		$token = $this->decodeToken($respToken);

		if($token == false){
			return $this->respond(true, "invalid JWT token");
		}

		$publicAddr = $this->getPubKey($token[1]);

		if(!$this->isBlockstackAddress($publicAddr)){
			return $this->respond(true, "invalid address");
		}

		// hash the token and pass all the info to the node server and test the response
		$hashedToken = hash_hmac("sha256", $respToken, "secret");
		$url = "http://" . $_SERVER['SERVER_NAME'] . ":5000/?token=" . $respToken . '&hashedToken=' . $hashedToken;
		$context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
		$file = file_get_contents($url, false, $context);
		$fileJSON = json_decode($file, true);

		if(json_last_error() != JSON_ERROR_NONE){
			return $this->respond(true, "invalid json response from node server");
		}

		if(!isset($fileJSON["verified"]) || !isset($fileJSON["encrypted"]) || !isset($fileJSON["encrypted"]["iv"])){
			return $this->respond(true, "invalid response from node server");
		}

		if(!$fileJSON["verified"]){
			return $this->respond(true, "invalid jwt signiture");
		}

		//encode the extra data and return
		return $this->respond(false, $fileJSON["encrypted"]);
	}

	public function auth(){

	}

	private function respond($isError, $data){
		return json_encode(
			array(
				"error" => $isError,
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

	private function getPubKey($payload){
		$iss = $payload["iss"];
		$parts = explode("btc-addr:", $iss);

		return $parts[1];
	}

	private function isBlockstackAddress($addr){
		// This function tests the address to check that it is associated with blockstack
		// Returns true if it is a blockstack address

		$url = "https://gaia.blockstack.org/hub/" . $addr . "/profile.json";
		$file = file_get_contents($url);
		$fileJSON = json_decode($file, true);

		if(json_last_error() != JSON_ERROR_NONE){
			// File is not json - a valid address should return json data
			return false;
		}

		if(!isset($fileJSON[0]["decodedToken"]["payload"]["claim"]["name"])){
			// Json data does not have the correct info accociated with it
			return false;
		}

		return true;
	}
}
?>
