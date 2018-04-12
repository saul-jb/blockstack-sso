<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");
$blkstk = new Blockstack_sso();

$req = json_decode($blkstk->createAuthReq(), true);
var_dump($req);


// "localhost:8888/auth?authRequest=" . $bsrequest
//header("http://localhost:8888/auth?authRequest=" . $req);

if(!$req["error"]){
	echo "<script>window.location.replace('http://localhost:8888/auth?authRequest=" . $req["data"] . "');</script>";
}
else{
	var_dump($req["data"]);
}

?>
