<?php
declare(strict_types=1);

namespace MayMeow\License;

interface LicenseInterface
{
    public function getLicense(): array;

    public function isValid(): bool;

    public function hasFeature(string $feature): bool;

    /**
     * Some features can have limits
     *
     * @param string $feature
     * @return integer|null
     */
    public function getFeatureLimit(string $feature): ?int;
}