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
Blockstack_sso.phpSignIn(userData).then((res) => {
	// seccessful sign-in
}).catch((err) => {
	// failed for because of err
});
```

### php Blockstack_sso init
```PHP
include( plugin_dir_path( __FILE__ ) . "../libs/blockstack sso.php");
$blkstk = new Blockstack_sso();
```

### php authenticate
```PHP
$blkstk->auth();
```

## Build
To build the blockstack sso.min.js run:
```BASH
npm i;
npm run all;
```
