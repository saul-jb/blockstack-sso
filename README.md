# Blockstack-SSO
Single sign-on for PHP apps using blockstack.

## Usage
All client side files need the blockstack sso.min.js file included.<br />
All server side files need the blockstack sso.php included.

### Login
```JS
Blockstack_sso.login().then( ( url ) => {
	// redirect to blockstack service
	window.location.replace( url );
}).catch( ( err ) => {
	// failed to make request
});
```

### Logout
```JS
Blockstack_sso.logout( "redirect url" );
```

### Is signed in
```JS
Blockstack_sso.isSignedIn().then( ( userData ) => {
	// successful blockstack sign in
}).catch( ( err ) => {
	// sign in failed.
});
```

### Get login data
```JS
Blockstack_sso.getLoginDetails().then( ( loginDetails ) => {
	// successfully retrieved data
	var password = loginDetails.password;
	var username = loginDetails.username;
}).catch((err) => {
	// failed to fetch or parse login details
});
```

### Set login data
```JS
Blockstack_sso.setLoginDetails("USERNAME", "PASSWORD").then( () => {
	// successfully set data
}).catch((err) => {
	// failed to set data
});
```

### Request php sign in
This function expects a json object returned from the url with a parameter "error" eg.
```JS
{"error": false}
```
It will reject the promise if error is true otherwise it will resolve the object.
```JS
Blockstack_sso.phpSignIn(userData, url).then((res) => {
	// seccessful sign-in
}).catch((err) => {
	// failed for because of err
	console.error(err.errorMessage);
});
```

### Php Blockstack_sso init
```PHP
include( "blockstack_sso.php" );
$blkstk = new Blockstack_sso();
```

### Php authenticate
```PHP
$response = $blkstk->auth();
```

$response["error"] will contain a boolean if the authentication has encountered an error.<br />
<br />
$response["data"] will contain the error message if there is an error or the userdata if there is no error.<br />
<br />
$response["data"] may contain some extra settings (listed below) in addition to the ones blockstack uses allowing you to have more options when configuring this library.<br />
<br />
$response["data"]["password"] will contain an application unique password<br />
<br />
$response["data"]["id"] will cointain an application unique ID string<br />
<br />
$response["data"]["did"] will cotain the user's decentralised ID<br />
<br />
$response["data"]["login"] will either contain false (if the login details have NOT been set on the client) or contain the following parameters:<br />
<br />
$response["data"]["login"]["username"] will be the locally set application specific username<br />
<br />
$response["data"]["login"]["password"] will be the locally set application specific password<br />
<br />
The idea behind the addional login username and password is that it allows the user to save and automatically send it to the server giving the possiblity of linking accounts and custom username/passwords.

## Build
To build the "blockstack_sso.min.js" file, run:
```BASH
npm i;
npm run all;
```
