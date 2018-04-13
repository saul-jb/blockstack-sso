# Blockstack-SSO
Single sign-on for PHP apps using blockstack.

## Usage
All client side files need the blockstack sso.min.js file included.<br />
All server side files need the blockstack sso.php included.

### Login
```JS
Blockstack_sso.login().then((url) => {
	// redirect to blockstack service
	window.location.replace(url);
}).catch((err) => {
	// failed to make request
});
```

### logout
```JS
Blockstack_sso.logout("redirect url");
```

### is signed in
```JS
Blockstack_sso.isSignedIn().then((userData) => {
	// successful blockstack sign in
}).catch((err) => {
	// sign in failed.
});
```

### request php sign in
```JS
var data = <?php echo $_SESSION["encryptedToken"]; ?>;
var hash = Blockstack_sso.decryptHash(data);
var name = userData.profile.name;
var key = userData.appPrivateKey;

Blockstack_sso.phpSignIn(hash, name, key).then((res) => {
	// seccessful sign-in
}).catch((err) => {
	// failed for some reason or another
});
```

### php Blockstack_sso init
```PHP
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");
$blkstk = new Blockstack_sso();
```

### php create authentication request
```PHP
$blkstk->createAuthReq();
```

### php authenticate
```PHP
$blkstk->auth();
```
