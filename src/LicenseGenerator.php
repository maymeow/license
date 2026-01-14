<?php
declare(strict_types=1);

namespace MayMeow\License;

class LicenseGenerator
{
    /**
     * Generate license array
     *
     * @param string $name
     * @param string $email
     * @param array $applicationFeatures
     * @param integer $validDays
     * @return array
     */
    public static function generateLicense(
        string $name,
        string $email,
        array $applicationFeatures,
        int $validDays = 365
    ): array
    {
        $validUntil = time() + ($validDays * 24 * 60 * 60);
        $licenseId = hash('sha256', $name . $email . random_bytes(16) . $validUntil);

        return [
            'licenseId' => $licenseId,
            'name' => $name,
            'email' => $email,
            'features' => $applicationFeatures,
            'validUntil' => $validUntil,
        ];
    }

    public static function signLicense(
        array $licenseData,
        string $privateKey
    ): string
    {
        $licenseString = json_encode($licenseData);
        $cryptoHelper = new ECCryptographyHelper();
        $signature = $cryptoHelper->sign($licenseString, $privateKey);

        return base64_encode(json_encode([
            'license' => $licenseString,
            'signature' => $signature,
        ]));
    }

    public static function verifyLicense(
        string $signedLicense,
        string $publicKey
    ): bool
    {
        $decoded = json_decode(base64_decode($signedLicense), true);
        $licenseString = $decoded['license'];
        $signature = $decoded['signature'];

        $cryptoHelper = new ECCryptographyHelper();
        return $cryptoHelper->verify($licenseString, $signature, $publicKey);
    }
}