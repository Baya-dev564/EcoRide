<?php

namespace App\Services;

class PlacesService
{
    private string $nominatimUrl = 'https://nominatim.openstreetmap.org/search';

    public function searchPlaces(string $query, string $type = '', string $city = ''): array
    {
        $params = [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => '1',
            'limit' => '8',
            'countrycodes' => 'fr',
            'accept-language' => 'fr'
        ];

        $url = $this->nominatimUrl . '?' . http_build_query($params);
        
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: EcoRide-App/1.0\r\n",
                'timeout' => 10
            ]
        ]);

        try {
            $response = file_get_contents($url, false, $context);
            if ($response === false) {
                return [];
            }
            
            $data = json_decode($response, true);
            if (!is_array($data)) {
                return [];
            }

            return $this->formatPlaces($data);
            
        } catch (\Exception $e) {
            error_log("Erreur API Nominatim: " . $e->getMessage());
            return [];
        }
    }

    private function formatPlaces(array $data): array
    {
        $places = [];
        
        foreach ($data as $item) {
            $address = $item['address'] ?? [];
            
            $name = $item['name'] ?? 'Lieu inconnu';
            if (empty($name) && isset($item['display_name'])) {
                $parts = explode(',', $item['display_name']);
                $name = trim($parts[0]);
            }

            $places[] = [
                'id' => 'api_' . md5($item['osm_type'] . $item['osm_id']),
                'name' => $name,
                'full_name' => $item['display_name'] ?? '',
                'type' => 'autre',
                'latitude' => (float)$item['lat'],
                'longitude' => (float)$item['lon'],
                'address' => $address['road'] ?? '',
                'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
                'postal_code' => $address['postcode'] ?? '',
                'icon' => '📍'
            ];
        }

        return $places;
    }
}
