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
}