<?php
/*
* Template Name: Login Page
*/
?>

<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: "GET, POST, PUT, DELETE"');
header('Access-Control-Allow-Headers: "Content-Type"');
?>
test
<div class="wp_login_form">
<div class="form_heading"> Login Form </div>
<button id="signin-button">Sign in with blockstack</button>
<?php
$args = array(
        'redirect' => home_url(),
        'id_username' => 'user',
        'id_password' => 'pass',
);
?>

<?php wp_login_form( $args ); ?>
</div>

<!-- include the blockstack file -->
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/blockstack sso.js'; ?>"></script>
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
		document.getElementById("signin-button").addEventListener("click", function(event) {
			event.preventDefault();
			Blockstack_sso.login();
		});
	});
</script>
