<?php

namespace MayMeow\License;

class License
{
    protected array $licenseDataDecoded;

    public function __construct(
        protected array $publicKey,
        protected string $licenseData
    )
    {
        $this->publicKey = $publicKey;
        $this->licenseData = $licenseData;
        $this->licenseDataDecoded = json_decode(base64_decode($licenseData), true);
    }

    protected function verifyLicense(): bool
    {
        $licenseString = $this->licenseDataDecoded['license'];
        $signature = $this->licenseDataDecoded['signature'];
        $cryptoHelper = new ECCryptographyHelper();

        return $cryptoHelper->verify(
            $licenseString,
            $signature,
            UnsignedArrayHelper::convertUnsignedArrayToKey($this->publicKey, 'public')
        );
    }

    public function getOriginalLiceseData(): string
    {
        return $this->licenseData;
    }

    public function isExpired(): bool
    {
        $licenseData = $this->getLicenseDataArray();
        $validUntil = $licenseData['validUntil'] ?? 0;

        return time() > $validUntil;
    }

    public function isValid(): bool
    {
        if ($this->isExpired()) {
            return false;
        }
        
        return $this->verifyLicense();
    }

    public function getLicenseDataArray(): array
    {
        $licenseString = $this->licenseDataDecoded['license'];
        return json_decode($licenseString, true);
    }

    public function hasFeature(string $feature): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        $licenseData = $this->getLicenseDataArray();

        return isset($licenseData['features'][$feature]);
    }

    public function getFeatureValue(string $feature): bool|int|string
    {
        $licenseData = $this->getLicenseDataArray();

        if (!$this->isValid()) {
            return false;
        }

        $features = $licenseData['features'] ?? [];

        // 1. Check if the feature acts as a KEY (e.g., 'blogs' => 5)
        if (array_key_exists($feature, $features)) {
            return $features[$feature];
        }

        // 2. Check if the feature acts as a VALUE (e.g., 'repost')
        // If found, it means the feature is enabled (true)
        if (in_array($feature, $features, true)) {
            return true;
        }

        // 3. Feature not found
        return false;
    }
}