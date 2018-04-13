blockstack = require('blockstack');
jsontokens = require('jsontokens');
encryption = require("./node_modules/blockstack/lib/encryption.js");
authApp = require("./node_modules/blockstack/lib/auth/authApp.js");

var Blockstack_sso = (() => {
	var login, logout, isSignedIn, decryptHash, phpSignIn, hash, getData;

	login = (serverUrl = false, blockstackServiceUrl = "http://localhost:8888") => {
		return new Promise((resolve, reject) => {
			//make sure the user somehow isn't already logged in - fixes the bugs too
			logout();
			var req = blockstack.makeAuthRequest();

			serverUrl = serverUrl ? serverUrl + req : "http:\/\/" + window.location.hostname + "/?bsrequest="  + req;

			getData(serverUrl).then((res) => {
				var data;

				try{
					data = JSON.parse(res);
				}
				catch (e){
					data = {error: true, data: e + " response: " + res}
				}

				data.error ? reject(data.data) : resolve(blockstackServiceUrl + "/auth?authRequest=" + data.data);
			}).catch((err) => {
				reject(data.data);
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
			else{
				reject("Not signed in");
			}
		});
	};

	decryptHash = (encryptedData) => {
		var transitKey = authApp.getTransitKey();
		var hash = encryption.decryptECIES(transitKey, encryptedData);

		return hash;
	};

	phpSignIn = (verificationHash, name, key, serverUrl = false) => {
		return new Promise((resolve, reject) => {
			var id, params;

			id = hash(key).toString();
			params = "verificationHash=" + verificationHash + "&id=" + id + "&name=" + name;

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

	hash = (data) => {
		var hash = 0, i, chr;

		if (data.length === 0){
			return hash
		};

		for (i = 0; i < data.length; i++) {
			chr   = data.charCodeAt(i);
			hash  = ((hash << 5) - hash) + chr;
			hash |= 0; // Convert to 32bit integer
		}

		return hash;
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
		decryptHash: decryptHash,
		phpSignIn: phpSignIn
	};
})();

module.exports = Blockstack_sso;
