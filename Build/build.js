window.blockstack = require('blockstack');
window.jsontokens = require('jsontokens');
window.encryption = require("./node_modules/blockstack/lib/encryption.js");
window.authApp = require("./node_modules/blockstack/lib/auth/authApp.js");

window.Blockstack_sso = (() => {
	var login, logout, isSignedIn, decryptHash, phpSignIn/*, getUrlParameter*/, hash, getData;

	login = (serverUrl = false, blockstackServiceUrl = "http://localhost:8888") => {
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
				data = {error: true, data: e}
			}

			if(!data.error){
				window.location.replace(blockstackServiceUrl + "/auth?authRequest=" + data.data);
			}
			else{
				console.error(data.data);
			}
		}).catch((err) => {
			console.error(err);
		});
	};

	logout = (redirectUrl = null) => {
		blockstack.signUserOut(redirectUrl);
	};

	isSignedIn = (successCallback, failureCallback, timeout = 2000) => {
		var failureTimout = setTimeout(failureCallback, timeout);

		if (blockstack.isUserSignedIn()) {
			var userData = blockstack.loadUserData();

			clearTimeout(failureTimout);
			successCallback(userData);
		}
		else if (blockstack.isSignInPending()) {
			clearTimeout(failureTimout);
			blockstack.handlePendingSignIn().then((userData) => {
				successCallback(userData);
			}).catch(err => {
				failureCallback(err);
			});
		}
	};

	decryptHash = (encryptedData) => {
		console.log("encryptedData", encryptedData)
		var transitKey = authApp.getTransitKey();
		var hash = encryption.decryptECIES(transitKey, encryptedData);

		return hash;
	};

	phpSignIn = (verificationHash, name, key, authUrl) => {
		var id, data, redirectUrl;

		id = hash(key).toString();
		console.log("ID", id);

		redirectUrl = authUrl + "?name=" + name + "&id=" + id + "&verificationHash=" + verificationHash;

		//window.location.replace(redirectUrl);

		getData(redirectUrl).then((res) => {
			console.log(res);
			var data;

			try{
				data = JSON.parse(res);
			}
			catch (e){
				data = {error: true, data: e}
			}

			data.error ? console.error(data.data) : console.log(data.data);
		}).catch((err) => {
			console.error(err);
		});
	};
/*
	getUrlParameter = (sParam) => {
		var sPageURL, sURLVariables, i, sParameterName;

		sPageURL = decodeURIComponent(window.location.search.substring(1));
		sURLVariables = sPageURL.split('&');

		for (i = 0; i < sURLVariables.length; i++) {
			sParameterName = sURLVariables[i].split('=');

			if (sParameterName[0] === sParam) {
				return sParameterName[1] === undefined ? false : sParameterName[1];
			}
		}
	};*/

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
