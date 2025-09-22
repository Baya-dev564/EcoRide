<?php

class GeolocationService 
{
    /**
     * Je convertis une adresse en coordonnées GPS (géocodage)
     * J'utilise l'API gratuite de Nominatim (OpenStreetMap) avec gestion d'erreurs
     */
    public function geocodeAddress($address, $postalCode = null) 
    {
        $fullAddress = $address;
        if ($postalCode) {
            $fullAddress .= ', ' . $postalCode;
        }
        $fullAddress .= ', France';
        
        $url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($fullAddress);
        
        // Je configure le contexte avec un User-Agent
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: EcoRide/1.0 (contact@ecoride.fr)',
                    'Accept: application/json'
                ],
                'timeout' => 10
            ]
        ]);
        
        try {
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                // Je log l'erreur mais je continue sans coordonnées
                error_log("Géocodage échoué pour : " . $fullAddress);
                return null;
            }
            
            $data = json_decode($response, true);
            
            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                return [
                    'latitude' => (float)$data[0]['lat'],
                    'longitude' => (float)$data[0]['lon'],
                    'display_name' => $data[0]['display_name'] ?? $fullAddress
                ];
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Erreur géocodage : " . $e->getMessage());
            return null;
        }
    }
}
