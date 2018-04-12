<?php
/*
* Template Name: Authentication page
*/
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");
$blkstk = new Blockstack_sso();
?>
<?php var_dump( $_SESSION["encryptedToken"]); ?>
<?php var_dump( $_SESSION["oldReq"]); ?>
LOGGING IN!
<button id='signout-button'>SIGN OUT</button>
<!-- include the blockstack file -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/blockstack sso.js'; ?>"></script>
<script>
	document.getElementById('signout-button').addEventListener('click', event => {
		event.preventDefault();
		Blockstack_sso.logout();
	});


	function authenticated(userData){
		// here blockstack has authenticated our login
		var data = <?php echo $_SESSION["encryptedToken"]; ?>;
		var url = "<?php echo plugin_dir_url( __FILE__ ) . 'auth.php'; ?>";
		var hash = Blockstack_sso.decryptHash(data);
		var name = userData.profile.name;
		var key = userData.appPrivateKey;

		Blockstack_sso.phpSignIn(hash, name, key, url);
	}

	function unauthenticated(err = false){
		console.log("Sign in failed.")

		if(err){
			console.error(err);
		}
	}

	Blockstack_sso.isSignedIn(authenticated, unauthenticated);
</script>
