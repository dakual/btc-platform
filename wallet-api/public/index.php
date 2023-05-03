<?php

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;

require __DIR__ . "/../vendor/autoload.php";

$network = Bitcoin::getNetwork();

$random = new Random();
$privKeyFactory = new PrivateKeyFactory();
$privateKey = $privKeyFactory->generateCompressed($random);
$publicKey = $privateKey->getPublicKey();

echo "Key Info<br>";
echo " - Compressed? " . (($privateKey->isCompressed() ? 'yes' : 'no')) . "<br>";

echo "Private key<br>";
echo " - WIF: " . $privateKey->toWif($network) . "<br>";
echo " - Hex: " . $privateKey->getHex() . "<br>";
echo " - Dec: " . gmp_strval($privateKey->getSecret(), 10) . "<br>";

echo "Public Key<br>";
echo " - Hex: " . $publicKey->getHex() . "<br>";
echo " - Hash: " . $publicKey->getPubKeyHash()->getHex() . "<br>";

$address = new PayToPubKeyHashAddress($publicKey->getPubKeyHash());
echo " - Address: " . $address->getAddress() . "<br>";