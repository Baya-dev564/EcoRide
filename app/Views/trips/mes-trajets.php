<?php
// app/Views/trips/mes-trajets.php
// Vue pour afficher les trajets proposés par l'utilisateur 


ob_start();

// Inclusion du fichier JavaScript spécifique
$jsFiles = ['/EcoRide/public/js/mes-trajets.js'];
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

<main class="container py-4" role="main">
    <!-- En-tête -->
    <header class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="text-success mb-2">
                        <i class="fas fa-route me-2" aria-hidden="true"></i>Mes trajets
                    </h1>
                    <p class="text-muted mb-0">Gérez vos trajets proposés sur EcoRide</p>
                </div>
                <nav>
                    <a href="/EcoRide/public/nouveau-trajet" class="btn btn-success">
                        <i class="fas fa-plus me-2" aria-hidden="true"></i>Proposer un trajet
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Statistiques rapides -->
    <section class="row mb-4" aria-labelledby="stats-titre">
        <div class="col-12">
            <h2 id="stats-titre" class="sr-only">Statistiques de vos trajets</h2>
        </div>
        
        <article class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-route fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-success mb-1"><?= count($trajets) ?></h3>
                    <p class="text-muted mb-0">Trajets proposés</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-users fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-primary mb-1">
                        <?= array_sum(array_column($trajets, 'nb_reservations')) ?>
                    </h3>
                    <p class="text-muted mb-0">Réservations reçues</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-coins fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-warning mb-1">
                        <?= array_sum(array_map(function($t) { return $t['prix'] * ($t['places_reservees'] ?? 0); }, $trajets)) ?>
                    </h3>
                    <p class="text-muted mb-0">Crédits gagnés</p>
                </div>
            </div>
        </article>
    </section>

    <!-- Liste des trajets -->
    <section aria-labelledby="liste-titre">
        <h2 id="liste-titre" class="sr-only">Liste de vos trajets proposés</h2>
        
        <?php if (empty($trajets)): ?>
            <!-- Aucun trajet -->
            <div class="row">
                <div class="col-12">
                    <article class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="text-muted mb-3">
                                <i class="fas fa-route fa-4x" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-muted mb-3">Aucun trajet proposé</h3>
                            <p class="text-muted mb-4">
                                Vous n'avez pas encore proposé de trajet sur EcoRide.<br>
                                Partagez vos trajets et gagnez des crédits !
                            </p>
                            <nav>
                                <a href="/EcoRide/public/nouveau-trajet" class="btn btn-success btn-lg">
                                    <i class="fas fa-plus me-2" aria-hidden="true"></i>Proposer mon premier trajet
                                </a>
                            </nav>
                        </div>
                    </article>
                </div>
            </div>
        <?php else: ?>
            <!-- Liste des trajets -->
            <div class="row">
                <?php foreach ($trajets as $trajet): ?>
                    <div class="col-12 mb-4">
                        <article class="card border-0 shadow-sm" aria-labelledby="trajet-<?= $trajet['id'] ?>">
                            <!-- En-tête de la carte -->
                            <header class="card-header bg-white d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?= $trajet['statut'] === 'ouvert' ? 'success' : ($trajet['statut'] === 'ferme' ? 'secondary' : 'warning') ?> me-2" role="img" aria-label="Statut : <?= ucfirst($trajet['statut']) ?>">
                                        <?= ucfirst($trajet['statut']) ?>
                                    </span>
                                    <?php if ($trajet['vehicule_electrique']): ?>
                                        <span class="badge bg-success me-2" role="img" aria-label="Véhicule électrique">
                                            🌱 Électrique
                                        </span>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        Créé le <time datetime="<?= $trajet['created_at'] ?>"><?= date('d/m/Y à H:i', strtotime($trajet['created_at'])) ?></time>
                                    </small>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="/EcoRide/public/trajet/<?= $trajet['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="Voir les détails du trajet">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <?php if ($trajet['statut'] === 'ouvert'): ?>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="modifierTrajet(<?= $trajet['id'] ?>)"
                                                aria-label="Modifier ce trajet">
                                            <i class="fas fa-edit" aria-hidden="true"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="annulerTrajet(<?= $trajet['id'] ?>)"
                                                aria-label="Annuler ce trajet">
                                            <i class="fas fa-times" aria-hidden="true"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <!-- Contenu de la carte -->
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Itinéraire -->
                                    <div class="col-md-4">
                                        <h3 id="trajet-<?= $trajet['id'] ?>" class="h6 mb-2">
                                            <span class="sr-only">Trajet de </span>
                                            <i class="fas fa-map-marker-alt text-success me-1" aria-hidden="true"></i>
                                            <?= htmlspecialchars($trajet['lieu_depart']) ?>
                                        </h3>
                                        <div class="text-center text-muted my-1">
                                            <i class="fas fa-arrow-down" aria-hidden="true"></i>
                                        </div>
                                        <div>
                                            <i class="fas fa-map-marker-alt text-danger me-1" aria-hidden="true"></i>
                                            <?= htmlspecialchars($trajet['lieu_arrivee']) ?>
                                        </div>
                                    </div>

                                    <!-- Date et heure -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-primary mb-1">
                                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                            </div>
                                            <div class="fw-bold">
                                                <time datetime="<?= $trajet['date_depart'] ?>">
                                                    <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?>
                                                </time>
                                            </div>
                                            <div class="text-muted">
                                                <time datetime="<?= $trajet['heure_depart'] ?>">
                                                    <?= date('H:i', strtotime($trajet['heure_depart'])) ?>
                                                </time>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Places et réservations -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-warning mb-1">
                                                <i class="fas fa-users" aria-hidden="true"></i>
                                            </div>
                                            <div class="fw-bold">
                                                <?= $trajet['places_reservees'] ?? 0 ?>/<?= $trajet['places'] ?>
                                            </div>
                                            <div class="text-muted">places</div>
                                        </div>
                                    </div>

                                    <!-- Prix -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-success mb-1">
                                                <i class="fas fa-coins" aria-hidden="true"></i>
                                            </div>
                                            <div class="fw-bold">
                                                <?= $trajet['prix'] ?> crédits
                                            </div>
                                            <div class="text-muted">par place</div>
                                        </div>
                                    </div>

                                    <!-- Véhicule -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="text-info mb-1">
                                                <i class="fas fa-car" aria-hidden="true"></i>
                                            </div>
                                            <?php if (!empty($trajet['vehicule_marque'])): ?>
                                                <div class="fw-bold small">
                                                    <?= htmlspecialchars($trajet['vehicule_marque']) ?>
                                                </div>
                                                <div class="text-muted small">
                                                    <?= htmlspecialchars($trajet['vehicule_modele']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted small">
                                                    Non spécifié
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Commentaire -->
                                <?php if (!empty($trajet['commentaire'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="bg-light p-3 rounded">
                                                <h4 class="h6 mb-2">
                                                    <i class="fas fa-comment me-1" aria-hidden="true"></i>
                                                    Commentaire
                                                </h4>
                                                <p class="mb-0 text-muted">
                                                    <?= nl2br(htmlspecialchars($trajet['commentaire'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Statistiques -->
                                <footer class="row mt-3">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="text-muted">
                                                    <i class="fas fa-eye me-1" aria-hidden="true"></i>
                                                    <?= $trajet['nb_reservations'] ?? 0 ?> réservation<?= ($trajet['nb_reservations'] ?? 0) > 1 ? 's' : '' ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-success fw-bold">
                                                    Gain : <?= ($trajet['prix'] * ($trajet['places_reservees'] ?? 0)) ?> crédits
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </footer>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
