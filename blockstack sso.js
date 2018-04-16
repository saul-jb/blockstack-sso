blockstack = require('blockstack');
jsontokens = require('jsontokens');

var Blockstack_sso = (() => {
	var login, logout, isSignedIn, phpSignIn, getData;

	login = (serverUrl = false, blockstackServiceUrl = "http://browser.blockstack.org") => {
		return new Promise((resolve, reject) => {
			//make sure the user somehow isn't already logged in - fixes the bugs too
			logout();

			var req = blockstack.makeAuthRequest();

			if(!req){
				reject("failed to make auth request");
			}

			resolve(`${blockstackServiceUrl}/auth?authRequest=${req}`);
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
			else{
				reject("Not signed in");
			}
		});
	};

	phpSignIn = (userObj, serverUrl) => {
		return new Promise((resolve, reject) => {
			var param = location.search.split('authResponse=')[1] ? location.search.split('authResponse=')[1] : false;
			var token = jsontokens.decodeToken(param);
			var iss = token.payload.iss;

			if(!iss){
				reject("missing iss/did")
			}

			userObj.did = (iss.charAt(4) === "b") ? iss.replace("did:btc-addr:", "") : iss.replace("did:ecdsa-pub:", "");

			getData(serverUrl, userObj, "POST").then((res) => {
				var data;

				try{
					data = JSON.parse(res);
				}
				catch (e){
					data = {error: true, data: `${e} response: ${res}`}
				}

				data.error ? reject(data.data) : resolve(data.data);
			}).catch((err) => {
				reject(err);
			});
		});
	};

	getData = (url, data, method = "GET") => {
		return new Promise((resolve, reject) => {
			const req = new XMLHttpRequest();
			req.open(method, url, true);
			req.setRequestHeader("Content-type", "application/json");
			req.onreadystatechange = (() => {
				if(req.status === 200 && req.readyState === 4){
					resolve(req.responseText);
				}
			});
			req.onerror = (e) => reject(Error(`Network Error: ${e}`));
    		req.send(JSON.stringify(data));
		});
	}

	return {
		login: login,
		logout: logout,
		isSignedIn: isSignedIn,
		phpSignIn: phpSignIn
	};
})();

module.exports = Blockstack_sso;
