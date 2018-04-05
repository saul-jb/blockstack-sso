<?php
/*
* Template Name: Login Page
*/
?>
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
<script src="<?php echo plugin_dir_url( __FILE__ ) . 'blockstack.js'; ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
	document.getElementById("signin-button").addEventListener("click", function(event) {
		event.preventDefault();
		blockstack.redirectToSignIn();
	})

	if (blockstack.isUserSignedIn()) {
		var profile = blockstack.loadUserData().profile;
		console.log(profile);
	}
	else if (blockstack.isSignInPending()) {
		blockstack.handlePendingSignIn().then(userData => {
			window.location = window.location.origin;
		}).catch(err => {
			console.error(err);
		});
	}
});
</script>
