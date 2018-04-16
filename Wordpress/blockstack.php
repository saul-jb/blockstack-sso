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
	add_action("init","preventPassowrdChange");
	add_action("init","goto_login_page");
	add_action("wp_login_failed", "goto_login_page");
	add_filter("authenticate", "goto_login_page", 1, 3);
	add_action("wp_logout", "goto_login_page");
}

//____________________________________________________________________________________________________________

function preventPassowrdChange(){
	$user = wp_get_current_user();

	if($user->exists() && get_user_meta($user->ID, "blockstack_user", true)){
		add_filter('show_password_fields',create_function('$nopass_profile','return false;'));
		add_filter('allow_password_reset',create_function('$nopass_login','return false;'));
	}
}

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

//__________________________________________________________________________________________________________________________

if( ! function_exists("get_avatar") ) {
	function get_avatar($id, $size = 96, $default = '', $alt = '', $args = null){
		$bsUrl = get_user_meta($id, "avatar_url", true);


		$defaults = array(
			// get_avatar_data() args.
			'size'          => 96,
			'height'        => null,
			'width'         => null,
			'default'       => get_option( 'avatar_default', 'mystery' ),
			'force_default' => false,
			'rating'        => get_option( 'avatar_rating' ),
			'scheme'        => null,
			'alt'           => '',
			'class'         => null,
			'force_display' => false,
			'extra_attr'    => '',
		);

		if ( empty( $args ) ) {
			$args = array();
		}

		$args['size']    = (int) $size;
		$args['default'] = $default;
		$args['alt']     = $alt;

		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['height'] ) ) {
			$args['height'] = $args['size'];
		}
		if ( empty( $args['width'] ) ) {
			$args['width'] = $args['size'];
		}

		if ( is_object( $id_or_email ) && isset( $id_or_email->comment_ID ) ) {
			$id_or_email = get_comment( $id_or_email );
		}

		$avatar = apply_filters( 'pre_get_avatar', null, $id_or_email, $args );

		if ( ! is_null( $avatar ) ) {
			/** This filter is documented in wp-includes/pluggable.php */
			return apply_filters( 'get_avatar', $avatar, $id_or_email, $args['size'], $args['default'], $args['alt'], $args );
		}

		if ( ! $args['force_display'] && ! get_option( 'show_avatars' ) ) {
			return false;
		}

		$url2x = get_avatar_url( $id_or_email, array_merge( $args, array( 'size' => $args['size'] * 2 ) ) );

		$args = get_avatar_data( $id_or_email, $args );

		$url = $args['url'];

		if ( ! $url || is_wp_error( $url ) ) {
			return false;
		}

		$class = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );

		if ( ! $args['found_avatar'] || $args['force_default'] ) {
			$class[] = 'avatar-default';
		}

		if ( $args['class'] ) {
			if ( is_array( $args['class'] ) ) {
				$class = array_merge( $class, $args['class'] );
			} else {
				$class[] = $args['class'];
			}
		}

		if($bsUrl){
			$url = $bsUrl;
			$url2x = $bsUrl;
		}

		$avatar = sprintf(
			"<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d' %s/>",
			esc_attr( $args['alt'] ),
			esc_url( $url ),
			esc_url( $url2x ) . ' 2x',
			esc_attr( join( ' ', $class ) ),
			(int) $args['height'],
			(int) $args['width'],
			$args['extra_attr']
		);

		return apply_filters( 'get_avatar', $avatar, $id_or_email, $args['size'], $args['default'], $args['alt'], $args );
	}
}
?>
