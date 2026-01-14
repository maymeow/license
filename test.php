<?php

use MayMeow\License\License;
use MayMeow\License\LicenseGenerator;
use MayMeow\License\ECCryptographyHelper;
use MayMeow\License\RSACryptographyHelper;

require_once __DIR__ . '/vendor/autoload.php';

/**
$CryptographyHelper = new ECCryptographyHelper();

// Generate keys
[$generatorPriv, $generatorPub] = $CryptographyHelper->generateEcKeyPair();
[$applicationPriv, $applicationPub]     = $CryptographyHelper->generateEcKeyPair();

// Alice encrypts for Bob
$sharedAlice = $CryptographyHelper->deriveSharedSecret($generatorPriv, $applicationPub);
$encrypted   = $CryptographyHelper->encryptString('Hello EC world', $sharedAlice); // signing license...

// Bob decrypts
$sharedBob =$CryptographyHelper->deriveSharedSecret($applicationPriv, $generatorPub);
$plaintext = $CryptographyHelper->decryptString($encrypted, $sharedBob);

echo $plaintext; // Hello EC world
*/

$cryptograhyHelper = new RSACryptographyHelper();

[$generatorKey, $applicationKey] = $cryptograhyHelper->generateRsaKeyPair();
[$generatorEcKey, $applicationEcKey] = (new ECCryptographyHelper())->generateEcKeyPair();

var_dump($applicationEcKey);

$applicationKeyArray = $cryptograhyHelper->convertKeyToUnsignedArray($generatorEcKey);

echo "Application Key as Unsigned Array:\n";
file_put_contents('application_key_array.php', sprintf('$applicationArray = [%s]', implode(',', $applicationKeyArray)));



$licenseData = json_encode([
    'user' => 'John Doe',
    'expiry' => '2025-12-31',
]);

$encryptedLicense = $cryptograhyHelper->encryptWithPrivate($licenseData, $generatorKey);

$decryptedLicense = $cryptograhyHelper->decryptWithPublic(
    $encryptedLicense,
    $cryptograhyHelper->convertUnsignedArrayToKey($applicationKeyArray, 'public')
);

echo $decryptedLicense; // {"user":"John Doe","expiry":"2025-12-31"}

