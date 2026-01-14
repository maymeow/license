<?php
declare(strict_types=1);

use MayMeow\License\ECCryptographyHelper;
use MayMeow\License\LicenseGenerator;
use MayMeow\License\UnsignedArrayHelper;

require_once __DIR__ . '/vendor/autoload.php';

$applicationEcKey = [
    48,89,48,19,6,7,42,134,72,206,61,2,1,6,8,42,134,72,206,61,3,1,7,3,66,0,4,170,90,97,76,217,
    77,97,101,134,132,53,122,230,92,138,212,148,32,87,159,208,75,56,80,103,210,77,197,159,164,
    95,181,210,156,47,2,16,56,222,212,250,211,140,227,147,63,192,215,28,24,130,70,78,254,145,17,
    135,84,168,27,42,252,124,69
];

$licensePackage = "eyJsaWNlbnNlIjoie1widXNlclwiOlwiSm9obiBEb2VcIixcImVtYWlsXCI6XCJ0ZXN0QGxvY2FsLnRsZFwifSIsInNpZ25hdHVyZSI6Ik1FVUNJRjBocXhIazRwbzh4OWx5VSt2UmlmdnhqY2pCQlhZRkNENXdESWFqSVNRNkFpRUFwWFUwNzV5b2VZT041NnFmbFZqcTRkVGx4cDdncCtwSHRyYnEwN1M3V2d3PSJ9";
$packageDecoded = json_decode(base64_decode($licensePackage), true);

var_dump($packageDecoded);

$cryptoHelpder = new ECCryptographyHelper();
$result = $cryptoHelpder->verify(
    $packageDecoded['license'],
    $packageDecoded['signature'],
    UnsignedArrayHelper::convertUnsignedArrayToKey($applicationEcKey, 'public')
);

var_dump($result);

$license = LicenseGenerator::generateLicense(
    'John Doe',
    'test@local.tld',
    [
        'blogs' => 5,
        'repost',
    ]
);

var_dump($license);
