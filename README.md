# License package for PHP applications

Lightweight helper to issue and validate signed licenses for your PHP apps using elliptic-curve cryptography.

## Requirements
- PHP 8.1+ with the OpenSSL extension enabled

## Installation

If published to Packagist:

```bash
composer require maymeow/license
```

For local development in this repo:

```bash
composer install
```

## Quick start

Generate a key pair for your license issuer and application, sign a license, and validate it.

```php
use MayMeow\License\ECCryptographyHelper;
use MayMeow\License\License;
use MayMeow\License\LicenseGenerator;
use MayMeow\License\UnsignedArrayHelper;

require __DIR__ . '/vendor/autoload.php';

$crypto = new ECCryptographyHelper();

// 1) Create EC key pairs
[$issuerPrivateKey, $issuerPublicKey] = $crypto->generateEcKeyPair();

// 2) Build license payload
$licensePayload = LicenseGenerator::generateLicense(
	name: 'Alice Smith',
	email: 'alice@example.com',
	applicationFeatures: ['blogs' => 5],
	validDays: 365
);

// 3) Sign license with issuer private key
$signedLicense = LicenseGenerator::signLicense($licensePayload, $issuerPrivateKey);

// 4) Validate in your app using issuer public key
$appPublicKeyBytes = UnsignedArrayHelper::convertKeyToUnsignedArray($issuerPublicKey);
$license = new License($appPublicKeyBytes, $signedLicense);

if ($license->isValid()) {
	// gate features
	if ($license->hasFeature('blogs')) {
		// allow feature
	}
}
```

## API highlights
- `LicenseGenerator::generateLicense()` builds license metadata (id, owner, email, features, expiry).
- `LicenseGenerator::signLicense()` signs the license JSON with an EC private key and returns a compact base64 package.
- `LicenseGenerator::verifyLicense()` verifies a signed package with an EC public key.
- `License` instance wraps a signed package and exposes:
  - `isValid()` signature + expiry check
  - `isExpired()` expiry only
  - `hasFeature($key)` convenience feature flag lookup
  - `getOriginalLiceseData()` returns the original base64 payload

### Handling keys as unsigned byte arrays
If you store keys as unsigned byte arrays (e.g., in config), convert them with `UnsignedArrayHelper`:

```php
// Convert PEM to array
$publicKeyBytes = UnsignedArrayHelper::convertKeyToUnsignedArray($issuerPublicKey);

// Convert array back to PEM
$publicKeyPem = UnsignedArrayHelper::convertUnsignedArrayToKey($publicKeyBytes, 'public');
```

## Additional helpers
- `ECCryptographyHelper` also supports shared-secret derivation (`deriveSharedSecret`), HKDF, and AES-256-GCM encrypt/decrypt for custom payload handling.
- `RSACryptographyHelper` offers basic RSA key generation and private/public encrypt/decrypt helpers if you prefer RSA.

## Running the examples
- `php test.php` generates a license, signs it, and validates it end-to-end.
- `php verificationtest.php` demonstrates verifying a prebuilt license package.

## License
MIT
