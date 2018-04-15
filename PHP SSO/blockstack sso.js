blockstack = require('blockstack');
jsontokens = require('jsontokens');
encryption = require("./node_modules/blockstack/lib/encryption.js");
authApp = require("./node_modules/blockstack/lib/auth/authApp.js");

var Blockstack_sso = (() => {
	var login, logout, isSignedIn, phpSignIn, getData;

	login = (serverUrl = false, blockstackServiceUrl = "http://localhost:8888") => {
		return new Promise((resolve, reject) => {
			//make sure the user somehow isn't already logged in - fixes the bugs too
			logout();

			var req = blockstack.makeAuthRequest();

			if(!req){
				reject("failed to make auth request");
			}

			resolve(blockstackServiceUrl + "/auth?authRequest=" + req);
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

	phpSignIn = (name, key, serverUrl = false) => {
		return new Promise((resolve, reject) => {
			var params;

			params = "key=" + key + "&name=" + name;

			serverUrl = serverUrl ? serverUrl + params : "http:\/\/" + window.location.hostname + "/?"  + params;

			getData(serverUrl).then((res) => {
				var data;

				try{
					data = JSON.parse(res);
				}
				catch (e){
					data = {error: true, data: e + " response: " + res}
				}

				data.error ? reject(data.data) : resolve(data.data);
			}).catch((err) => {
				reject(err);
			});
		});
	};

	getData = (url) => {
		return new Promise((resolve, reject) => {
			const req = new XMLHttpRequest();
			req.open('GET', url);
			req.onload = () => req.status === 200 ? resolve(req.response) : reject(Error(req.statusText));
			req.onerror = (e) => reject(Error(`Network Error: ${e}`));
			req.send();
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
