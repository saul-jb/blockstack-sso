<?php
/**
* Plugin Name: Blockstack - Authentication Via Blockstack
* Plugin URI:
* Description: Blockstack allows the login page to be modified to allow signing in by blockstack. This plugin will run a node.js server on port 5000 to handle the authenticaiton.
* Version: 0.0.1
* Author: Saul Boyd
* Author URI: http://avikar.io
* Text Domain: blockstack
*
* @package blockstack
* @category Core
* @author Saul Boyd
*/

// hooks for activativating and deactivating the node server
register_activation_hook(__FILE__, "activate");
register_deactivation_hook(__FILE__, "deactivate");

// init the rest of the class
add_action("plugins_loaded", "init");

$page_showing = basename($_SERVER["REQUEST_URI"]);

if (strpos($page_showing, "failed") !== false) {
	echo "<p class='error-msg'><strong>ERROR:</strong> Invalid username and/or password.</p>";
}
elseif (strpos($page_showing, "blank") !== false ) {
	echo "<p class='error-msg'><strong>ERROR:</strong> Username and/or Password is empty.</p>";
}

function init() {
	// hooks for directing the blockstack-login url
	add_filter("generate_rewrite_rules", "rewriteRules");
	add_filter("query_vars", "queryVars");
	add_action("template_redirect", "templateRedirect");

	// hooks for redirecting the login to our custom login
	add_action("init", "goto_login_page");
	add_action("wp_login_failed", "login_failed");
	add_filter("authenticate", "blank_username_password", 1, 3);
	add_action("wp_logout", "logout_page");
}

function dependencyCheck(){
	$nodeInstalled = shell_exec("hash node 2>/dev/null || echo 'ERROR'");
	$npmInstalled = shell_exec("hash npm 2>/dev/null || echo 'ERROR'");
	$errorMsg = "";

	if($nodeInstalled == "ERROR"){
		// Node is not installed, abort!
		$errorMsg = "node is not installed - please install node.js\n";
	}
	if($npmInstalled == "ERROR"){
		// Npm is not installed, abort!
		$errorMsg = $errorMsg . "npm is not installed - please install the node package manager\n";
	}
	else{
		// npm is installed but is forever?
		if(shell_exec("hash forever 2>/dev/null || echo 'ERROR'")){
			// yeah... forever is NOT installed
			$errorMsg = $errorMsg . "forever is not installed - please install forever (sudo npm i -g forever)\n";
		}
	}

	return $errorMsg;
}

function activate(){
	$dependencyOutput = dependencyCheck();

	if($dependencyOutput){
		// dependencyCheck has returned some error
		wp_die(__($dependencyOutput, "blockstack"));
	}
	else{
		// dependencies are installed run instalation
		$logData = shell_exec("cd backend && npm i && cd ..");
		// installed - run the node server!
		$logData = $logData . shell_exec("forever start backend/server.js");
		// log info
		logFile($logData);
	}
}

function deactivate(){
	// someone deactivate the plugin... I suppose we should stop the node server...
	$logData = shell_exec("forever stop 0");
	logFile($logData);
}

function rewriteRules($wp_rewrite){
	$feed_rules = array("blockstack-login/?$" => "index.php?login=1");
	$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;

	return $wp_rewrite->rules;
}

function queryVars($query_vars){
	$query_vars[] = "login";
	return $query_vars;
}

function templateRedirect(){
	$login = intval(get_query_var("login"));
	if($login){
		include plugin_dir_path( __FILE__ ) . "login.php";
		die;
	}
}

function goto_login_page() {
	$login_page = home_url("/blockstack-login");
	$page = basename($_SERVER["REQUEST_URI"]);

	if($page == "wp-login.php" && $_SERVER["REQUEST_METHOD"] == "GET"){
		wp_redirect($login_page);
		exit;
	}
}

function login_failed() {
	$login_page = home_url("/index.php?login=1");
	wp_redirect( $login_page . '&login=failed' );
	exit;
}

function blank_username_password($user, $username, $password){
	$login_page = home_url("/index.php?login=1");
	if($username == "" || $password == ""){
		wp_redirect($login_page . "&login=blank");
		exit;
	}
}

function logout_page() {
	$login_page = home_url("/index.php?custom=1");
	wp_redirect($login_page . "&login=false");
	exit;
}

function logFile($data){
	file_put_contents(plugin_dir_path( __FILE__ ) . "log", $data . '\n', FILE_APPEND);
}
?>
