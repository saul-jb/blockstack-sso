<?php
/*
* Template Name: Authentication page
*/
?>

<?php
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");
$blkstk = new Blockstack_sso();
?>
<div class="loginText">
	Logging in!
</div>

<style>
	.loginText{
		text-align: center;
		position: relative;
		top: 200px;
	}
</style>
<!-- include the blockstack file -->
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/blockstack sso.js'; ?>"></script>
<script>
	Blockstack_sso.isSignedIn().then((userData) => {
		// successful sign in
		var url = "<?php echo plugin_dir_url( __FILE__ ) . 'auth.php?' ?>"
		var data = <?php echo $_SESSION["encryptedToken"]; ?>;
		var hash = Blockstack_sso.decryptHash(data);
		var name = userData.profile.name;
		var key = userData.appPrivateKey;

		Blockstack_sso.phpSignIn(hash, name, key, url).then((res) => {
			// seccessful sign-in
			window.location.replace("http:\/\/" + window.location.hostname + "/wp-admin/");
		}).catch((err) => {
			// failed for some reason or another
			window.location.replace("http:\/\/" + window.location.hostname + "/blockstack-login/");
		});
	}).catch((err) => {
		// sign in failed.
		window.location.replace("http:\/\/" + window.location.hostname + "/blockstack-login/");
	});
</script>
