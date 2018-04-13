<?php
/**
* Plugin Name: Blockstack2 - Authentication Via Blockstack
* Plugin URI:
* Description: Blockstack allows the login page to be modified to allow signing in by blockstack.
* Version: 0.0.1
* Author: Saul Boyd
* Author URI: http://avikar.io
* Text Domain: blockstack
*
* @package blockstack
* @category Core
* @author Saul Boyd
*/

register_activation_hook( __FILE__, 'activated' );

function activated(){
	flush_rewrite_rules();
}

add_action("plugins_loaded", "init");

function init(){
	// hooks for directing the blockstack-login url
	add_filter("generate_rewrite_rules", "rewriteRules");
	add_filter("query_vars", "queryVars");
	add_action("template_redirect", "templateRedirect");

	// hooks for redirecting the login to our custom login
	add_action("init","goto_login_page");
	add_action("wp_login_failed", "goto_login_page");
	add_filter("authenticate", "goto_login_page", 1, 3);
	add_action("wp_logout", "goto_login_page");
}

//____________________________________________________________________________________________________________

function rewriteRules($wp_rewrite){
	$feed_rules = array(
		"blockstack-login/?$" => "index.php?bslogin=1",
		"manifest.json/?$" => "index.php?manifest=1"
	);
	$wp_rewrite->rules = $feed_rules + $wp_rewrite->rules;

	return $wp_rewrite->rules;
};


function queryVars($query_vars){
	$query_vars[] = "bslogin";
	$query_vars[] = "manifest";
	$query_vars[] = "authResponse";
	$query_vars[] = "verificationHash";
	$query_vars[] = "bsrequest";

	return $query_vars;
};


function templateRedirect(){
	$bslogin = intval(get_query_var("bslogin"));
	if($bslogin){
		include plugin_dir_path( __FILE__ ) . 'pages/login.php';
		die;
	}

	$manifest = intval( get_query_var("manifest"));
	if($manifest){
		include plugin_dir_path( __FILE__ ) . "pages/manifest.php";
		die;
	}

	$authResponse = get_query_var("authResponse");
	if($authResponse){
		include plugin_dir_path( __FILE__ ) . "pages/authPage.php";
		die;
	}

	$bsrequest = get_query_var("bsrequest");
	$verificationHash = get_query_var("verificationHash");
	if($bsrequest || $verificationHash){
		include plugin_dir_path( __FILE__ ) . "pages/auth.php";
		die;
	}
};
//________________________________________________________________________________________________________________________

function goto_login_page() {
	$login_page = home_url("/index.php?bslogin=1");
	$page = basename($_SERVER["REQUEST_URI"]);

	if($page == "wp-login.php" && $_SERVER["REQUEST_METHOD"] == "GET") {
		wp_redirect($login_page);
		exit;
	}
}
?>
