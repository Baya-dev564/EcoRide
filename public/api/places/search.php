<?php
// public/api/places/search.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// CORRECTION : bon chemin pour l'autoloader
require_once __DIR__ . '/../../../app/Services/PlacesService.php';

$query = $_GET['q'] ?? '';

if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètre q manquant']);
    exit;
}

try {
    $placesService = new \App\Services\PlacesService();
    $places = $placesService->searchPlaces($query);
    
    echo json_encode($places);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
