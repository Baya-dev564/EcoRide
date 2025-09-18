<?php
/**
 * Modèle AvisMongo pour EcoRide - Système NoSQL MongoDB + MySQL Hybride
 * TP Développement Web - Architecture MVC hybride 
 * 
 * ARCHITECTURE CHOISIE :
 * - MongoDB : Stockage des avis (données NoSQL pour flexibilité)
 * - MySQL : Stockage des utilisateurs (données relationnelles pour cohérence)
 * - PHP : Logique de jointure entre les deux bases (performance optimisée)
 * 
 * Développé pour environnement Docker avec PHP natif
 * @author Développeur EcoRide 
 * @version 2.2 - CORRECTION système de modération avec affichage tous avis validés
 */

class AvisMongo 
{
    // ========== PROPRIÉTÉS DE CONNEXION ==========
    
    /** @var MongoDB\Driver\Manager - Gestionnaire de connexion MongoDB */
    private $manager;
    
    /** @var string - Nom de la base de données MongoDB */
    private $database = 'EcoRide';
    
    /** @var string - Nom de la collection MongoDB pour les avis */
    private $collection = 'avis';

    /**
     * Constructeur - J'établis la connexion avec MongoDB dans Docker
     * 
     * CONNEXION SÉCURISÉE DOCKER :
     * - J'utilise les identifiants définis dans docker-compose.yml
     * - J'authentifie sur la base admin (MongoDB requirement)
     * - Je gère les erreurs de façon robuste pour éviter les crashes silencieux
     */
    public function __construct() 
    {
        try {
            // Je connecte MongoDB avec paramètres Docker Compose
            // Format: mongodb://user:password@container:port/database?authSource=admin
            $this->manager = new MongoDB\Driver\Manager(
                "mongodb://ecoride:ecoride123@mongo:27017/EcoRide?authSource=admin"
            );
            
            // Je log le succès pour debug (retiré en production)
            error_log("DEBUG: Connexion MongoDB établie avec succès");
            
        } catch (Exception $e) {
            // J'arrête tout car MongoDB indispensable pour les avis
            die('ERREUR CRITIQUE - Connexion MongoDB échouée: ' . $e->getMessage());
        }
    }

    // ========== MÉTHODES CRUD MONGODB ==========

    /**
     * J'ajoute un nouvel avis dans MongoDB - AVEC MODÉRATION
     * 
     * LOGIQUE MÉTIER MODIFIÉE :
     * - Je valide les données en amont (contrôleur)
     * - Je stocke avec timestamp MongoDB natif pour tri chronologique
     * - Je mets statut 'en_attente' par défaut (NOUVEAU : système de modération)
     * - Je retourne une réponse standardisée success/error pour API JSON
     * 
     * @param array $donnees - Données validées de l'avis
     * @return array - Résultat standardisé de l'opération
     */
    public function ajouterAvis($donnees) 
    {
        try {
            // Je structure le document MongoDB optimisé pour les requêtes
            $avis = [
                'trajet_id' => (int)$donnees['trajet_id'], // Je cast pour cohérence type
                'utilisateur_id' => (int)$donnees['utilisateur_id'], // Je référence vers MySQL
                'nom_utilisateur' => $donnees['nom_utilisateur'], // Je cache le pseudo pour perfs
                'note' => (int)$donnees['note'], // Je valide range 1-5 en amont
                'commentaire' => $donnees['commentaire'], // Je limite texte libre 500 chars
                'date_creation' => new MongoDB\BSON\UTCDateTime(), // Je timestamp MongoDB natif
                'statut' => 'en_attente' // MODIFIÉ : J'attends validation admin
            ];

            // J'effectue l'opération d'écriture atomique MongoDB
            $bulk = new MongoDB\Driver\BulkWrite;
            $id = $bulk->insert($avis); // Je récupère l'ObjectId généré
            
            // J'exécute l'écriture sur le cluster MongoDB
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection, $bulk);
            
            // Je log pour suivi d'activité (optionnel en production)
            error_log("DEBUG: Avis ajouté en attente - ID: " . (string)$id);
            
            // Je retourne une réponse standardisée pour API JSON - MESSAGE MODIFIÉ
            return [
                'success' => true,
                'avis_id' => (string)$id, // Je convertis ObjectId → string pour JSON
                'message' => 'Avis soumis avec succès. Il sera visible après validation par l\'équipe.'
            ];

        } catch (Exception $e) {
            // Je log l'erreur technique pour debug
            error_log("ERREUR ajouterAvis MongoDB: " . $e->getMessage());
            
            // Je retourne une erreur standardisée (pas d'info technique côté client)
            return [
                'success' => false,
                'error' => 'Erreur technique lors de l\'ajout de l\'avis'
            ];
        }
    }

    /**
     * NOUVELLE MÉTHODE CORRIGÉE : Je récupère TOUS les avis validés (pour page publique utilisateurs)
     * 
     * UTILITÉ PRINCIPALE :
     * - Page publique /avis : J'affiche tous les avis validés par l'admin
     * - DIFFÉRENT de getTousLesAvis() qui est pour l'admin (tous statuts)
     * - REMPLACE getAvisParTrajet() pour la page publique
     * 
     * ARCHITECTURE HYBRIDE :
     * - Je récupère depuis MongoDB (tous les avis validés)
     * - J'enrichis avec pseudos MySQL
     * - Je trie chronologique pour meilleur UX
     * 
     * @return array - Tous les avis actifs formatés avec pseudos
     */
    public function getTousLesAvisValidés() 
    {
        try {
            // ÉTAPE 1 : Je récupère TOUS les avis VALIDÉS (pour utilisateurs)
            $filter = ['statut' => 'actif']; // Seulement les avis validés par l'admin
            $options = ['sort' => ['date_creation' => -1]]; // Je mets plus récents en premier
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            // Je convertis curseur → tableau PHP
            $avis_mongo = [];
            foreach ($cursor as $avis) {
                $avis_mongo[] = [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id ?? '',
                    'utilisateur_id' => $avis->utilisateur_id,
                    'nom_utilisateur' => $avis->nom_utilisateur ?? '',
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s'),
                    'statut' => 'actif'
                ];
            }

            // ÉTAPE 2 : J'extrais les IDs utilisateur uniques
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));

            // ÉTAPE 3 : J'enrichis avec pseudos MySQL
            $pseudos = $this->getPseudosUtilisateurs($user_ids);

            // ÉTAPE 4 : Je fusionne avec pseudos à jour
            $avis_formates = [];
            foreach ($avis_mongo as $avis) {
                $pseudo_mysql = $pseudos[$avis['utilisateur_id']] ?? null;
                $avis['nom_utilisateur'] = $pseudo_mysql ?: $avis['nom_utilisateur'] ?: 'Utilisateur Inconnu';
                
                $avis_formates[] = $avis;
            }

            error_log("DEBUG: " . count($avis_formates) . " avis validés pour utilisateurs");

            return [
                'success' => true,
                'avis' => $avis_formates,
                'total' => count($avis_formates)
            ];

        } catch (Exception $e) {
            error_log('ERREUR getTousLesAvisValidés: ' . $e->getMessage());
            
            return [
                'success' => false,
                'avis' => [],
                'error' => 'Erreur lors de la récupération des avis'
            ];
        }
    }

    /**
     * Je récupère tous les avis avec pseudos utilisateurs depuis MySQL (par trajet spécifique)
     * 
     * ARCHITECTURE HYBRIDE OPTIMISÉE :
     * Étape 1: Je récupère les avis depuis MongoDB (rapide, NoSQL)
     * Étape 2: J'extrais les IDs utilisateur uniques (évite doublons)
     * Étape 3: Je requête MySQL groupée pour les pseudos (1 seule requête)
     * Étape 4: Je fusionne les données en PHP (jointure manuelle optimisée)
     * 
     * AVANTAGES vs duplication :
     * - Source unique de vérité pour les utilisateurs (MySQL)
     * - Pseudos toujours à jour (changements auto-répercutés)
     * - Pas de synchronisation base à base
     * - Performance: O(1) requête MySQL même pour 1000 avis
     * 
     * @param int $trajet_id - ID du trajet spécifique
     * @return array - Avis formatés avec pseudos MySQL pour ce trajet
     */
    public function getAvisParTrajet($trajet_id) 
    {
        try {
            // ÉTAPE 1 : Je récupère avis MongoDB (CORRIGÉ : filtre VRAIMENT par trajet)
            if ($trajet_id && $trajet_id !== '') {
                $filter = [
                    'trajet_id' => (int)$trajet_id, // ✅ JE FILTRE PAR TRAJET MAINTENANT !
                    'statut' => 'actif'
                ];
                error_log("DEBUG getAvisParTrajet: Filtre trajet_id = $trajet_id");
            } else {
                $filter = ['statut' => 'actif']; // Tous les avis validés si pas de trajet
                error_log("DEBUG getAvisParTrajet: Tous les avis actifs (pas de trajet spécifique)");
            }
            
            $options = ['sort' => ['date_creation' => -1]]; // Je mets plus récents en premier
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            // Je convertis curseur → tableau PHP pour manipulation
            $avis_mongo = [];
            foreach ($cursor as $avis) {
                $avis_mongo[] = [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id,
                    'utilisateur_id' => $avis->utilisateur_id, // CLEF pour jointure MySQL
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s')
                ];
            }

            // ÉTAPE 2 : J'extrais les IDs utilisateur uniques (optimisation)
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            error_log("DEBUG: IDs utilisateur à récupérer: " . count($user_ids));

            // ÉTAPE 3 : Je récupère pseudos MySQL (1 seule requête groupée)
            $pseudos = $this->getPseudosUtilisateurs($user_ids);

            // ÉTAPE 4 : Je fusionne MongoDB + MySQL (jointure manuelle PHP)
            $avis_formates = [];
            foreach ($avis_mongo as $avis) {
                // Je fais la jointure sur utilisateur_id
                $avis['nom_utilisateur'] = $pseudos[$avis['utilisateur_id']] ?? 'Utilisateur Inconnu';
                $avis_formates[] = $avis;
            }

            error_log("DEBUG: " . count($avis_formates) . " avis formatés avec pseudos MySQL");

            return [
                'success' => true,
                'avis' => $avis_formates,
                'total' => count($avis_formates)
            ];

        } catch (Exception $e) {
            // Je log technique détaillé pour debug développeur
            error_log('ERREUR getAvisParTrajet hybride: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Erreur lors de la récupération des avis'
            ];
        }
    }

    /**
     * MÉTHODE EXISTANTE : Je récupère TOUS les avis (pour l'administration)
     * 
     * UTILITÉ ADMIN :
     * - Gestion/modération de tous les avis de la plateforme
     * - Statistiques globales et rapports
     * - Surveillance de la qualité des avis
     * 
     * ARCHITECTURE HYBRIDE :
     * - Je récupère MongoDB (tous statuts confondus)
     * - J'enrichis avec pseudos MySQL
     * - Je trie chronologique pour faciliter modération
     * 
     * @param int $limit - Limite du nombre d'avis (défaut 100)
     * @return array - Tous les avis formatés avec pseudos
     */
    public function getTousLesAvis($limit = 100) 
    {
        try {
            // ÉTAPE 1 : Je récupère TOUS les avis MongoDB (incluant tous statuts pour admin)
            $filter = []; // Pas de filtre statut pour l'admin - il voit tout
            $options = [
                'sort' => ['date_creation' => -1], // Je mets plus récents en premier
                'limit' => $limit // Je pagine pour performance
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            // Je convertis curseur → tableau PHP
            $avis_mongo = [];
            foreach ($cursor as $avis) {
                $avis_mongo[] = [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id ?? '', // Je fallback si champ absent
                    'utilisateur_id' => $avis->utilisateur_id,
                    'nom_utilisateur' => $avis->nom_utilisateur ?? '', // Je cache du pseudo MongoDB
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s'),
                    'statut' => $avis->statut ?? 'en_attente' // Je fallback statut MODIFIÉ
                ];
            }

            // ÉTAPE 2 : J'extrais IDs utilisateur uniques
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            error_log("DEBUG getTousLesAvis: " . count($user_ids) . " utilisateurs uniques");

            // ÉTAPE 3 : J'enrichis pseudos MySQL (mise à jour cache)
            $pseudos = $this->getPseudosUtilisateurs($user_ids);

            // ÉTAPE 4 : Je fusionne et mets à jour cache pseudos
            $avis_formates = [];
            foreach ($avis_mongo as $avis) {
                // Je priorise pseudo MySQL > cache MongoDB > fallback
                $pseudo_mysql = $pseudos[$avis['utilisateur_id']] ?? null;
                $avis['nom_utilisateur'] = $pseudo_mysql ?: $avis['nom_utilisateur'] ?: 'Utilisateur Inconnu';
                
                $avis_formates[] = $avis;
            }

            error_log("DEBUG: " . count($avis_formates) . " avis admin récupérés");

            return [
                'success' => true,
                'avis' => $avis_formates,
                'total' => count($avis_formates)
            ];

        } catch (Exception $e) {
            error_log('ERREUR getTousLesAvis admin: ' . $e->getMessage());
            
            return [
                'success' => false,
                'avis' => [], // Je retourne vide en cas d'erreur
                'error' => 'Erreur lors de la récupération des avis'
            ];
        }
    }

    /**
     * MÉTHODE EXISTANTE : Je récupère les avis en attente de validation (pour l'admin)
     * 
     * UTILITÉ ADMIN :
     * - File d'attente de modération
     * - Priorisation des avis à valider
     * - Workflow de validation efficace
     * 
     * @param int $limit - Limite du nombre d'avis (défaut 50)
     * @return array - Avis en attente avec pseudos
     */
    public function getAvisEnAttente($limit = 50) 
    {
        try {
            // ÉTAPE 1 : Je récupère avis en attente MongoDB
            $filter = ['statut' => 'en_attente']; // Seulement ceux à valider
            $options = [
                'sort' => ['date_creation' => 1], // Je mets plus anciens en premier (FIFO)
                'limit' => $limit
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            // Je convertis curseur → tableau PHP
            $avis_mongo = [];
            foreach ($cursor as $avis) {
                $avis_mongo[] = [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id ?? '',
                    'utilisateur_id' => $avis->utilisateur_id,
                    'nom_utilisateur' => $avis->nom_utilisateur ?? '',
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s'),
                    'statut' => $avis->statut
                ];
            }

            // ÉTAPE 2 : J'enrichis pseudos MySQL
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            $pseudos = $this->getPseudosUtilisateurs($user_ids);

            // ÉTAPE 3 : Je fusionne avec pseudos à jour
            $avis_formates = [];
            foreach ($avis_mongo as $avis) {
                $pseudo_mysql = $pseudos[$avis['utilisateur_id']] ?? null;
                $avis['nom_utilisateur'] = $pseudo_mysql ?: $avis['nom_utilisateur'] ?: 'Utilisateur Inconnu';
                
                $avis_formates[] = $avis;
            }

            error_log("DEBUG: " . count($avis_formates) . " avis en attente de validation");

            return [
                'success' => true,
                'avis' => $avis_formates,
                'total' => count($avis_formates)
            ];

        } catch (Exception $e) {
            error_log('ERREUR getAvisEnAttente: ' . $e->getMessage());
            
            return [
                'success' => false,
                'avis' => [],
                'error' => 'Erreur lors de la récupération des avis en attente'
            ];
        }
    }

    /**
     * MÉTHODE EXISTANTE : Je valide un avis (le rend visible)
     * 
     * @param string $avis_id - ID de l'avis à valider
     * @return array - Résultat de l'opération
     */
    public function validerAvis($avis_id) 
    {
        return $this->modifierStatutAvis($avis_id, 'actif');
    }

    /**
     * MÉTHODE EXISTANTE : Je rejette un avis (le marque comme refusé)
     * 
     * @param string $avis_id - ID de l'avis à rejeter
     * @return array - Résultat de l'opération
     */
    public function rejeterAvis($avis_id) 
    {
        return $this->modifierStatutAvis($avis_id, 'refuse');
    }

    /**
     * Je récupère les pseudos utilisateurs depuis MySQL
     * 
     * OPTIMISATION REQUÊTE :
     * - Je requête IN() groupée au lieu de N requêtes séparées
     * - Je prépare PDO pour sécurité injection SQL
     * - Je gère les utilisateurs supprimés/introuvables
     * - Je connecte Docker MySQL avec paramètres adaptés
     * 
     * SÉCURITÉ :
     * - Je requête préparée PDO (protection injection SQL)
     * - Je valide les IDs en entrée
     * - Je fallback gracieux si utilisateur introuvable
     * 
     * @param array $user_ids - Liste des IDs utilisateur à récupérer
     * @return array - Tableau associatif [user_id => pseudo]
     */
    private function getPseudosUtilisateurs($user_ids) 
    {
        // Je valide l'entrée : si aucun ID, je retourne vide (évite requête inutile)
        if (empty($user_ids)) {
            return [];
        }
        
        try {
            // CONNEXION MYSQL DOCKER
            // Je paramètre adaptés à l'environnement Docker Compose
            $dsn = "mysql:host=mysql;dbname=ecoride;charset=utf8mb4";
            $username = "root"; // J'adapte selon ta config Docker
            $password = "root"; // J'adapte selon ta config Docker
            
            // Je configure PDO pour robustesse et sécurité
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Je lève exceptions pour erreurs
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Je récupère tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES => false // Je utilise vraies requêtes préparées
            ]);
            
            // REQUÊTE IN() OPTIMISÉE
            // Je construis des placeholders sécurisés pour IN()
            $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
            $sql = "SELECT id, pseudo, nom, prenom FROM utilisateurs WHERE id IN ($placeholders)";
            
            // J'exécute requête préparée (sécurité injection SQL)
            $stmt = $pdo->prepare($sql);
            $stmt->execute($user_ids);
            $users = $stmt->fetchAll();
            
            // CONSTRUCTION INDEX PERFORMANT
            // Je crée tableau associatif [user_id => pseudo] pour lookup O(1)
            $pseudos = [];
            foreach ($users as $user) {
                // Je logique de fallback pseudo : pseudo > nom+prénom > défaut
                $pseudos[$user['id']] = $user['pseudo'] ?? 
                                       trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: 
                                       'Utilisateur';
            }
            
            // Je log pour suivi performance (optionnel)
            error_log("DEBUG: " . count($pseudos) . " pseudos MySQL récupérés");
            
            return $pseudos;
            
        } catch (Exception $e) {
            // Je log erreur technique pour debug (critique pour fonctionnalité)
            error_log("ERREUR CRITIQUE getPseudosUtilisateurs MySQL: " . $e->getMessage());
            
            // Je retourne gracieux : array vide = fallback "Utilisateur" partout
            return [];
        }
    }

    // ========== AUTRES MÉTHODES MONGODB ==========

    /**
     * Je récupère un avis spécifique par son ID MongoDB
     * 
     * UTILITÉ : Affichage détail d'avis, modération, édition
     * SÉCURITÉ : Je valide ObjectId MongoDB pour éviter erreurs
     * 
     * @param string $id - ID MongoDB de l'avis (_id)
     * @return array|null - L'avis trouvé ou null si pas trouvé
     */
    public function getAvisParId($id) 
    {
        try {
            // Je valide format ObjectId MongoDB (évite exceptions)
            $filter = [
                '_id' => new MongoDB\BSON\ObjectId($id),
                'statut' => 'actif' // Seulement les avis validés
            ];
            $query = new MongoDB\Driver\Query($filter);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            $avisArray = iterator_to_array($cursor);
            
            if (!empty($avisArray)) {
                $avis = $avisArray[0]; // Je prends premier (et unique) résultat
                
                return [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id,
                    'utilisateur_id' => $avis->utilisateur_id,
                    'nom_utilisateur' => $avis->nom_utilisateur,
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s'),
                    'statut' => $avis->statut
                ];
            }
            
            return null; // Avis non trouvé
            
        } catch (Exception $e) {
            error_log("ERREUR getAvisParId MongoDB: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Je récupère tous les avis d'un utilisateur spécifique
     * 
     * UTILITÉ : Profile utilisateur, historique personnel
     * PERFORMANCE : Je index sur utilisateur_id + tri date optimisé
     * 
     * @param int $user_id - ID de l'utilisateur
     * @return array - Liste des avis de cet utilisateur
     */
    public function getAvisParUtilisateur($user_id) 
    {
        try {
            $filter = [
                'utilisateur_id' => (int)$user_id,
                'statut' => 'actif' // Seulement les avis validés
            ];
            
            // Je tri chronologique inverse (plus récents en premier)
            $options = ['sort' => ['date_creation' => -1]];
            $query = new MongoDB\Driver\Query($filter, $options);
            
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            $avis_utilisateur = [];
            foreach ($cursor as $avis) {
                $avis_utilisateur[] = [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id,
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s')
                ];
            }

            return [
                'success' => true,
                'avis' => $avis_utilisateur,
                'total' => count($avis_utilisateur)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Je calcule la note moyenne d'un trajet avec agrégation MongoDB
     * 
     * PERFORMANCE : J'utilise pipeline d'agrégation natif MongoDB
     * AVANTAGE : Je calcule côté base (plus rapide que PHP)
     * UTILITÉ : Affichage note trajet, classements, stats
     * 
     * @param int $trajet_id - ID du trajet
     * @return array - Moyenne et nombre total d'avis
     */
    public function calculerNoteMoyenne($trajet_id) 
    {
        try {
            // Je pipeline d'agrégation MongoDB (calcul côté base)
            $pipeline = [
                // Je filtre les avis pertinents (SEULEMENT LES VALIDÉS)
                ['$match' => ['trajet_id' => (int)$trajet_id, 'statut' => 'actif']],
                // J'agrège : moyenne + comptage
                ['$group' => [
                    '_id' => null,
                    'moyenne' => ['$avg' => '$note'], // Je fonction native MongoDB
                    'total_avis' => ['$sum' => 1]     // Je comptage documents
                ]]
            ];

            // J'exécute pipeline d'agrégation
            $command = new MongoDB\Driver\Command([
                'aggregate' => $this->collection,
                'pipeline' => $pipeline,
                'cursor' => new stdClass
            ]);
            
            $cursor = $this->manager->executeCommand($this->database, $command);
            $result = $cursor->toArray();
            
            if (!empty($result)) {
                return [
                    'success' => true,
                    'moyenne' => round($result[0]->moyenne, 1), // J'arrondis à 1 décimale
                    'total_avis' => $result[0]->total_avis
                ];
            }
            
            // Je cas aucun avis : retour cohérent
            return [
                'success' => true,
                'moyenne' => 0,
                'total_avis' => 0
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Je supprime (désactive) un avis - Soft Delete NoSQL
     * 
     * BONNE PRATIQUE NoSQL : Soft delete au lieu de suppression physique
     * AVANTAGES : Auditabilité, récupération possible, statistiques
     * SÉCURITÉ : Je valide ObjectId pour éviter erreurs
     * 
     * @param string $avis_id - ID MongoDB de l'avis
     * @return array - Résultat de l'opération
     */
    public function supprimerAvis($avis_id) 
    {
        try {
            // Je soft delete : changement de statut au lieu de suppression
            $filter = ['_id' => new MongoDB\BSON\ObjectId($avis_id)];
            $update = ['$set' => ['statut' => 'supprime']]; // Je nouveau statut
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection, $bulk);
            
            return [
                'success' => true,
                'message' => 'Avis supprimé avec succès'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * MÉTHODE EXISTANTE : Je modifie le statut d'un avis (pour l'administration)
     * 
     * UTILITÉ ADMIN :
     * - Je modère les avis (masquer, signaler, réactiver)
     * - Je gère les contenus inappropriés
     * - Je workflow de validation des avis
     * 
     * @param string $avis_id - ID MongoDB de l'avis
     * @param string $nouveau_statut - Nouveau statut (actif, masque, signale, supprime, en_attente, refuse)
     * @return array - Résultat de l'opération
     */
    public function modifierStatutAvis($avis_id, $nouveau_statut) 
    {
        try {
            // Je valide les statuts autorisés (AJOUT DE NOUVEAUX STATUTS)
            $statuts_valides = ['actif', 'masque', 'signale', 'supprime', 'en_attente', 'refuse'];
            if (!in_array($nouveau_statut, $statuts_valides)) {
                return [
                    'success' => false,
                    'error' => 'Statut invalide'
                ];
            }

            // Je mets à jour le statut
            $filter = ['_id' => new MongoDB\BSON\ObjectId($avis_id)];
            $update = [
                '$set' => [
                    'statut' => $nouveau_statut,
                    'date_modification' => new MongoDB\BSON\UTCDateTime()
                ]
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update($filter, $update);
            
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection, $bulk);
            
            if ($result->getModifiedCount() > 0) {
                return [
                    'success' => true,
                    'message' => "Statut mis à jour vers '$nouveau_statut'"
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Avis non trouvé ou statut inchangé'
                ];
            }

        } catch (Exception $e) {
            error_log("ERREUR modifierStatutAvis: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la modification du statut'
            ];
        }
    }
}

// ========== CLASSE AVIS POUR COMPATIBILITÉ VUE ==========

/**
 * Classe Avis pour objets compatibles avec la vue HTML Bootstrap
 * 
 * DESIGN PATTERN : Data Transfer Object (DTO)
 * UTILITÉ : Conversion données MongoDB → objets PHP → getters pour vue
 * COMPATIBILITÉ : Interface stable pour la vue même si MongoDB change
 */
class Avis 
{
    // Je propriétés publiques pour simplicité (PHP natif)
    public $trajet_id, $conducteur_id, $note_globale, $criteres, $commentaire, $tags, $date_creation, $statut, $pseudo, $_id, $user_id;

    /**
     * Constructeur - J'hydrate depuis tableau MongoDB
     * 
     * @param array $data - Données depuis MongoDB/MySQL fusion
     */
    public function __construct($data = []) 
    {
        // J'hydrate avec fallbacks sécurisés
        $this->_id = $data['_id'] ?? uniqid('avis_');
        $this->pseudo = $data['pseudo'] ?? null; 
        $this->user_id = $data['user_id'] ?? null;
        $this->trajet_id = $data['trajet_id'] ?? '';
        $this->conducteur_id = $data['conducteur_id'] ?? '';
        $this->note_globale = $data['note_globale'] ?? 0;
        $this->criteres = $data['criteres'] ?? [];
        $this->commentaire = $data['commentaire'] ?? '';
        $this->tags = $data['tags'] ?? [];
        $this->date_creation = $data['date_creation'] ?? date('Y-m-d H:i:s');
        $this->statut = $data['statut'] ?? 'en_attente'; // MODIFIÉ : Je défaut en_attente
    }

    // ========== GETTERS POUR VUE HTML ==========
    
    /** Je récupère l'ID unique de l'avis */
    public function getId() { return $this->_id; }
    
    /** Je récupère le pseudo utilisateur (depuis MySQL via jointure) */
    public function getPseudo() { return $this->pseudo ?? 'Utilisateur'; }
    
    /** Je récupère l'ID utilisateur pour référence */
    public function getUserId() { return $this->user_id; }
    
    /** Je récupère l'ID trajet */
    public function getTrajetId() { return $this->trajet_id; }
    
    /** Je récupère l'ID conducteur */
    public function getConducteurId() { return $this->conducteur_id; }
    
    /** Je récupère la note globale (1-5 étoiles) */
    public function getNoteGlobale() { return $this->note_globale; }
    
    /** Je récupère les critères détaillés */
    public function getCriteres() { return $this->criteres; }
    
    /** Je récupère le commentaire utilisateur */
    public function getCommentaire() { return $this->commentaire; }
    
    /** Je récupère les tags associés */
    public function getTags() { return $this->tags; }
    
    /** Je récupère la date de création formatée */
    public function getDateCreation() { return $this->date_creation; }
    
    /** Je récupère le statut de l'avis */
    public function getStatut() { return $this->statut; }
}
?>
