# Blockstack-SSO
Single sign-on for PHP apps using blockstack.<br />
<br />
This library is to help provide a method for Single sign-on usingblockstack for PHP apps like wordpress, media wiki, jooml etc.

## Usage

### Requirements
* All client side files need the "blockstack_sso.min.js" file included.
* All server side authentication pages need the "blockstack_sso.php" included.
* The manifest file must have the "Access-Control-Allow-Origin: * " set.
* SSL must be enabled in order to use the blockstack browser.

### Basic usage

#### Manifest page
We have to have a manifest page for the blockstack authentication service to access<br />
otherwise the service will not work, the manifest must have the server's url and the<br />
"Access-Control-Allow-Origin: * " header set or it will not work on the blockstack<br />
authentication service. The defualt manifest location is at: "your.domain/manifest.json".<br />
<br />
```JS
{
	"name": "Blockstack Log-in",
	"start_url": "your.domain",
	"description": "The blockstack plugin to log into yourPhpApp with blockstack",
	"icons": [
		{
			"src": "https://blockstack.org/images/logos/blockstack-bug.svg",
			"sizes": "192x192",
			"type": "image/svg"
		}
	]
}
```

#### Sign in page
First we are required to include the "blockstack_sso.min.js" script to the page.<br />
We can then create a button on the page for the users to click when they want to sign<br />
in. We should add the event listener: "DOMContentLoaded" to the document to ensure that<br />
the "blockstack_sso.min.js" is loaded before attempting to use it. We can then add a<br />
"click" event listner on the button and call "Blockstack_sso.login()" to create the<br />
authentication url, then we can redirect the page to this url so blockstack can get<br />
the users permission to share their data with the app.<br />
<br />
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
Again we have to include the "blockstack_sso.min.js" script to the page, and<br />
again we will wait for it to have loaded using the "DOMContentLoaded" listener.<br />
Then we will call the Blockstack_sso.isSignedIn() method which will check that<br />
the user is logged in and return the user data. We can then call the<br />
"Blockstack_sso.phpSignIn()" method with the user's data as the first paarameter<br />
and a url to POST the data to as the second.<br />
<br />
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

#### PHP authentication page
There should be a PHP page at the location we sent the post data to waiting to<br />
receive the data. We first have to include the "blockstack_sso.php" library and<br />
initialise is by calling "$blkstk = new Blockstack_sso();" then we can obtain<br />
and validate the POST data by calling "$blkstk->auth()" which will return a json<br />
object containg the user data or the error message. Then we should check if the<br />
response has the parameter "error" set to true and exit if it is due to the data<br />
being invalid, or otherwise create a user on the php app with the returned data.<br />
<br />
```PHP
include( "blockstack_sso.php" );

$blkstk = new Blockstack_sso();
$response = json_decode( $blkstk->auth(), true );

if ( $response["error"] ) {
	die( '{"error": true, "message": "' . $response["data"] . '"}' );
} else {
	// Login
	die( '{"error": false, "message": "Loggin in."}' );
}
```

### Login
The login proccess starts by calling logout to prevent user issues further down,<br />
then it uses the blockstack method "makeAuthRequest" to create the JWT <br />
authentication token using the functions parameters if they are set and the <br />
permissions of "store_write" and "publish_data", then it creates a url to the<br />
blockstack service and returns it so that you can redirect the user toward the<br />
blockstack service to give their permission for the app.<br />
<br />
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
The logout proccess simply calls the blockstack "logout" function with the option<br />
to redirect the user if the redirectUrl parameter is set.<br />
<br />
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
The isSignedIn proccess involves calling the blockstack "isUserSignedIn" and resolves<br />
with the blockstack "loadUserData" method if the user is already signed in, then it<br />
calls blockstacks "isSignInPending" method to see if the user is being signed in and<br />
resolves with the userdata once that is completed otherwise it rejects due to the user<br />
not being signed in.<br />
<br />
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
The getLoginDetails process calls the blockstack "getFile" method on "login.json"<br />
whcih should retrieve a json string containing the login data or nothing if it<br />
hasn't been set.<br />
<br />
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
The setLoginDetails process creates an object from the two parameters and<br />
stringifys the object then calls the blockstack "putFile" method on "login.json"<br />
and the string.<br />
<br />
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
The phpSignIn proccess checks for the "authResponse" parmeter in the url and<br />
decodes it into a JWT token using jsontokens "decodeToken" method on it, then<br />
it adds a "did" parameter to the userData parameter using the users public<br />
address. After adding the "did" parameter it calls "getLoginDetails" and adds<br />
the details to the userObj under "login" in the object, then it calls getData<br />
whcih sends a POST request to the server sending the userObj with it.<br />
<br />
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
The auth function retrieves the json data that was sent in the POST request made<br />
by the phpSignIn function and checks that folowing parmeters exist: "appPrivateKey"<br />
and "did" and returns a json object containing error infomation if they don't exist.<br />
It then checks if the user profile name is specified and if it isn't it attempts to<br />
obtain the data from the user's hub url. It then checks if the user has an avartar<br />
url specified and adds a parameter to the json: "avatarUrl" which contains the avatar<br />
url if it is set otherwise it sets a default one to it. It will then check if the<br />
user's name is set and if it isn't it sets it to "Anonymous", it will also create<br />
an empty description if it doesn't already exist. It will then create a "password"<br />
parameter in the json object with is created by hashing the appPrivateKey with "sha256"<br />
using itself as the secret, it will then add an id parmeter to the json object<br />
which contains a "sha256" hash of the appPrivateKey with no secret and retrieves the<br />
first 30 characters of the string. then it returns a json object containing the data.<br />
<br />
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
