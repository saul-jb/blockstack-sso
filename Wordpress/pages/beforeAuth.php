<?php
/*
* Template Name: Authentication page
*/
?>

<?php
include 'libs/blockstack.php';
$qwerty = new Blockstack();
echo "TEST";
?>

LOGGING IN!
<button id='signout-button'>SIGN OUT</button>
<!-- include the blockstack file -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/blockstack.min.js'; ?>"></script>
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/encryption.js'; ?>"></script>
<script>
	document.getElementById('signout-button').addEventListener('click', event => {
		event.preventDefault();
		blockstack.signUserOut(window.location.href);
	});

	function authenticated(userData){
		//here blockstack has authenticated our login

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

	console.log((<?php $qwerty.beforeAuth() ?>);
</script>
