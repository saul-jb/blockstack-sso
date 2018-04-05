<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
?>
{
	"name": "Wordpress Blockstack Log-in",
	"start_url": "<?php echo $_SERVER['SERVER_NAME']; ?>",
	"description": "The blockstack plugin to log into wordpress with blockstack",
	"icons": [
		{
			"src": "https://helloblockstack.com/icon-192x192.png",
			"sizes": "192x192",
			"type": "image/png"
		}
	]
}
