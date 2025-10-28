<?php
/**
 * Gestion des avis MongoDB - Version .env
 *  Compatible Docker local ET production Hostinger via .env
 */

// Je charge les variables d'environnement


class AvisMongo
{
    private $manager;
    private $database = 'EcoRide';
    private $collection = 'avis';
    
    public function __construct()
    {
        try {
            // Je récupère l'URI MongoDB depuis les variables d'environnement
            $mongoUri = getenv('MONGODB_URI');
            
            if (!$mongoUri) {
                throw new Exception('MONGODB_URI non définie dans le fichier .env');
            }
            
            $this->manager = new MongoDB\Driver\Manager($mongoUri);
            error_log("DEBUG: Connexion MongoDB établie avec succès");
            
        } catch (Exception $e) {
            error_log('ERREUR CRITIQUE - Connexion MongoDB échouée: ' . $e->getMessage());
            die('Erreur de connexion à MongoDB. Vérifiez votre configuration .env');
        }
    }
    
    /**
     * J'ajoute un nouvel avis dans MongoDB avec système de modération
     */
    public function ajouterAvis($donnees)
    {
        try {
            $avis = [
                'trajet_id' => (int)$donnees['trajet_id'],
                'utilisateur_id' => (int)$donnees['utilisateur_id'],
                'nom_utilisateur' => $donnees['nom_utilisateur'],
                'note' => (int)$donnees['note'],
                'commentaire' => $donnees['commentaire'],
                'date_creation' => new MongoDB\BSON\UTCDateTime(),
                'statut' => 'en_attente'
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $id = $bulk->insert($avis);
            $result = $this->manager->executeBulkWrite($this->database . '.' . $this->collection, $bulk);
            
            error_log("DEBUG: Avis ajouté en attente - ID: " . (string)$id);
            
            return [
                'success' => true,
                'avis_id' => (string)$id,
                'message' => 'Avis soumis avec succès. Il sera visible après validation par l\'équipe.'
            ];
            
        } catch (Exception $e) {
            error_log("ERREUR ajouterAvis MongoDB: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur technique lors de l\'ajout de l\'avis'
            ];
        }
    }
    
    /**
     * Je récupère tous les avis validés pour la page publique utilisateurs
     */
    public function getTousLesAvisValidés()
    {
        try {
            $filter = ['statut' => 'actif'];
            $options = ['sort' => ['date_creation' => -1]];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
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
            
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            $pseudos = $this->getPseudosUtilisateurs($user_ids);
            
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
     */
    public function getAvisParTrajet($trajet_id)
    {
        try {
            if ($trajet_id && $trajet_id !== '') {
                $filter = [
                    'trajet_id' => (int)$trajet_id,
                    'statut' => 'actif'
                ];
                error_log("DEBUG getAvisParTrajet: Filtre trajet_id = $trajet_id");
            } else {
                $filter = ['statut' => 'actif'];
                error_log("DEBUG getAvisParTrajet: Tous les avis actifs (pas de trajet spécifique)");
            }
            
            $options = ['sort' => ['date_creation' => -1]];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            $avis_mongo = [];
            foreach ($cursor as $avis) {
                $avis_mongo[] = [
                    'id' => (string)$avis->_id,
                    'trajet_id' => $avis->trajet_id,
                    'utilisateur_id' => $avis->utilisateur_id,
                    'note' => $avis->note,
                    'commentaire' => $avis->commentaire,
                    'date_creation' => $avis->date_creation->toDateTime()->format('Y-m-d H:i:s')
                ];
            }
            
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            error_log("DEBUG: IDs utilisateur à récupérer: " . count($user_ids));
            
            $pseudos = $this->getPseudosUtilisateurs($user_ids);
            
            $avis_formates = [];
            foreach ($avis_mongo as $avis) {
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
            error_log('ERREUR getAvisParTrajet hybride: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erreur lors de la récupération des avis'
            ];
        }
    }
    
    /**
     * Je récupère tous les avis pour l'administration
     */
    public function getTousLesAvis($limit = 100)
    {
        try {
            $filter = [];
            $options = [
                'sort' => ['date_creation' => -1],
                'limit' => $limit
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
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
                    'statut' => $avis->statut ?? 'en_attente'
                ];
            }
            
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            error_log("DEBUG getTousLesAvis: " . count($user_ids) . " utilisateurs uniques");
            
            $pseudos = $this->getPseudosUtilisateurs($user_ids);
            
            $avis_formates = [];
            foreach ($avis_mongo as $avis) {
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
                'avis' => [],
                'error' => 'Erreur lors de la récupération des avis'
            ];
        }
    }
    
    /**
     * Je récupère les avis en attente de validation pour l'admin
     */
    public function getAvisEnAttente($limit = 50)
    {
        try {
            $filter = ['statut' => 'en_attente'];
            $options = [
                'sort' => ['date_creation' => 1],
                'limit' => $limit
            ];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
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
            
            $user_ids = array_unique(array_column($avis_mongo, 'utilisateur_id'));
            $pseudos = $this->getPseudosUtilisateurs($user_ids);
            
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
     * Je valide un avis (le rend visible)
     */
    public function validerAvis($avis_id)
    {
        return $this->modifierStatutAvis($avis_id, 'actif');
    }
    
    /**
     * Je rejette un avis (le marque comme refusé)
     */
    public function rejeterAvis($avis_id)
    {
        return $this->modifierStatutAvis($avis_id, 'refuse');
    }
    
    /**
     * Je récupère les pseudos utilisateurs depuis MySQL via .env
     */
    private function getPseudosUtilisateurs($user_ids)
    {
        if (empty($user_ids)) {
            return [];
        }
        
        try {
            // Je récupère les credentials MySQL depuis les variables d'environnement
            $host = getenv('MYSQL_HOST') ?: 'localhost';
            $database = getenv('MYSQL_DATABASE') ?: 'EcoRide';
            $username = getenv('MYSQL_USER');
            $password = getenv('MYSQL_PASSWORD');
            $port = getenv('MYSQL_PORT') ?: 3306;
            
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
            $sql = "SELECT id, pseudo, nom, prenom FROM utilisateurs WHERE id IN ($placeholders)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($user_ids);
            $users = $stmt->fetchAll();
            
            $pseudos = [];
            foreach ($users as $user) {
                $pseudos[$user['id']] = $user['pseudo'] ??
                    trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?:
                    'Utilisateur';
            }
            
            error_log("DEBUG: " . count($pseudos) . " pseudos MySQL récupérés via .env");
            return $pseudos;
            
        } catch (Exception $e) {
            error_log("ERREUR CRITIQUE getPseudosUtilisateurs MySQL: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Je récupère un avis spécifique par son ID MongoDB
     */
    public function getAvisParId($id)
    {
        try {
            $filter = [
                '_id' => new MongoDB\BSON\ObjectId($id),
                'statut' => 'actif'
            ];
            $query = new MongoDB\Driver\Query($filter);
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
            $avisArray = iterator_to_array($cursor);
            if (!empty($avisArray)) {
                $avis = $avisArray[0];
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
            
            return null;
        } catch (Exception $e) {
            error_log("ERREUR getAvisParId MongoDB: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Je récupère tous les avis avec filtres pour l'administration
     */
    public function obtenirTousLesAvis($filtres = [])
    {
        try {
            $filter = [];
            if (!empty($filtres['statut'])) {
                $filter['statut'] = $filtres['statut'];
            }
            
            if (!empty($filtres['note'])) {
                $filter['note'] = (int)$filtres['note'];
            }
            
            $options = ['sort' => ['date_creation' => -1]];
            $query = new MongoDB\Driver\Query($filter, $options);
            $cursor = $this->manager->executeQuery($this->database . '.' . $this->collection, $query);
            
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
                    'statut' => $avis->statut ?? 'en_attente'
                ];
            }
            
            return $avis_mongo;
        } catch (Exception $e) {
            error_log("Erreur obtenirTousLesAvis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Je récupère tous les avis d'un utilisateur spécifique
     */
    public function getAvisParUtilisateur($user_id)
    {
        try {
            $filter = [
                'utilisateur_id' => (int)$user_id,
                'statut' => 'actif'
            ];
            
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
     */
    public function calculerNoteMoyenne($trajet_id)
    {
        try {
            $pipeline = [
                ['$match' => ['trajet_id' => (int)$trajet_id, 'statut' => 'actif']],
                ['$group' => [
                    '_id' => null,
                    'moyenne' => ['$avg' => '$note'],
                    'total_avis' => ['$sum' => 1]
                ]]
            ];
            
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
                    'moyenne' => round($result[0]->moyenne, 1),
                    'total_avis' => $result[0]->total_avis
                ];
            }
            
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
     */
    public function supprimerAvis($avis_id)
    {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($avis_id)];
            $update = ['$set' => ['statut' => 'supprime']];
            
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
     * Je modifie le statut d'un avis pour l'administration
     */
    public function modifierStatutAvis($avis_id, $nouveau_statut)
    {
        try {
            $statuts_valides = ['actif', 'masque', 'signale', 'supprime', 'en_attente', 'refuse'];
            if (!in_array($nouveau_statut, $statuts_valides)) {
                return [
                    'success' => false,
                    'error' => 'Statut invalide'
                ];
            }
            
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

/**
 * Classe Avis pour objets compatibles avec la vue HTML Bootstrap
 */
class Avis
{
    public $trajet_id, $conducteur_id, $note_globale, $criteres, $commentaire, $tags, $date_creation, $statut, $pseudo, $_id, $user_id;
    
    public function __construct($data = [])
    {
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
        $this->statut = $data['statut'] ?? 'en_attente';
    }
    
    // Getters pour vue HTML
    public function getId() { return $this->_id; }
    public function getPseudo() { return $this->pseudo ?? 'Utilisateur'; }
    public function getUserId() { return $this->user_id; }
    public function getTrajetId() { return $this->trajet_id; }
    public function getConducteurId() { return $this->conducteur_id; }
    public function getNoteGlobale() { return $this->note_globale; }
    public function getCriteres() { return $this->criteres; }
    public function getCommentaire() { return $this->commentaire; }
    public function getTags() { return $this->tags; }
    public function getDateCreation() { return $this->date_creation; }
    public function getStatut() { return $this->statut; }
}
?>
