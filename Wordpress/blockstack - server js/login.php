<?php
/*
* Template Name: Login Page
*/
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
<script src="<?php echo get_site_url() . ':5000'; ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
	document.getElementById("signin-button").addEventListener("click", function(event) {
		event.preventDefault()
		blockstack.redirectToSignIn()
	})
});
</script>
