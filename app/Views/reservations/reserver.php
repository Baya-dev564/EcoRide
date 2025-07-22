<?php
// app/Views/reservations/reserver.php
// Vue pour le formulaire de réservation e

ob_start();

// Inclusion du fichier JavaScript spécifique
$jsFiles = ['/EcoRide/public/js/reserver.js'];
?>

<main class="container py-4" role="main">
    <!-- En-tête -->
    <header class="row mb-4">
        <div class="col-12">
            <nav aria-label="Fil d'Ariane">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/EcoRide/public/">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="/EcoRide/public/trajets">Trajets</a></li>
                    <li class="breadcrumb-item"><a href="/EcoRide/public/trajet/<?= $trajet['id'] ?>">Détails</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Réserver</li>
                </ol>
            </nav>
            <h1 class="text-success">
                <i class="fas fa-calendar-plus me-2" aria-hidden="true"></i>Réserver ce trajet
            </h1>
        </div>
    </header>

    <div class="row">
        <!-- Détails du trajet -->
        <section class="col-md-6" aria-labelledby="details-titre">
            <article class="card border-0 shadow-sm mb-4">
                <header class="card-header bg-success text-white">
                    <h2 id="details-titre" class="mb-0">
                        <i class="fas fa-route me-2" aria-hidden="true"></i>Détails du trajet
                    </h2>
                </header>
                
                <div class="card-body">
                    <!-- Itinéraire -->
                    <section class="mb-3" aria-labelledby="itineraire-titre">
                        <h3 id="itineraire-titre" class="sr-only">Itinéraire du trajet</h3>
                        
                        <div class="d-flex align-items-center mb-2">
                            <div class="text-success me-3">
                                <i class="fas fa-map-marker-alt fa-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($trajet['lieu_depart']) ?></strong>
                                <address class="text-muted d-block mb-0"><?= htmlspecialchars($trajet['code_postal_depart']) ?></address>
                            </div>
                        </div>
                        
                        <div class="text-center text-muted my-2">
                            <i class="fas fa-arrow-down" aria-hidden="true"></i>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="text-danger me-3">
                                <i class="fas fa-map-marker-alt fa-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($trajet['lieu_arrivee']) ?></strong>
                                <address class="text-muted d-block mb-0"><?= htmlspecialchars($trajet['code_postal_arrivee']) ?></address>
                            </div>
                        </div>
                    </section>

                    <!-- Informations -->
                    <section class="row text-center mb-3" aria-labelledby="infos-titre">
                        <h3 id="infos-titre" class="sr-only">Informations du trajet</h3>
                        
                        <div class="col-4">
                            <div class="text-primary mb-1">
                                <i class="fas fa-calendar" aria-hidden="true"></i>
                            </div>
                            <small class="text-muted d-block">
                                <time datetime="<?= $trajet['date_depart'] ?>">
                                    <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?>
                                </time>
                            </small>
                            <small class="fw-bold">
                                <time datetime="<?= $trajet['date_depart'] ?>">
                                    <?= date('H:i', strtotime($trajet['date_depart'])) ?>
                                </time>
                            </small>
                        </div>
                        
                        <div class="col-4">
                            <div class="text-warning mb-1">
                                <i class="fas fa-users" aria-hidden="true"></i>
                            </div>
                            <small class="text-muted d-block">Places</small>
                            <small class="fw-bold">
                                <?= $trajet['places_disponibles'] ?> disponibles
                            </small>
                        </div>
                        
                        <div class="col-4">
                            <div class="text-success mb-1">
                                <i class="fas fa-coins" aria-hidden="true"></i>
                            </div>
                            <small class="text-muted d-block">Prix</small>
                            <small class="fw-bold">
                                <?= $prixParPlace ?> crédits/place
                            </small>
                        </div>
                    </section>

                    <!-- Conducteur -->
                    <section class="border-top pt-3" aria-labelledby="conducteur-titre">
                        <h3 id="conducteur-titre" class="h6 text-muted mb-2">Conducteur</h3>
                        
                        <div class="d-flex align-items-center">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 40px; height: 40px;"
                                 role="img"
                                 aria-label="Avatar du conducteur <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>">
                                <i class="fas fa-user text-white" aria-hidden="true"></i>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($trajet['conducteur_pseudo']) ?></strong>
                                <div class="text-warning" role="img" aria-label="Note : <?= $trajet['conducteur_note'] ?> étoiles sur 5">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $trajet['conducteur_note'] ? '' : '-o' ?>" aria-hidden="true"></i>
                                    <?php endfor; ?>
                                    <small class="text-muted">(<?= $trajet['conducteur_note'] ?>/5)</small>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Badge écologique -->
                    <?php if ($trajet['vehicule_electrique']): ?>
                        <aside class="mt-3">
                            <span class="badge bg-success" role="img" aria-label="Véhicule électrique - Trajet écologique">
                                <i class="fas fa-leaf me-1" aria-hidden="true"></i>Véhicule électrique
                            </span>
                        </aside>
                    <?php endif; ?>
                </div>
            </article>
        </section>

        <!-- Formulaire de réservation -->
        <section class="col-md-6" aria-labelledby="reservation-titre">
            <article class="card border-0 shadow-sm">
                <header class="card-header bg-white">
                    <h2 id="reservation-titre" class="mb-0">
                        <i class="fas fa-ticket-alt me-2" aria-hidden="true"></i>Réservation
                    </h2>
                </header>
                
                <div class="card-body">
                    <form id="formReservation" role="form" aria-labelledby="reservation-titre">
                        <input type="hidden" name="trajet_id" value="<?= $trajet['id'] ?>">
                        <input type="hidden" id="prixParPlace" value="<?= $prixParPlace ?>">
                        <input type="hidden" id="creditsUtilisateur" value="<?= $creditsUtilisateur ?>">
                        
                        <fieldset>
                            <legend class="sr-only">Détails de la réservation</legend>
                            
                            <!-- Nombre de places -->
                            <div class="mb-3">
                                <label for="nb_places" class="form-label">Nombre de places *</label>
                                <select class="form-select" 
                                        id="nb_places" 
                                        name="nb_places" 
                                        required
                                        aria-describedby="places-help">
                                    <?php for($i = 1; $i <= min(4, $trajet['places_disponibles']); $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> place<?= $i > 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                                <div id="places-help" class="form-text">Maximum <?= min(4, $trajet['places_disponibles']) ?> places par réservation</div>
                            </div>

                            <!-- Récapitulatif du coût -->
                            <section class="mb-3" aria-labelledby="recap-titre">
                                <h3 id="recap-titre" class="sr-only">Récapitulatif des coûts</h3>
                                
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h4 class="card-title h6">Récapitulatif</h4>
                                        <dl class="row mb-0">
                                            <dt class="col-8">Prix par place :</dt>
                                            <dd class="col-4 text-end"><?= $prixParPlace ?> crédits</dd>
                                            
                                            <dt class="col-8">Nombre de places :</dt>
                                            <dd class="col-4 text-end" id="recap-places">1</dd>
                                            
                                            <dt class="col-12"><hr></dt>
                                            
                                            <dt class="col-8 fw-bold">Total :</dt>
                                            <dd class="col-4 text-end fw-bold" id="recap-total"><?= $prixParPlace ?> crédits</dd>
                                        </dl>
                                    </div>
                                </div>
                            </section>

                            <!-- Crédits disponibles -->
                            <aside class="mb-3">
                                <div class="alert alert-info" id="alert-credits" role="status" aria-live="polite">
                                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                                    Vous avez <strong><?= $creditsUtilisateur ?> crédits</strong> disponibles
                                </div>
                            </aside>

                            <!-- Commentaire optionnel -->
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Message au conducteur (optionnel)</label>
                                <textarea class="form-control" 
                                          id="commentaire" 
                                          name="commentaire" 
                                          rows="3" 
                                          maxlength="200"
                                          placeholder="Un message pour le conducteur..."
                                          aria-describedby="commentaire-help"></textarea>
                                <div id="commentaire-help" class="form-text">Message optionnel pour le conducteur (max 200 caractères)</div>
                            </div>

                            <!-- Conditions -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="conditions" 
                                           required
                                           aria-describedby="conditions-help">
                                    <label class="form-check-label" for="conditions">
                                        J'accepte les <a href="/EcoRide/public/conditions" target="_blank" class="text-success">conditions de réservation</a> *
                                    </label>
                                    <div id="conditions-help" class="invalid-feedback">
                                        Vous devez accepter les conditions de réservation.
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Actions -->
                        <footer class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check me-2" aria-hidden="true"></i>Confirmer la réservation
                            </button>
                            <a href="/EcoRide/public/trajet/<?= $trajet['id'] ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Retour aux détails
                            </a>
                        </footer>
                    </form>
                </div>
            </article>
        </section>
    </div>
</main>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
