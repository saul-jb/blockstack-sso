# Blockstack-SSO
Single sign-on for PHP apps using blockstack.

## Usage

### Requirements
All client side files need the blockstack sso.min.js file included.<br />
All server side files need the blockstack sso.php included.

### Basic usage

#### Sign in page
```HTML
<input id="blockstackLogin" type="button" value="Sign In With Blockstack" />
<script src="blockstack_sso.min.js"></script>
<script>
	document.addEventListener( "DOMContentLoaded", function( event ) {
		document.getElementById( "blockstackLogin" ).addEventListener( "click", function( event ) {
			event.preventDefault();
			Blockstack_sso.login().then( ( url ) => {
				// Blockstack has created the authentication request
				// So we need to redirect to the blockstack service to complete the login

				window.location.replace( url );
			}).catch( ( err ) => {
				console.error( "Error: " + err );
			});
		});
	});
</script>
```

#### Authentication page
```HTML
<input id="blockstackLogin" type="button" value="Sign In With Blockstack" />
<script src="blockstack_sso.min.js"></script>
<script>
	document.addEventListener( "DOMContentLoaded", function( event ) {
		Blockstack_sso.isSignedIn().then( ( userData ) => {
			// successful sign in

			var url = window.location.orgin + "/authenticate";

			Blockstack_sso.phpSignIn( userData, url ).then( ( res ) => {
				window.location.replace( "http:\/\/" + window.location.hostname + "/wp-admin/" );
			}).catch( ( err ) => {
				// failed for some reason
				console.error( err.data );
			});
		}).catch( ( err ) => {
			// sign in failed.
			console.error( err.data );
		});
	});
</script>
```

#### Php authentication page
```PHP
include( "blockstack_sso.php" );

$blkstk = new Blockstack_sso();
$response = json_decode( $blkstk->auth(), true );

if ( $response["error"] ) {
	die( '{"error": true, "message": "Failed to authenticate the request."}' );
} else {
	// Login
	die( '{"error": false, "message": "Loggin in."}' );
}
```

### Login
```JS
/**
 * Create login request using blockstack.
 * @param {String} [redirectUrl=`${window.location.origin}/`]
 * The url that blockstack will redirect the authentication request to.
 * @param {String} [manifest=`${window.location.origin }/manifest.json`]
 * The location that the blockstack service will look for the manifest file.
 * @param {String} [blockstackServiceUrl = "http://browser.blockstack.org"]
 * The location it will make the auth request to.
 * @return {Promise} - The promise will resolve to the redirect url or
 * reject to the error message.
**/

Blockstack_sso.login().then( ( url ) => {
	// redirect to blockstack service
	window.location.replace( url );
}).catch( ( err ) => {
	// failed to make request
});
```

### Logout
```JS
/**
 * Log the current user out
 * @param {String} [redirectUrl=null]
 * The url that blockstack will redirect to after signing the user out.
 * @return {void}
**/

Blockstack_sso.logout();
```

### Is signed in
```JS
/**
 * Check if the user is logged into the blockstack service.
 * @return {Promise} - the promise will resolve to the user's data object or
 * reject the error message if the user is not signed in.
**/

Blockstack_sso.isSignedIn().then( ( userData ) => {
	// successful blockstack sign in
}).catch( ( err ) => {
	// sign in failed.
});
```

### Get login data
```JS
/**
 * Get the user's local sign in data
 * @return {Promise} - the promise will resolve to an object containing
 * The saved username and password or reject to the error message.
**/

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
/**
 * Set the users local sign in data.
 * @param {String} username - The username you wish to save.
 * @param {String} password - The password you wish to save.
 * @return {Promise} - the promise will resolve to the data url or
 * reject to the error message.
**/

Blockstack_sso.setLoginDetails("USERNAME", "PASSWORD").then( () => {
	// successfully set data
}).catch((err) => {
	// failed to set data
});
```

### Request php sign in
```JS
/**
 * Request a sign in from the server.
 * @param {object} userObj - The userdata to send to the server.
 * @param {String} serverUrl - The location that the server is expecting the request.
 * @return {Promise} - The promise will resolve if the server returns an object that
 * contains an "error" parameter equalling false, or reject if the error is set to true
 * or the server sends invalid json data. If the server returns any valid json
 * containing the "error" parameter is returned in both reject and resolve.
**/

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

/**
 * Initialise the blockstack class
 * @return {void}
**/

$blkstk = new Blockstack_sso();
```

### Php authenticate
```PHP
/**
 * Attempt to authenticate the data that the js lib will send it.
 * @return {String} - The string will contain a json object with two parameters:
 * "error" and "data", "error" will be true if it failed to authenticate
 * the js request, "data" will contain the error message if error is set to
 * true otherwise it will contain the userdata.
**/

$response = json_decode( $blkstk->auth(), true );

$response["error"]; // will contain a boolean if the authentication has encountered an error.

$response["data"]; // will contain the error message if there is an error or the userdata if there is no error.
$response["data"]; // may contain some extra settings (listed below) in addition to the ones blockstack uses allowing you to have more options when configuring this library.

$response["data"]["password"]; // will contain an application unique password
$response["data"]["id"]; // will cointain an application unique ID string
$response["data"]["did"]; // will cotain the user's decentralised ID

$response["data"]["login"]; // will either contain false (if the login details have NOT been set on the client) or contain the following parameters:
$response["data"]["login"]["username"]; // will be the locally set application specific username
$response["data"]["login"]["password"]; // will be the locally set application specific password
// The idea behind the addional login username and password is that it allows the user to save and automatically send it to the server giving the possiblity of linking accounts and custom username/passwords.
```

## Build
To build the "blockstack_sso.min.js" file, run:
```BASH
npm i;
npm run all;
```
