<?php
require_once("../../../../wp-load.php");
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");
$blkstk = new Blockstack_sso();
$response = json_decode($blkstk->auth(), true);

if($response["error"]){
	//error handle
	echo '{"error": true, "data": ' . $response["data"] . '}';
}
else{
	//login!
	$userName = $response["data"]["name"] .  $response["data"]["id"];
	$userId = username_exists($userName);

	if(!$userId){
		echo '{"error": false, "data": "Creating user"}';

		$userId = wp_create_user($userName, $response["data"]["password"]);
		update_user_meta($userId, 'first_name', $response["data"]["name"]);
		update_user_meta($userId, 'nickname', $response["data"]["name"]);
		update_user_meta($userId, 'display_name', $response["data"]["name"]);
	}
	else{
		echo '{"error": false, "data": "Logging in"}';

		$creds = array(
			'user_login' => $userName,
			'user_password' => $response["data"]["password"],
			'remember' => true
		);

		$user = wp_signon( $creds, false );
	}
}
?>
