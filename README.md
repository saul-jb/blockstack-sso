# Blockstack-SSO
Single sign-on for PHP apps using blockstack.

## Usage
All client side files need the blockstack sso.min.js file included.<br />
All server side files need the blockstack sso.php included.

### Login
Blockstack_sso.login().then((url) => {<br />
	// redirect to blockstack service<br />
	window.location.replace(url);<br />
}).catch((err) => {<br />
	// failed to make request<br />
});

### logout
Blockstack_sso.logout("redirect url");

### is signed in
Blockstack_sso.isSignedIn().then((userData) => {<br />
	// successful blockstack sign in<br />
}).catch((err) => {<br />
	// sign in failed.<br />
});

### request php sign in
var data = <?php echo $_SESSION["encryptedToken"]; ?>;<br />
var hash = Blockstack_sso.decryptHash(data);<br />
var name = userData.profile.name;<br />
var key = userData.appPrivateKey;<br />
<br />
Blockstack_sso.phpSignIn(hash, name, key).then((res) => {<br />
	// seccessful sign-in<br />
}).catch((err) => {<br />
	// failed for some reason or another<br />
});<br />
<br />
### php Blockstack_sso init<br />
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");<br />
$blkstk = new Blockstack_sso();

### php create authentication request
$blkstk->createAuthReq();

### php authenticate
$blkstk->auth();
