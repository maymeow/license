<?php

use MayMeow\License\License;
use MayMeow\License\LicenseGenerator;
use MayMeow\License\ECCryptographyHelper;
use MayMeow\License\RSACryptographyHelper;
use MayMeow\License\UnsignedArrayHelper;

require_once __DIR__ . '/vendor/autoload.php';

$cryptoHelper = new ECCryptographyHelper();

[$generatorKey, $applicationKey] = $cryptoHelper->generateEcKeyPair();

$license = LicenseGenerator::generateLicense(
    'Alice Smith',
    'alice@smith.tld',
    [
        'blogs' => 5
    ]
);

$signedLicense = LicenseGenerator::signLicense($license, $generatorKey);

var_dump($signedLicense);

$verificationResult = LicenseGenerator::verifyLicense($signedLicense, $applicationKey);

var_dump($verificationResult);

$applicationKeyBytes = UnsignedArrayHelper::convertKeyToUnsignedArray($applicationKey);

$licenseInstance = new License($applicationKeyBytes, $signedLicense);

var_dump($licenseInstance->hasFeature('blogs'));
