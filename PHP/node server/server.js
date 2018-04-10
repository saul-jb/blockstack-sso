var jsontokens = require("jsontokens");
var express = require("express");
var encryption = require("./encryption.js");

var app = express();

app.get('/', function(req, res, next){
	var token, decodedToken, verified, encryptedToken;

	token = req.query.token;
	decodedToken = jsontokens.decodeToken(token);
	verified = new jsontokens.TokenVerifier(decodedToken.header.alg, decodedToken.payload.public_keys[0]).verify(req.query.token);


	hashedToken = req.query.hashedToken;

	console.log(hashedToken);

	encryptedToken = encryption.encryptECIES("03964f177782ac12fd84c18831a04a7889e02e32fe43c6ae2cc0fc2912850a26e7", "TEST");

	res.setHeader("Content-Type", "application/json");
	res.set('Connection', 'close');
	console.log({ verified: verified, encrypted: encryptedToken });
	res.json({ verified: verified, encrypted: encryptedToken });
	next();
});

app.listen(5000);
/*
eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJqdGkiOiI0YmI2OTU4MS00MjEzLTRhNDQtYmE0OC0xZThjMDAyN2E1ZDkiLCJpYXQiOiIyMDE4LTA0LTA1VDE5OjU3OjIxLjY1MloiLCJleHAiOiIyMDE5LTA0LTA1VDE4OjU3OjIxLjY1MloiLCJzdWJqZWN0Ijp7InB1YmxpY0tleSI6IjAzOTY0ZjE3Nzc4MmFjMTJmZDg0YzE4ODMxYTA0YTc4ODllMDJlMzJmZTQzYzZhZTJjYzBmYzI5MTI4NTBhMjZlNyJ9LCJpc3N1ZXIiOnsicHVibGljS2V5IjoiMDM5NjRmMTc3NzgyYWMxMmZkODRjMTg4MzFhMDRhNzg4OWUwMmUzMmZlNDNjNmFlMmNjMGZjMjkxMjg1MGEyNmU3In0sImNsYWltIjp7IkB0eXBlIjoiUGVyc29uIiwiQGNvbnRleHQiOiJodHRwOi8vc2NoZW1hLm9yZyIsIm5hbWUiOiJTYXVsIiwiZGVzY3JpcHRpb24iOiJGdWxsIHN0YWNrIGRldi4iLCJhY2NvdW50IjpbeyJAdHlwZSI6IkFjY291bnQiLCJwbGFjZWhvbGRlciI6ZmFsc2UsInNlcnZpY2UiOiJnaXRodWIiLCJpZGVudGlmaWVyIjoic2F1bC1hdmlrYXIiLCJwcm9vZlR5cGUiOiJodHRwIiwicHJvb2ZVcmwiOiJodHRwczovL2dpc3QuZ2l0aHViLmNvbS9zYXVsLWF2aWthci85YzMzYTc5OTFlOTVkNjMxYzkwMjc5MzRhNjkyMmIyMCJ9XX19.TzQPU6XLCOYizVaWCAS9fgR6u1esLRpXQsqSwpbBxlXk8WfLF1whHAlAnfy26c_OOzK0EhIL5xCn8DeH-7h0OQ
*/
/*

var jsontokens = require("jsontokens");
var express = require("express");
var bodyParser = require("body-parser");
var encryption = require("./encryption.js");
var app = express();

app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

app.get('/', function(req, res){
        var token, decodedToken, verified, encryptedToken;

        token = req.body.token;
        decodedToken = jsontokens.decodeToken(token);
console.log(decodedToken);

        verified = new jsontokens.TokenVerifier(decodedToken.header.alg, decodedToken.payload.public_keys[0]).verify(req.query.token);

        console.log(decodedToken);

        encryptedToken = encryption.encryptECIES("03964f177782ac12fd84c18831a04a7889e02e32fe43c6ae2cc0fc2912850a26e7", "TEST");

        res.setHeader("Content-Type", "application/json");
        res.set('Connection', 'close');
        res.write(JSON.stringify({ verified: verified, encrypted: encryptedToken }));
});

app.listen(5000);



*/
