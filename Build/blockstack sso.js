window.blockstack = require('blockstack');
window.jsontokens = require('jsontokens');
window.encryption = require("./node_modules/blockstack/lib/encryption.js");
window.authApp = require("./node_modules/blockstack/lib/auth/authApp.js");


var Blockstack_sso = (() => {
	var login, logout, isSignedIn, decryptHash, phpSignIn, getUrlParameter, postData;

	login = () => {
		var req = blockstack.makeAuthRequest();
		var url = "http:\/\/" + window.location.hostname + "/?authResponse="  + req;

		window.location.replace(url);
	};

	logout = () => {
		blockstack.signUserOut(window.location.href);
	};

	isSignedIn = (successCallback, failureCallback, timeout = 2000) => {
		var failureTimout = setTimeout(failureCallback, timeout);

		if (blockstack.isUserSignedIn()) {
			var userData = blockstack.loadUserData();

			clearTimeout(failureTimout);
			successCallback(userData);
		}
		else if (blockstack.isSignInPending()) {
			blockstack.handlePendingSignIn().then((userData) => {
				clearTimeout(failureTimout);
				successCallback(userData);
			}).catch(err => {
				failureCallback(err);
			});
		}
	};

	decryptHash = (encryptedData) => {
		var transitKey = authApp.getTransitKey();
		var hash = encryption.decryptECIES(transitKey, encryptedData);

		return hash;
	};

	phpSignIn = (verificationHash, name, callback) => {
		var id, decodedToken, token, postData;

		token = getUrlParameter("authResponse");
		decodedToken = jsontokens.decodeToken(token);
		id = decodedToken.payload.public_keys[0];

		postData = {
			token,
			id,
			verificationHash,
			name
		};

		postData(postData, callback);
	};

	postData = (data, callback) => {
		var http = new XMLHttpRequest();
		var url = "get_data.php";
		var params = "name=" + data.name + "&id=" + data.id + "&hash=" + data.verificationHash + "&token=" + data.token;

		http.open("POST", url, true);
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

		http.onreadystatechange = function() {
			if(http.readyState == 4 && http.status == 200) {
				callback(http.responseText);
			}
		}

		http.send(params);
	}

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
	};

	return {
		login: login,
		logout: logout,
		isSignedIn: isSignedIn,
		decryptHash: decryptHash,
		phpSignIn: phpSignIn
	};
})();

exports.Blockstack_sso = Blockstack_sso
