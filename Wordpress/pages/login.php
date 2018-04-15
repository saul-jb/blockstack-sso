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
<div class="wp_login_form">
<?php
$args = array(
    'redirect' => admin_url(),
    'id_username' => 'user',
    'id_password' => 'pass',
);

wp_login_form( $args );
?>

<div id="signin-button">Sign in with blockstack</div>
</div>

<style>
	.wp_login_form{
		border: 2px solid black;
		border-radius: 20px;
		/*display: flex;
		flex-grow: 1;*/
		left: 35%;
		position: relative;
		width: 30%;
		padding: 50px;
		box-sizing: border-box;
		top: 200px;
	}

	form > p{
		width: 100%;
	}

	label[for="user"], label[for="pass"]{
		font-weight: bold;
	}

	form > p > input{
		width: 100%;
	}

	form{
		padding: 20px 50px;
		border: 1px solid black;
		border-radius: 10px;
	}

	#signin-button{
		padding: 15px;
		border: 1px solid black;
		border-radius: 10px;
		text-align: center;
		cursor: pointer;
	}
</style>

<!-- include the blockstack file -->
<script src="<?php echo plugin_dir_url( __FILE__ ) . '../js/blockstack sso.js'; ?>"></script>
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
		document.getElementById("signin-button").addEventListener("click", function(event) {
			event.preventDefault();
			Blockstack_sso.login().then((url) => {
				window.location.replace(url);
			}).catch((err) => {
				console.error("Error: " + err);
			});
		});
	});
</script>
