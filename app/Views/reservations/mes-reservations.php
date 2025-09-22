<?php
// Vue pour afficher les réservations de l'utilisateur 

ob_start();

$jsFiles = ['js/mes-reservations.js'];
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

<?php if (!empty($erreur)): ?>
    <aside class="container mt-3" role="alert" aria-live="assertive">
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                <div><?= htmlspecialchars($erreur) ?></div>
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
                        <i class="fas fa-calendar-check me-2" aria-hidden="true"></i>Mes réservations
                    </h1>
                    <p class="text-muted mb-0">
                        Gérez vos réservations de trajets EcoRide. 
                        Crédit disponible : <strong><?= $user['credit'] ?> crédits</strong>
                    </p>
                </div>
                <nav>
                    <a href="/trajets" class="btn btn-success">
                        <i class="fas fa-search me-2" aria-hidden="true"></i>Rechercher un trajet
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Statistiques rapides -->
    <section class="row mb-4" aria-labelledby="stats-titre">
        <div class="col-12">
            <h2 id="stats-titre" class="visually-hidden">Statistiques de vos réservations</h2>
        </div>
        <article class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-calendar-check fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-success mb-1"><?= count($reservations) ?></h3>
                    <p class="text-muted mb-0">Réservations totales</p>
                </div>
            </div>
        </article>
        <article class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-check-circle fa-2x" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-primary mb-1">
                        <?= count(array_filter($reservations, function($r) { return $r['statut'] === 'confirme'; })) ?>
                    </h3>
                    <p class="text-muted mb-0">Confirmées</p>
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
                        <?= array_sum(array_column($reservations, 'credits_utilises')) ?>
                    </h3>
                    <p class="text-muted mb-0">Crédits dépensés</p>
                </div>
            </div>
        </article>
    </section>

    <!-- Liste des réservations -->
    <section aria-labelledby="liste-titre">
        <h2 id="liste-titre" class="visually-hidden">Liste de vos réservations</h2>
        
        <?php if (empty($reservations)): ?>
            <!-- Aucune réservation -->
            <div class="row">
                <div class="col-12">
                    <article class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="text-muted mb-3">
                                <i class="fas fa-calendar-times fa-4x" aria-hidden="true"></i>
                            </div>
                            <h3 class="text-muted mb-3">Aucune réservation</h3>
                            <p class="text-muted mb-4">
                                Vous n'avez pas encore réservé de trajet sur EcoRide.<br>
                                Trouvez le trajet parfait pour vos déplacements !
                            </p>
                            <nav>
                                <a href="/trajets" class="btn btn-success btn-lg">
                                    <i class="fas fa-search me-2" aria-hidden="true"></i>Rechercher un trajet
                                </a>
                            </nav>
                        </div>
                    </article>
                </div>
            </div>
        <?php else: ?>
            <!-- Liste des réservations -->
            <div class="row">
                <?php foreach ($reservations as $reservation): ?>
                    <div class="col-12 mb-4">
                        <article class="card border-0 shadow-sm" aria-labelledby="reservation-<?= $reservation['id'] ?>">
                            <!-- En-tête de la carte -->
                            <header class="card-header bg-white d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?= $reservation['statut'] === 'confirme' ? 'success' : ($reservation['statut'] === 'annule' ? 'danger' : ($reservation['statut'] === 'termine' ? 'primary' : 'warning')) ?> me-2" role="img" aria-label="Statut : <?= ucfirst($reservation['statut']) ?>">
                                        <?= ucfirst($reservation['statut']) ?>
                                    </span>
                                    <small class="text-muted">
                                        Réservé le <time datetime="<?= $reservation['date_reservation'] ?>"><?= date('d/m/Y à H:i', strtotime($reservation['date_reservation'])) ?></time>
                                    </small>
                                </div>
                                <?php if ($reservation['statut'] === 'confirme'): ?>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            data-reservation-id="<?= $reservation['id'] ?>"
                                            aria-label="Annuler cette réservation">
                                        <i class="fas fa-times me-1" aria-hidden="true"></i>Annuler
                                    </button>
                                <?php endif; ?>
                            </header>

                            <!-- Contenu de la carte -->
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Itinéraire -->
                                    <div class="col-md-4">
                                        <h3 id="reservation-<?= $reservation['id'] ?>" class="h6 mb-2">
                                            <span class="visually-hidden">Trajet de </span>
                                            <i class="fas fa-map-marker-alt text-success me-1" aria-hidden="true"></i>
                                            <?= htmlspecialchars($reservation['lieu_depart']) ?>
                                        </h3>
                                        <div class="text-center text-muted my-1">
                                            <i class="fas fa-arrow-down" aria-hidden="true"></i>
                                        </div>
                                        <div>
                                            <i class="fas fa-map-marker-alt text-danger me-1" aria-hidden="true"></i>
                                            <?= htmlspecialchars($reservation['lieu_arrivee']) ?>
                                        </div>
                                    </div>
                                    <!-- Informations du trajet -->
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="text-primary mb-1">
                                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                            </div>
                                            <div class="fw-bold">
                                                <time datetime="<?= $reservation['date_depart'] ?>">
                                                    <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?>
                                                </time>
                                            </div>
                                            <div class="text-muted">
                                                <time datetime="<?= $reservation['date_depart'] ?>">
                                                    <?= date('H:i', strtotime($reservation['date_depart'])) ?>
                                                </time>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Détails de la réservation -->
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="text-warning mb-1">
                                                <i class="fas fa-users" aria-hidden="true"></i>
                                            </div>
                                            <div class="fw-bold">
                                                <?= $reservation['nb_places'] ?> place<?= $reservation['nb_places'] > 1 ? 's' : '' ?>
                                            </div>
                                            <div class="text-muted">
                                                <?= $reservation['credits_utilises'] ?> crédits
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Conducteur -->
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-1" 
                                                 style="width: 40px; height: 40px;"
                                                 role="img"
                                                 aria-label="Avatar du conducteur <?= htmlspecialchars($reservation['conducteur_pseudo']) ?>">
                                                <i class="fas fa-user text-white" aria-hidden="true"></i>
                                            </div>
                                            <div class="fw-bold small">
                                                <?= htmlspecialchars($reservation['conducteur_pseudo']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message du passager -->
                                <?php if (!empty($reservation['message_passager'])): ?>
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-light">
                                                <h4 class="alert-heading small">
                                                    <i class="fas fa-comment me-1" aria-hidden="true"></i>
                                                    Votre message au conducteur :
                                                </h4>
                                                <p class="mb-0 small">
                                                    <?= nl2br(htmlspecialchars($reservation['message_passager'])) ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Statistiques et liens -->
                                <footer class="row mt-3">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?php if ($reservation['statut'] === 'confirme'): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Réservation confirmée
                                                    </span>
                                                <?php elseif ($reservation['statut'] === 'annule'): ?>
                                                    <span class="text-danger">
                                                        <i class="fas fa-times-circle me-1" aria-hidden="true"></i>Réservation annulée
                                                        <?php if (!empty($reservation['date_annulation'])): ?>
                                                            <small class="text-muted d-block">
                                                                Le <?= date('d/m/Y à H:i', strtotime($reservation['date_annulation'])) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php elseif ($reservation['statut'] === 'termine'): ?>
                                                    <span class="text-primary">
                                                        <i class="fas fa-flag-checkered me-1" aria-hidden="true"></i>Trajet terminé
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <nav>
                                                <a href="/trajet/<?= $reservation['trajet_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   aria-label="Voir les détails du trajet">
                                                    <i class="fas fa-eye me-1" aria-hidden="true"></i>Voir le trajet
                                                </a>
                                            </nav>
                                        </div>
                                    </div>
                                </footer>

                                <!-- ✅ NOUVEAU WORKFLOW : NOTATION DES TRAJETS TERMINÉS -->
                                <?php if ($reservation['statut'] === 'termine'): ?>
                                    <!-- 🌟 TRAJET TERMINÉ - BOUTON DE NOTATION -->
                                    <div class="alert alert-success mt-3" role="alert">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <i class="fas fa-check-circle me-2"></i>
                                                <strong>Trajet terminé !</strong> Comment s'est passé ce voyage ?
                                            </div>
                                            <a href="/EcoRide/public/donner-avis?trajet_id=<?= $reservation['trajet_id'] ?>&conducteur_id=<?= $reservation['conducteur_id'] ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="fas fa-star me-1"></i>
                                                Noter ce trajet
                                            </a>
                                        </div>
                                    </div>
                                    
                                <?php elseif ($reservation['statut'] === 'confirme'): ?>
                                    <!-- 📋 LOGIQUE POUR LES TRAJETS EN COURS -->
                                    <?php if (
                                        isset($reservation['statut_validation'], $reservation['trajet_statut_execution']) &&
                                        $reservation['statut_validation'] === 'attente' &&
                                        $reservation['trajet_statut_execution'] === 'termine'
                                    ): ?>
                                        <!-- ✅ VALIDATION DU TRAJET -->
                                        <form method="POST" action="/EcoRide/public/valider-trajet" class="mt-3">
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Confirmer que le trajet s'est bien passé
                                            </button>
                                        </form>
                                        
                                    <?php elseif (isset($reservation['date_debut_trajet']) && $reservation['date_debut_trajet']): ?>
                                        <!-- 🚗 TRAJET EN COURS -->
                                        <div class="alert alert-info mt-3" role="alert">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-road me-2"></i>
                                                <span><strong>Trajet en cours</strong> - Démarré le <?= date('d/m/Y à H:i', strtotime($reservation['date_debut_trajet'])) ?></span>
                                            </div>
                                        </div>
                                        
                                    <?php else: ?>
                                        <!-- ⏳ EN ATTENTE DU DÉMARRAGE -->
                                        <div class="alert alert-secondary mt-3" role="alert">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-hourglass-half me-2"></i>
                                                <span>Trajet confirmé - En attente du démarrage par le conducteur</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<!-- Modal d'annulation -->
<div class="modal fade" id="modalAnnulation" tabindex="-1" aria-labelledby="modalAnnulationLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="modalAnnulationLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2" aria-hidden="true"></i>
                    Annuler la réservation
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="/annuler-reservation">
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir annuler cette réservation ?</p>
                    <p class="text-muted small">Vos crédits vous seront remboursés automatiquement.</p>
                    
                    <div class="mb-3">
                        <label for="motif_annulation" class="form-label">Motif d'annulation (optionnel)</label>
                        <textarea class="form-control" id="motif_annulation" name="motif_annulation" rows="3" placeholder="Raison de l'annulation..."></textarea>
                    </div>
                    
                    <input type="hidden" id="reservation_id" name="reservation_id" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Garder la réservation</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2" aria-hidden="true"></i>Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$cssFiles = ['css/reservations.css'];
$jsFiles = ['js/mes-reservations.js'];
require __DIR__ . '/../layouts/main.php';
?>
