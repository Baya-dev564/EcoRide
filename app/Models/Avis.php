<?php
/**
 * Modèle Avis — EcoRide
 * Stockage NoSQL local dans un fichier JSON (avis_nosql.json)
 */

// je définis la classe Avis
class Avis {
    // Propriétés publiques (pour simplicité et compatibilité avec PHP natif)
    public $trajet_id, $conducteur_id, $note_globale, $criteres, $commentaire, $tags, $date_creation, $statut, $pseudo,$_id, $user_id;

    /**
     * Constructeur (crée une instance à partir d'un tableau associatif)
     */
    public function __construct($data = []) {
        $this->_id = $data['_id'] ?? uniqid('avis_');// Génère un ID unique si manquant
        $this->pseudo   = $data['pseudo'] ?? null; 
        $this->user_id = $data['user_id'] ?? null;
        $this->trajet_id = $data['trajet_id'] ?? '';
        $this->conducteur_id = $data['conducteur_id'] ?? '';
        $this->note_globale = $data['note_globale'] ?? 0;
        $this->criteres = $data['criteres'] ?? []; // tableau associatif
        $this->commentaire = $data['commentaire'] ?? '';
        $this->tags = $data['tags'] ?? [];
        $this->date_creation = $data['date_creation'] ?? date('Y-m-d H:i:s');
        $this->statut = $data['statut'] ?? 'validé';
    }

    /**
     * Enregistre l'avis courant dans le fichier JSON NoSQL
     */
    public function save() {
        $data_dir = __DIR__ . '/../../data';
        // Crée le dossier /data si inexistant
        if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

        $file = $data_dir . '/avis_nosql.json';
        // Récupération de tous les avis déjà stockés
        $avis = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
        // Ajout de l'avis courant (forme tableau pour l'encodage JSON)
        $avis[] = [
            '_id' => $this->_id,
            'user_id' => $this->user_id,
            'pseudo' => $this->pseudo,
            'trajet_id' => $this->trajet_id,
            'conducteur_id' => $this->conducteur_id,
            'note_globale' => $this->note_globale,
            'criteres' => $this->criteres,
            'commentaire' => $this->commentaire,
            'tags' => $this->tags,
            'date_creation' => $this->date_creation,
            'statut' => $this->statut
        ];
        // Écriture dans le JSON (format lisible)
        return file_put_contents($file, json_encode($avis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }

    /**
     * Récupère et retourne la totalité des avis depuis le fichier JSON
     * @return array <Avis>
     */
    public static function getAll() {
        $file = __DIR__ . '/../../data/avis_nosql.json';
        if (!file_exists($file)) return [];
        $avis = json_decode(file_get_contents($file), true) ?? [];
        //  crée un tableau d'objets Avis à partir des données JSON brutes
        return array_map(fn($d) => new Avis($d), $avis);
    }

    /**
     * Récupère uniquement les avis pour un conducteur donné
     * @param $conducteur_id string|int
     * @return array <Avis>
     */
    public static function getByConducteur($conducteur_id) {
        return array_filter(self::getAll(), function($avis) use ($conducteur_id) {
            return $avis->getConducteurId() == $conducteur_id;
        });
    }

    // **GETTERS** utilisés par les vues
    public function getNoteGlobale()   { return $this->note_globale; }
    public function getCriteres()      { return $this->criteres; }
    public function getCommentaire()   { return $this->commentaire; }
    public function getTags()          { return $this->tags; }
    public function getConducteurId()  { return $this->conducteur_id; }
    public function getTrajetId()      { return $this->trajet_id; }
    public function getDateCreation()  { return $this->date_creation; }
    public function getStatut()        { return $this->statut; }
    public function getUserId()        { return $this->user_id; }
    public function getPseudo()        { return $this->pseudo; }  
}
?>
