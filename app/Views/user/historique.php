<?php
/**
 * app/Views/user/historique.php
 * Vue pour l'historique complet des activités de l'utilisateur EcoRide
 * Affiche les trajets proposés terminés et les réservations effectuées
 */

ob_start();
?>

<!-- Messages d'alerte -->
<?php if (!empty($message)): ?>
    <aside class="container mt-3" role="alert" aria-live="polite">
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                <div><?= htmlspecialchars($message) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer le message"></button>
        </div>
    </aside>
<?php endif; ?>

<main class="container py-4 historique-page" role="main">
    <!-- En-tête de la page -->
    <header class="row mb-4">
        <div class="col-12">
            <nav aria-label="Fil d'Ariane">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/EcoRide/public/">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="/EcoRide/public/profil">Mon profil</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Historique</li>
                </ol>
            </nav>
            <h1 class="text-success mb-2">
                <i class="fas fa-history me-2" aria-hidden="true"></i>Mon historique EcoRide
            </h1>
            <p class="text-muted">Retrouvez tous vos trajets et réservations terminés</p>
        </div>
    </header>

    <!-- Statistiques globales -->
    <section class="row mb-4" aria-labelledby="stats-titre">
        <div class="col-12">
            <h2 id="stats-titre" class="sr-only">Statistiques globales</h2>
        </div>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon text-success mb-2">
                        <i class="fas fa-route fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="stat-value text-success mb-1"><?= $stats['total_trajets_proposes'] ?></h3>
                    <p class="stat-label text-muted mb-0">Trajets proposés</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon text-primary mb-2">
                        <i class="fas fa-user-check fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="stat-value text-primary mb-1"><?= $stats['total_reservations'] ?></h3>
                    <p class="stat-label text-muted mb-0">Réservations effectuées</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm stat-card">
                <div class="card-body text-center">
                    <div class="stat-icon text-warning mb-2">
                        <i class="fas fa-coins fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="stat-value text-warning mb-1">
                        <?= ($stats['credits_gagnes'] - $stats['credits_depenses']) ?>
                    </h3>
                    <p class="stat-label text-muted mb-0">Crédits nets</p>
                    <small class="text-muted">
                        <i class="fas fa-arrow-up text-success"></i> <?= $stats['credits_gagnes'] ?>
                        <i class="fas fa-arrow-down text-danger ms-2"></i> <?= $stats['credits_depenses'] ?>
                    </small>
                </div>
            </div>
        </article>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm stat-card stat-eco">
                <div class="card-body text-center">
                    <div class="stat-icon text-success mb-2">
                        <i class="fas fa-leaf fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="stat-value text-success mb-1"><?= $stats['co2_economise'] ?> kg</h3>
                    <p class="stat-label text-muted mb-0">CO₂ économisé</p>
                    <small class="text-muted"><?= round($stats['total_km_conduits'] + $stats['total_km_voyages']) ?> km partagés</small>
                </div>
            </div>
        </article>
    </section>

    <!-- Onglets pour trajets proposés / réservations -->
    <section aria-labelledby="historique-titre">
        <h2 id="historique-titre" class="sr-only">Détail de votre historique</h2>
        
        <ul class="nav nav-tabs mb-4" id="historiqueTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" 
                        id="trajets-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#trajets" 
                        type="button" 
                        role="tab" 
                        aria-controls="trajets" 
                        aria-selected="true">
                    <i class="fas fa-car me-2" aria-hidden="true"></i>
                    Trajets proposés (<?= count($trajetsProposesTermines) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" 
                        id="reservations-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#reservations" 
                        type="button" 
                        role="tab" 
                        aria-controls="reservations" 
                        aria-selected="false">
                    <i class="fas fa-user-friends me-2" aria-hidden="true"></i>
                    Réservations effectuées (<?= count($reservationsTerminees) ?>)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="historiqueTabContent">
            <!-- Onglet Trajets proposés -->
            <div class="tab-pane fade show active" 
                 id="trajets" 
                 role="tabpanel" 
                 aria-labelledby="trajets-tab">
                
                <?php if (empty($trajetsProposesTermines)): ?>
                    <article class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5 empty-state">
                            <div class="text-muted mb-3">
                                <i class="fas fa-route fa-4x" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-muted mb-3">Aucun trajet terminé</h3>
                            <p class="text-muted mb-4">
                                Vous n'avez pas encore terminé de trajet en tant que conducteur.
                            </p>
                            <a href="/EcoRide/public/nouveau-trajet" class="btn btn-success">
                                <i class="fas fa-plus me-2" aria-hidden="true"></i>Proposer un trajet
                            </a>
                        </div>
                    </article>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($trajetsProposesTermines as $trajet): ?>
                            <div class="col-12 mb-3">
                                <article class="card border-0 shadow-sm trajet-card">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <!-- Itinéraire -->
                                            <div class="col-md-4">
                                                <div class="trajet-itineraire">
                                                    <div class="lieu-depart mb-2">
                                                        <i class="fas fa-map-marker-alt text-success me-2" aria-hidden="true"></i>
                                                        <strong><?= htmlspecialchars($trajet['lieu_depart']) ?></strong>
                                                    </div>
                                                    <div class="text-center text-muted my-2">
                                                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                                                        <span class="mx-2"><?= $trajet['distance_estimee'] ?></span>
                                                    </div>
                                                    <div class="lieu-arrivee">
                                                        <i class="fas fa-flag-checkered text-danger me-2" aria-hidden="true"></i>
                                                        <strong><?= htmlspecialchars($trajet['lieu_arrivee']) ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Date et infos -->
                                            <div class="col-md-3 text-center">
                                                <div class="mb-2">
                                                    <i class="fas fa-calendar text-primary me-1" aria-hidden="true"></i>
                                                    <time datetime="<?= $trajet['date_depart'] ?>">
                                                        <?= $trajet['date_depart_formatee'] ?>
                                                    </time>
                                                </div>
                                                <?php if (!empty($trajet['vehicule_marque'])): ?>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-car me-1" aria-hidden="true"></i>
                                                        <?= htmlspecialchars($trajet['vehicule_marque']) ?> 
                                                        <?= htmlspecialchars($trajet['vehicule_modele']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Statistiques -->
                                            <div class="col-md-2 text-center">
                                                <div class="mb-2">
                                                    <i class="fas fa-users text-warning me-1" aria-hidden="true"></i>
                                                    <strong><?= $trajet['nb_passagers'] ?></strong> passager<?= $trajet['nb_passagers'] > 1 ? 's' : '' ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <?= $trajet['total_places_reservees'] ?> place<?= $trajet['total_places_reservees'] > 1 ? 's' : '' ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Crédits gagnés -->
                                            <div class="col-md-3 text-center">
                                                <div class="credits-gagnes">
                                                    <i class="fas fa-coins text-warning me-1" aria-hidden="true"></i>
                                                    <strong class="text-success">+<?= $trajet['credits_gagnes'] ?></strong> crédits
                                                </div>
                                                <span class="badge bg-success mt-2">
                                                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Terminé
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Onglet Réservations effectuées -->
            <div class="tab-pane fade" 
                 id="reservations" 
                 role="tabpanel" 
                 aria-labelledby="reservations-tab">
                
                <?php if (empty($reservationsTerminees)): ?>
                    <article class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5 empty-state">
                            <div class="text-muted mb-3">
                                <i class="fas fa-user-friends fa-4x" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-muted mb-3">Aucune réservation terminée</h3>
                            <p class="text-muted mb-4">
                                Vous n'avez pas encore effectué de réservation terminée.
                            </p>
                            <a href="/EcoRide/public/" class="btn btn-primary">
                                <i class="fas fa-search me-2" aria-hidden="true"></i>Rechercher un trajet
                            </a>
                        </div>
                    </article>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($reservationsTerminees as $reservation): ?>
                            <div class="col-12 mb-3">
                                <article class="card border-0 shadow-sm reservation-card">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <!-- Itinéraire -->
                                            <div class="col-md-4">
                                                <div class="trajet-itineraire">
                                                    <div class="lieu-depart mb-2">
                                                        <i class="fas fa-map-marker-alt text-success me-2" aria-hidden="true"></i>
                                                        <strong><?= htmlspecialchars($reservation['lieu_depart']) ?></strong>
                                                    </div>
                                                    <div class="text-center text-muted my-2">
                                                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                                                        <span class="mx-2"><?= $reservation['distance_estimee'] ?></span>
                                                    </div>
                                                    <div class="lieu-arrivee">
                                                        <i class="fas fa-flag-checkered text-danger me-2" aria-hidden="true"></i>
                                                        <strong><?= htmlspecialchars($reservation['lieu_arrivee']) ?></strong>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Date et conducteur -->
                                            <div class="col-md-3">
                                                <div class="mb-2">
                                                    <i class="fas fa-calendar text-primary me-1" aria-hidden="true"></i>
                                                    <time datetime="<?= $reservation['date_depart'] ?>">
                                                        <?= $reservation['date_depart_formatee'] ?>
                                                    </time>
                                                </div>
                                                <div class="conducteur-info">
                                                    <i class="fas fa-user text-muted me-1" aria-hidden="true"></i>
                                                    <span class="text-muted">Avec</span>
                                                    <strong><?= htmlspecialchars($reservation['conducteur_pseudo']) ?></strong>
                                                </div>
                                            </div>
                                            
                                            <!-- Places réservées -->
                                            <div class="col-md-2 text-center">
                                                <div class="mb-2">
                                                    <i class="fas fa-chair text-info me-1" aria-hidden="true"></i>
                                                    <strong><?= $reservation['nb_places'] ?></strong> place<?= $reservation['nb_places'] > 1 ? 's' : '' ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Crédits dépensés -->
                                            <div class="col-md-3 text-center">
                                                <div class="credits-depenses">
                                                    <i class="fas fa-coins text-warning me-1" aria-hidden="true"></i>
                                                    <strong class="text-danger">-<?= $reservation['credits_depenses'] ?></strong> crédits
                                                </div>
                                                <span class="badge bg-success mt-2">
                                                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Terminé
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Impact écologique global -->
    <aside class="row mt-5">
        <div class="col-12">
            <article class="card border-0 shadow-sm impact-eco-card">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-leaf me-2" aria-hidden="true"></i>Votre impact écologique
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="impact-item">
                                <i class="fas fa-route fa-2x text-success mb-2" aria-hidden="true"></i>
                                <h3 class="h2 text-success mb-1">
                                    <?= round($stats['total_km_conduits'] + $stats['total_km_voyages']) ?>
                                </h3>
                                <p class="text-muted mb-0">kilomètres partagés</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="impact-item">
                                <i class="fas fa-cloud fa-2x text-success mb-2" aria-hidden="true"></i>
                                <h3 class="h2 text-success mb-1"><?= $stats['co2_economise'] ?> kg</h3>
                                <p class="text-muted mb-0">de CO₂ économisé</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="impact-item">
                                <i class="fas fa-gas-pump fa-2x text-success mb-2" aria-hidden="true"></i>
                                <h3 class="h2 text-success mb-1"><?= $stats['carburant_economise'] ?> L</h3>
                                <p class="text-muted mb-0">de carburant économisé</p>
                            </div>
                        </div>
                    </div>
                    <footer class="text-center mt-4">
                        <p class="text-muted mb-0">
                            <i class="fas fa-heart text-danger me-1" aria-hidden="true"></i>
                            Merci de contribuer à une mobilité plus durable !
                        </p>
                    </footer>
                </div>
            </article>
        </div>
    </aside>
</main>

<?php
$content = ob_get_clean();
$cssFiles = ['css/historique.css'];
$jsFiles = [];

require __DIR__ . '/../layouts/main.php';
?>
