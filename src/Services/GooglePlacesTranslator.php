<?php

namespace MetaFramework\Services;

use Illuminate\Support\Facades\Http;

class GooglePlacesTranslator
{
    public function __construct(
        private ?string $apiKey = null,
        private int $timeoutSeconds = 8,
    ) {
        $this->apiKey = $this->apiKey ?? config('mfw-inputable.google.places_api_key');
    }

    /**
     * @param  array<int, string>  $locales
     * @param  array<string, array<string, string>>  $existing
     * @return array<string, array<string, string>>
     */
    public function translations(string $placeId, array $locales, array $existing = []): array
    {
        if (! $placeId || ! $this->apiKey) {
            return $existing;
        }

        $translations = $existing;

        foreach ($locales as $locale) {
            $details = $this->fetchDetails($placeId, $locale);
            if (! $details) {
                continue;
            }

            $fields = $this->extractTranslatableFields($details);

            foreach ($fields as $field => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $translations[$field][$locale] = $value;
            }
        }

        return $translations;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchDetails(string $placeId, string $locale): array
    {
        $details = $this->fetchPlacesV1($placeId, $locale);
        if ($details) {
            return $details;
        }

        return $this->fetchLegacy($placeId, $locale);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPlacesV1(string $placeId, string $locale): array
    {
        $response = Http::timeout($this->timeoutSeconds)
            ->withHeaders([
                'X-Goog-Api-Key' => $this->apiKey,
                'X-Goog-FieldMask' => 'addressComponents,formattedAddress',
            ])
            ->get('https://places.googleapis.com/v1/places/' . $placeId, [
                'languageCode' => $locale,
            ]);

        if (! $response->ok()) {
            return [];
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return [];
        }

        if (empty($payload['addressComponents']) && empty($payload['formattedAddress'])) {
            return [];
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchLegacy(string $placeId, string $locale): array
    {
        $response = Http::timeout($this->timeoutSeconds)->get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'address_component,formatted_address',
            'language' => $locale,
            'key' => $this->apiKey,
        ]);

        if (! $response->ok()) {
            return [];
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            return [];
        }

        if (($payload['status'] ?? null) !== 'OK') {
            return [];
        }

        return is_array($payload['result'] ?? null) ? $payload['result'] : [];
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, string|null>
     */
    private function extractTranslatableFields(array $details): array
    {
        $components = $details['addressComponents'] ?? $details['address_components'] ?? [];
        $fields = [];

        foreach ($components as $component) {
            $types = $component['types'] ?? [];
            foreach ($types as $type) {
                if ($type === 'route') {
                    $fields['route'] = $this->componentText($component);
                }
                if ($type === 'locality') {
                    $fields['locality'] = $this->componentText($component);
                }
                if ($type === 'administrative_area_level_1') {
                    $fields['administrative_area_level_1'] = $this->componentText($component);
                }
                if ($type === 'administrative_area_level_2') {
                    $fields['administrative_area_level_2'] = $this->componentText($component);
                }
            }
        }

        $fields['text_address'] = $details['formattedAddress'] ?? $details['formatted_address'] ?? null;

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $component
     */
    private function componentText(array $component): ?string
    {
        if (isset($component['longText'])) {
            return $component['longText'];
        }

        if (isset($component['long_name'])) {
            return $component['long_name'];
        }

        return null;
    }
}
