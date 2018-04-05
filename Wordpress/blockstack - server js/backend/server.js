const express = require('express');
const path = require('path');

const app = express();
const port = 5000;

app.use((req, res, next) => {
	res.header('Access-Control-Allow-Origin', '*');
	res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
	res.header('Access-Control-Allow-Headers', 'Content-Type');
	next();
});

app.get('/manifest.json', (req, res) => {
	res.sendFile(path.join(__dirname + '/manifest.json'));
});

app.get('/test', (req, res) => {
	res.sendFile(path.join(__dirname + '/test.html'));
});

app.get('/', (req, res) => {
	res.sendFile(path.join(__dirname + '/bundle.js'));
});

app.get('*', (req, res) => {
	res.send({
		Error: 404,
		Message: "Not found refer to '/' for the blockstack javascript, or '/manifest.json' for the manifest"
	});
});

app.listen(port, (err) => {
	if(err){
		return console.error('Error: ', err);
	}
});
