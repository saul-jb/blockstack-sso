<?php
/*
* Template Name: Authentication page
*/
?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack.php");
$blkstk = new Blockstack();
?>

LOGGING IN!
<button id='signout-button'>SIGN OUT</button>
<!-- include the blockstack file -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/blockstack.min.js'; ?>"></script>
<script>
	document.getElementById('signout-button').addEventListener('click', event => {
		event.preventDefault();
		blockstack.signUserOut(window.location.href);
	});


	function authenticated(userData){
		// here blockstack has authenticated our login
		var data = <?php echo $blkstk->beforeAuth(); ?>;
		var transitKey = authApp.getTransitKey();
		var verificationHash = encryption.decryptECIES(transitKey, data.data);
	}

	if (blockstack.isUserSignedIn()) {
		var userData = blockstack.loadUserData();
		authenticated(userData);
	}
	else if (blockstack.isSignInPending()) {
		blockstack.handlePendingSignIn().then((userData) => {
			authenticated(userData);
		}).catch(err => {
			console.error(err);
		});
	}
</script>
