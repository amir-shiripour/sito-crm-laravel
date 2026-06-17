<?php

namespace Modules\Market\App\Services\Map;

interface MapServiceInterface
{
    /**
     * Get the active map provider key/identifier (e.g. 'neshan' or 'map_ir').
     */
    public function getProviderName(): string;

    /**
     * Get the API Key for the active provider.
     */
    public function getApiKey(): ?string;

    /**
     * Reverse geocode coordinates to a human-readable address.
     *
     * @param float $lat
     * @param float $lng
     * @return array
     */
    public function reverseGeocode(float $lat, float $lng): array;

    /**
     * Search for places or addresses.
     *
     * @param string $term
     * @param float|null $lat
     * @param float|null $lng
     * @return array
     */
    public function search(string $term, ?float $lat = null, ?float $lng = null): array;
}
