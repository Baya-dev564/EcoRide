<?php
/**
 * Je charge les variables d'environnement depuis le fichier .env
 * Compatible avec Hostinger et Docker
 */

function chargerEnvironnement($cheminEnv = null)
{
    // Je définis le chemin du fichier .env
    if ($cheminEnv === null) {
        $cheminEnv = __DIR__ . '/../.env';
    }
    
    // Je vérifie si le fichier .env existe
    if (!file_exists($cheminEnv)) {
        error_log("INFO: Fichier .env non trouvé à : $cheminEnv");
        return false;
    }
    
    // Je lis le fichier .env ligne par ligne
    $lignes = file($cheminEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lignes as $ligne) {
        // Je supprime les espaces
        $ligne = trim($ligne);
        
        // J'ignore les commentaires et les lignes vides
        if (empty($ligne) || strpos($ligne, '#') === 0) {
            continue;
        }
        
        // Je sépare la clé et la valeur (NOM=valeur)
        if (strpos($ligne, '=') === false) {
            continue;
        }
        
        list($nom, $valeur) = explode('=', $ligne, 2);
        
        $nom = trim($nom);
        $valeur = trim($valeur);
        
        // Je retire les guillemets si présents
        $valeur = trim($valeur, '"\'');
        
        // Je définis la variable d'environnement seulement si elle n'existe pas déjà
        if (!getenv($nom)) {
            putenv("$nom=$valeur");
            $_ENV[$nom] = $valeur;
            $_SERVER[$nom] = $valeur;
        }
    }
    
    error_log("INFO: Fichier .env chargé avec succès depuis : $cheminEnv");
    return true;
}

// Je charge automatiquement le .env quand ce fichier est inclus
chargerEnvironnement();
?>
