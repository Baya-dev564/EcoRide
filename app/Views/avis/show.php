<?php
/**
 *  Affichage des avis d'un conducteur EcoRide
 * Interface pour consulter tous les avis d'un conducteur spécifique
 */

// Vérification que les variables sont définies
$pageTitle = $pageTitle ?? "Avis du conducteur - EcoRide";
$avis = $avis ?? [];
$note_moyenne = $note_moyenne ?? 0;
$conducteur_id = $conducteur_id ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS personnalisé pour les avis -->
    <link rel="stylesheet" href="/css/avis.css">
    
    <!-- CSS spécifique à la vue show -->
    <link rel="stylesheet" href="/css/show-avis.css">
</head>
<body>
    <!-- En-tête principal de la page -->
    <header class="bg-success text-white py-4 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-1">
                        <i class="fas fa-user-check text-warning me-2" aria-hidden="true"></i>
                        Profil du conducteur
                    </h1>
                    <p class="mb-0">
                        <i class="fas fa-id-badge me-1" aria-hidden="true"></i>
                        Conducteur #<?= htmlspecialchars($conducteur_id) ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <!-- Bouton retour -->
                    <a href="/ecoride/avis" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>
                        Retour aux avis
                    </a>
                    <!-- Bouton donner avis -->
                    <a href="/ecoride/avis/create?conducteur_id=<?= htmlspecialchars($conducteur_id) ?>" 
                       class="btn btn-warning btn-sm">
                        <i class="fas fa-plus me-1" aria-hidden="true"></i>
                        Donner un avis
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="container">
        <!-- Section des statistiques -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card-stats shadow-sm">
                    <div class="card-header bg-light">
                        <h2 class="h5 mb-0">
                            <i class="fas fa-chart-bar text-primary me-2" aria-hidden="true"></i>
                            Statistiques du conducteur
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <!-- Note moyenne -->
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-star text-warning"></i>
                                    </div>
                                    <div class="stat-value">
                                        <?= number_format($note_moyenne, 1) ?>/5
                                    </div>
                                    <div class="stat-label">Note moyenne</div>
                                    <div class="stars-display">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= round($note_moyenne) ? 'text-warning' : 'text-muted' ?>" 
                                               aria-hidden="true"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Nombre d'avis -->
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-comments text-primary"></i>
                                    </div>
                                    <div class="stat-value">
                                        <?= count($avis) ?>
                                    </div>
                                    <div class="stat-label">Avis reçus</div>
                                </div>
                            </div>
                            
                            <!-- Taux de satisfaction -->
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-thumbs-up text-success"></i>
                                    </div>
                                    <div class="stat-value">
                                        <?php
                                        $avis_positifs = array_filter($avis, function($avis_item) {
                                            return $avis_item->getNoteGlobale() >= 4;
                                        });
                                        $taux_satisfaction = count($avis) > 0 ? round((count($avis_positifs) / count($avis)) * 100) : 0;
                                        echo $taux_satisfaction;
                                        ?>%
                                    </div>
                                    <div class="stat-label">Satisfaction</div>
                                </div>
                            </div>
                            
                            <!-- Avis récents -->
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-clock text-info"></i>
                                    </div>
                                    <div class="stat-value">
                                        <?php
                                        $avis_recents = array_filter($avis, function($avis_item) {
                                            $date_avis = new DateTime($avis_item->getDateCreation());
                                            $date_limite = new DateTime('-30 days');
                                            return $date_avis > $date_limite;
                                        });
                                        echo count($avis_recents);
                                        ?>
                                    </div>
                                    <div class="stat-label">Ce mois-ci</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section répartition des notes -->
        <section class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-chart-pie text-info me-2" aria-hidden="true"></i>
                            Répartition des notes
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Calcul de la répartition des notes
                        $repartition = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                        foreach ($avis as $avis_item) {
                            $note = $avis_item->getNoteGlobale();
                            if (isset($repartition[$note])) {
                                $repartition[$note]++;
                            }
                        }
                        $total_avis = count($avis);
                        ?>
                        
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <?php
                            $count = $repartition[$i];
                            $percentage = $total_avis > 0 ? ($count / $total_avis) * 100 : 0;
                            ?>
                            <div class="rating-bar mb-2">
                                <div class="rating-label">
                                    <?= $i ?> <i class="fas fa-star text-warning"></i>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" 
                                         style="width: <?= $percentage ?>%"
                                         role="progressbar"
                                         aria-valuenow="<?= $percentage ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="rating-count">
                                    <?= $count ?> (<?= number_format($percentage, 1) ?>%)
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            
            <!-- Section critères moyens -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h6 mb-0">
                            <i class="fas fa-list-ul text-success me-2" aria-hidden="true"></i>
                            Critères moyens
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Calcul des moyennes par critère
                        $criteres_moyens = [
                            'ponctualite' => 0,
                            'conduite' => 0,
                            'proprete' => 0,
                            'ambiance' => 0
                        ];
                        
                        if (count($avis) > 0) {
                            foreach ($avis as $avis_item) {
                                $criteres = $avis_item->getCriteres();
                                foreach ($criteres_moyens as $critere => $moyenne) {
                                    if (isset($criteres[$critere])) {
                                        $criteres_moyens[$critere] += $criteres[$critere];
                                    }
                                }
                            }
                            
                            foreach ($criteres_moyens as $critere => $total) {
                                $criteres_moyens[$critere] = $total / count($avis);
                            }
                        }
                        
                        $criteres_labels = [
                            'ponctualite' => ['Ponctualité', 'fas fa-clock', 'primary'],
                            'conduite' => ['Conduite', 'fas fa-car', 'success'],
                            'proprete' => ['Propreté', 'fas fa-sparkles', 'warning'],
                            'ambiance' => ['Ambiance', 'fas fa-smile', 'danger']
                        ];
                        ?>
                        
                        <?php foreach ($criteres_labels as $critere => $info): ?>
                            <div class="critere-moyenne mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="critere-label">
                                        <i class="<?= $info[1] ?> text-<?= $info[2] ?> me-2" aria-hidden="true"></i>
                                        <?= $info[0] ?>
                                    </span>
                                    <span class="critere-note">
                                        <?= number_format($criteres_moyens[$critere], 1) ?>/5
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-<?= $info[2] ?>" 
                                         style="width: <?= ($criteres_moyens[$critere] / 5) * 100 ?>%"
                                         role="progressbar"
                                         aria-valuenow="<?= $criteres_moyens[$critere] ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="5">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section liste des avis -->
        <section class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="h5 mb-0">
                                <i class="fas fa-comments text-primary me-2" aria-hidden="true"></i>
                                Tous les avis (<?= count($avis) ?>)
                            </h3>
                            
                            <!-- Filtre rapide -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle btn-sm" 
                                        type="button" id="filterDropdown" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter me-1" aria-hidden="true"></i>
                                    Filtrer
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-filter="all">Tous les avis</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="5">5 étoiles</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="4">4 étoiles</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="3">3 étoiles</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="2">2 étoiles</a></li>
                                    <li><a class="dropdown-item" href="#" data-filter="1">1 étoile</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div id="avisContainer">
                            <?php if (empty($avis)): ?>
                                <!-- Message si aucun avis -->
                                <div class="alert alert-info text-center py-5">
                                    <i class="fas fa-info-circle fa-3x mb-3 text-muted" aria-hidden="true"></i>
                                    <h4 class="h5">Aucun avis disponible</h4>
                                    <p class="text-muted">Ce conducteur n'a pas encore reçu d'avis.</p>
                                    <a href="/ecoride/avis/create?conducteur_id=<?= htmlspecialchars($conducteur_id) ?>" 
                                       class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-1" aria-hidden="true"></i>
                                        Donner le premier avis
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Boucle pour afficher chaque avis -->
                                <?php foreach ($avis as $index => $avis_item): ?>
                                    <article class="avis-item mb-4" data-note="<?= $avis_item->getNoteGlobale() ?>">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Colonne gauche : Note et date -->
                                                    <div class="col-md-3">
                                                        <div class="text-center">
                                                            <!-- Note globale -->
                                                            <div class="note-display mb-2">
                                                                <span class="h4 text-primary">
                                                                    <?= $avis_item->getNoteGlobale() ?>
                                                                </span>
                                                                <span class="text-muted">/5</span>
                                                            </div>
                                                            
                                                            <!-- Étoiles -->
                                                            <div class="stars-small mb-2">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fas fa-star <?= $i <= $avis_item->getNoteGlobale() ? 'text-warning' : 'text-muted' ?>" 
                                                                       aria-hidden="true"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                            
                                                            <!-- Date -->
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar me-1" aria-hidden="true"></i>
                                                                <?= date('d/m/Y', strtotime($avis_item->getDateCreation())) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Colonne droite : Détails -->
                                                    <div class="col-md-9">
                                                        <!-- En-tête -->
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h4 class="h6 mb-1">
                                                                 <i class="fas fa-user text-secondary me-1" aria-hidden="true"></i>
                                                                  <?= htmlspecialchars($avis_item->pseudo ?? ('#' . $avis_item->user_id)) ?>
                                                                </h4>

                                                                <small class="text-muted">
                                                                    <i class="fas fa-route me-1" aria-hidden="true"></i>
                                                                    Trajet #<?= $avis_item->getTrajetId() ?>
                                                                </small>
                                                            </div>
                                                            
                                                            <!-- Statut -->
                                                            <span class="badge bg-<?= $avis_item->getStatut() === 'validé' ? 'success' : 'warning' ?>">
                                                                <?= ucfirst($avis_item->getStatut()) ?>
                                                            </span>
                                                        </div>
                                                        
                                                        <!-- Commentaire -->
                                                        <blockquote class="blockquote mb-3">
                                                            <p class="mb-0">
                                                                <?= htmlspecialchars($avis_item->getCommentaire()) ?>
                                                            </p>
                                                        </blockquote>
                                                        
                                                        <!-- Critères détaillés -->
                                                        <div class="row mb-3">
                                                            <?php foreach ($avis_item->getCriteres() as $critere => $note): ?>
                                                                <div class="col-6 col-sm-3 mb-2">
                                                                    <div class="critere-detail">
                                                                        <small class="text-muted d-block">
                                                                            <?= ucfirst($critere) ?>
                                                                        </small>
                                                                        <div class="stars-tiny">
                                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                                <i class="fas fa-star <?= $i <= $note ? 'text-warning' : 'text-muted' ?>" 
                                                                                   aria-hidden="true"></i>
                                                                            <?php endfor; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        
                                                        <!-- Tags -->
                                                        <?php if (!empty($avis_item->getTags())): ?>
                                                            <div class="tags">
                                                                <?php foreach ($avis_item->getTags() as $tag): ?>
                                                                    <span class="badge bg-light text-dark me-1">
                                                                        #<?= htmlspecialchars($tag) ?>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Pied de page -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">
                <i class="fas fa-leaf me-1 text-success" aria-hidden="true"></i>
                EcoRide - Plateforme de covoiturage écologique
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé pour les avis -->
    <script src="/js/avis.js"></script>
    
    <!-- JavaScript spécifique à la vue show -->
    <script src="/js/show-avis.js"></script>
</body>
</html>
