var blockstack = require('blockstack');
var jsontokens = require('jsontokens');

var BlockstackCommon = (() => {
	var login, logout, isSignedIn, phpSignIn, getData, setLoginDetails, getLoginDetails, dappLoaded;

	login = (redirectUrl = false, manifest = false) => {
		return new Promise((resolve, reject) => {
			//make sure the user somehow isn't already logged in - fixes the bugs too
			logout();

			var req = blockstack.makeAuthRequest(
				blockstack.generateAndStoreTransitKey(),
				redirectUrl ? redirectUrl : `${window.location.origin}/`,
				manifest ? manifest : `${window.location.origin}/manifest.json`,
				["store_write", "publish_data"]
			);

			if (!req) {
				reject( "failed to make auth request" );
			}


			dappLoaded().then(() => {
				resolve(`http://localhost:8888/auth?authRequest=${req}`);
			}).catch(() => {
				resolve(`http://browser.blockstack.org/auth?authRequest=${req}`);
			});
		});
	};

	logout = (redirectUrl = null) => {
		blockstack.signUserOut(redirectUrl);
	};

	isSignedIn = () => {
		return new Promise((resolve, reject) => {
			if (blockstack.isUserSignedIn()) {
				var userData = blockstack.loadUserData();

				resolve(userData);
			}
			else if (blockstack.isSignInPending()) {
				blockstack.handlePendingSignIn().then((userData) => {
					resolve(userData);
				}).catch(err => {
					reject(err);
				});
			}
			else {
				reject("Not signed in");
			}
		});
	};

	/**
	 * Query the PHP app to validate the Blockstack data and log in the user.
	 * - userObj: the data returned from the Blockstack browser
	 * - serverUrl: the URL to POST the data to using XHR (or return the POST data to send if false)
	 * - Return: A promise that will resolve to the server response or the data to manually POST to the server
	 */
	phpSignIn = (userObj, serverUrl = false) => {
		return new Promise((resolve, reject) => {
			var param = location.search.split("authResponse=")[1] ? location.search.split("authResponse=")[1] : false;
			var token = jsontokens.decodeToken(param);
			var iss = token.payload.iss;

			if (!param) {
				reject({ error: true, data: "Missing 'authResponse' parameter." });
			}

			if(!iss) {
				reject({ error: true, data: "Missing iss/did." })
			}

			userObj.did = (iss.charAt(4) === "b") ? iss.replace("did:btc-addr:", "") : iss.replace("did:ecdsa-pub:", "");

			if(!serverUrl){
				getLoginDetails().then((res) => {
					userObj.login = res;
				}).catch((err) => {
					userObj.login = false;
				}).finally(() => {
					getData(serverUrl, userObj, "POST").then((res) => {
						var data;

						try {
							data = JSON.parse(res);
						}
						catch(e) {
							data = { error: true, data: `${e} response: ${res}` }
						}

						data.error ? reject(data) : resolve(data);
					}).catch((err) => {
						reject(err);
					});
				});
			} else {
				resolve(userObj);
			}
		});
	};

	getData = (url, data, method = "GET") => {
		return new Promise((resolve, reject) => {
			const req = new XMLHttpRequest();
			req.open(method, url, true);
			req.setRequestHeader("Content-type", "application/json");
			req.onreadystatechange = ( () => {
				if (req.status === 200 && req.readyState === 4) {
					resolve(req.responseText);
				}
			});
			req.onerror = (e) => reject(Error(`Network Error: ${e}`));
    		req.send(JSON.stringify(data));
		});
	};

	setLoginDetails = (username, password) => {
		var loginObj = {
			username: username,
			password: password
		};

		return blockstack.putFile("login.json", JSON.stringify(loginObj), { encrypt: true });
	};

	getLoginDetails = () => {
		return new Promise((resolve, reject) => {
			blockstack.getFile("login.json", { decrypt: true }).then((res) => {
				try {
					resolve(JSON.parse(res));
				}
				catch (e) {
					reject(e);
				}
			}).catch((err) => {
				reject(err);
			});
		});
	};

	/**
	 * This function detects whether the local Blockstack browser dapp is running
	 *
	 * This detection is rather difficult because the services are on HTTP, but the site we're logging in to may be on HTTPS
	 * in which case a non-HTTPS XHR request will not be allowed.
	 *
	 * To get around this, an image is loaded from the dapp service which doesn't suffer from the "mixed content" restriction.
	 * We can then test after a small delay (100ms) whether we have a non-zero height for the image.
	 */
	dappLoaded = () => {
		return new Promise((resolve, reject) => {
			var img = new Image()
			img.src = 'http://localhost:8888/images/icon-nav-profile.svg';
			setTimeout(() => {
				img.height ? resolve() : reject();
			}, 100);
		});
	};

	return {
		login: login,
		logout: logout,
		isSignedIn: isSignedIn,
		phpSignIn: phpSignIn,
		setLoginDetails: setLoginDetails,
		getLoginDetails: getLoginDetails,
		dappLoaded: dappLoaded
	};
})();

module.exports = BlockstackCommon;
