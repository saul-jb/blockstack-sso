var jsontokens = require("jsontokens");
var express = require("express");
var encryption = require("./encryption.js");

var app = express();

app.get('/', function(req, res, next){
	var token, decodedToken, verified, encryptedToken;

	token = req.query.token;
	decodedToken = jsontokens.decodeToken(token);
	hashedToken = req.query.hashedToken;
	encryptedToken = encryption.encryptECIES(decodedToken.payload.public_keys[0],  hashedToken);
	verified = new jsontokens.TokenVerifier(decodedToken.header.alg, decodedToken.payload.public_keys[0]).verify(req.query.token);

	res.setHeader("Content-Type", "application/json");
	res.set('Connection', 'close');
	res.json({ verified: verified, encrypted: encryptedToken });
	next();
});

app.listen(5000);
