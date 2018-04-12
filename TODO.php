<script>
// This function needs to get converted to PHP so we can get rid of the need for a node server
function encryptECIES(publicKey, content) {
	var isString = typeof content === 'string';
	var plainText = new Buffer(content); // always copy to buffer

	var ecPK = ecurve.keyFromPublic(publicKey, 'hex').getPublic();
	var ephemeralSK = ecurve.genKeyPair();
	var ephemeralPK = ephemeralSK.getPublic();
	var sharedSecret = ephemeralSK.derive(ecPK);

	var sharedSecretHex = getHexFromBN(sharedSecret);

	var sharedKeys = sharedSecretToKeys(new Buffer(sharedSecretHex, 'hex'));

	var initializationVector = _crypto2.default.randomBytes(16);

	var cipherText = aes256CbcEncrypt(initializationVector, sharedKeys.encryptionKey, plainText);

	var macData = Buffer.concat([initializationVector, new Buffer(ephemeralPK.encodeCompressed()), cipherText]);
	var mac = hmacSha256(sharedKeys.hmacKey, macData);

	return {
		iv: initializationVector.toString('hex'),
		ephemeralPK: ephemeralPK.encodeCompressed('hex'),
		cipherText: cipherText.toString('hex'),
		mac: mac.toString('hex'),
		wasString: isString
	};
}
</script>

<?php
function encryptECIES($publicKey, $content) {
	$cipher = "aes-256-cbc";
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = openssl_random_pseudo_bytes($ivlen);

	$encryptedcontent = openssl_encrypt($content, $cipher, $publicKey, OPENSSL_RAW_DATA, $iv);

	$wasString = false;
	$ivHex = implode(unpack("H*", $iv));
	$encryptedcontentvHex = implode(unpack("H*", $encryptedcontent));

	if(gettype($content) == "string"){
		$wasString = true;
	}

	return json_encode(
		array(
			"iv" => $ivHex,
			"ephemeralPK" => "?",
			"cipherText" => $encryptedcontentvHex,
			"mac" => "?",
			"wasString" => $wasString
		)
	);
}
?>
