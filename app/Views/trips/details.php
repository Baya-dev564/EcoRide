<?php
/**
 * Vue détail d'un trajet EcoRide
 * Respecte la sémantique HTML5, l'accessibilité WCAG 2.1 et les bonnes pratiques
 * 
 * @author Équipe EcoRide
 * @version 1.0
 */

ob_start();
?>

<div class="container py-4">
    <!-- Messages système avec rôles ARIA appropriés -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" 
             role="alert" 
             aria-live="polite" 
             aria-atomic="true">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                <div><?= htmlspecialchars($message) ?></div>
            </div>
            <button type="button" 
                    class="btn-close" 
                    data-bs-dismiss="alert" 
                    aria-label="Fermer le message de succès"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger alert-dismissible fade show" 
             role="alert" 
             aria-live="assertive" 
             aria-atomic="true">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                <div><?= htmlspecialchars($erreur) ?></div>
            </div>
            <button type="button" 
                    class="btn-close" 
                    data-bs-dismiss="alert" 
                    aria-label="Fermer le message d'erreur"></button>
        </div>
    <?php endif; ?>

    <!-- Navigation de fil d'Ariane sémantique -->
    <nav aria-label="Fil d'Ariane de navigation">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/" aria-label="Retour à l'accueil EcoRide">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span class="visually-hidden">Accueil</span>
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="/trajets" aria-label="Retour à la liste des covoiturages">Covoiturages</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <span class="visually-hidden">Trajet actuel : </span>
                <?= htmlspecialchars($trajet['lieu_depart']) ?> → <?= htmlspecialchars($trajet['lieu_arrivee']) ?>
            </li>
        </ol>
    </nav>

    <!-- Contenu principal structuré sémantiquement -->
    <div class="row">
        <!-- Section principale - Détails du trajet -->
        <main class="col-lg-8" role="main" aria-labelledby="trajet-titre">
            <article class="card shadow-sm" itemscope itemtype="https://schema.org/Trip">
                <!-- En-tête du trajet -->
                <header class="card-header bg-success text-white">
                    <h1 class="h4 mb-0" id="trajet-titre">
                        <i class="fas fa-route me-2" aria-hidden="true"></i>
                        <span itemprop="departureLocation"><?= htmlspecialchars($trajet['lieu_depart']) ?></span>
                        <span aria-hidden="true"> → </span>
                        <span class="visually-hidden">vers</span>
                        <span itemprop="arrivalLocation"><?= htmlspecialchars($trajet['lieu_arrivee']) ?></span>
                        
                        <?php if ($trajet['vehicule_electrique']): ?>
                            <span class="badge bg-light text-success ms-2" 
                                  role="img" 
                                  aria-label="Trajet écologique avec véhicule électrique">
                                <i class="fas fa-leaf" aria-hidden="true"></i>
                                <span class="visually-hidden">Écologique</span>
                            </span>
                        <?php endif; ?>
                    </h1>
                </header>
                
                <div class="card-body">
                    <!-- Informations principales du trajet -->
                    <section aria-labelledby="infos-principales">
                        <h2 class="visually-hidden" id="infos-principales">Informations principales du trajet</h2>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <!-- Date et heure -->
                                <div class="mb-3">
                                    <h3 class="h5">
                                        <i class="fas fa-calendar-alt text-success" aria-hidden="true"></i>
                                        <span class="ms-1">Date et heure de départ</span>
                                    </h3>
                                    <p class="mb-0" itemprop="departureTime">
                                        <time datetime="<?= date('c', strtotime($trajet['date_depart'])) ?>">
                                            <?= $trajet['date_depart_formatee'] ?>
                                        </time>
                                    </p>
                                </div>
                                
                                <!-- Distance -->
                                <div class="mb-3">
                                    <h3 class="h5">
                                        <i class="fas fa-route text-success" aria-hidden="true"></i>
                                        <span class="ms-1">Distance estimée</span>
                                    </h3>
                                    <p class="mb-0" itemprop="distance">
                                        <?= $trajet['distance_estimee'] ?>
                                        <?php if ($trajet['duree_estimee'] !== 'N/A'): ?>
                                            <span class="text-muted ms-2">• Durée : <?= $trajet['duree_estimee'] ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Places disponibles -->
                                <div class="mb-3">
                                    <h3 class="h5">
                                        <i class="fas fa-users text-success" aria-hidden="true"></i>
                                        <span class="ms-1">Places disponibles</span>
                                    </h3>
                                    <p class="mb-0">
                                        <span class="badge bg-primary fs-6" 
                                              role="status" 
                                              aria-label="<?= $trajet['places_disponibles'] ?> places disponibles sur ce trajet">
                                            <?= $trajet['places_disponibles'] ?> place<?= $trajet['places_disponibles'] > 1 ? 's' : '' ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <!-- Prix -->
                                <div class="mb-3">
                                    <h3 class="h5">
                                        <i class="fas fa-coins text-success" aria-hidden="true"></i>
                                        <span class="ms-1">Prix du trajet</span>
                                    </h3>
                                    <p class="mb-0">
                                        <span class="h4 text-success" 
                                              itemprop="price" 
                                              aria-label="Prix : <?= $trajet['prix'] ?> crédits EcoRide">
                                            <?= $trajet['prix'] ?> crédits
                                        </span>
                                        <?php if ($trajet['vehicule_electrique']): ?>
                                            <small class="text-success d-block">
                                                <i class="fas fa-tag" aria-hidden="true"></i>
                                                Prix réduit écologique
                                            </small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Commentaire du conducteur -->
                    <?php if (!empty($trajet['commentaire'])): ?>
                        <section class="mb-4" aria-labelledby="commentaire-titre">
                            <h2 class="h5" id="commentaire-titre">
                                <i class="fas fa-comment text-success" aria-hidden="true"></i>
                                <span class="ms-1">Message du conducteur</span>
                            </h2>
                            <div class="bg-light p-3 rounded" role="region" aria-label="Commentaire du conducteur">
                                <blockquote class="mb-0" itemprop="description">
                                    <?= nl2br(htmlspecialchars($trajet['commentaire'])) ?>
                                </blockquote>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Point de rencontre -->
                    <?php if (!empty($trajet['point_rencontre'])): ?>
                        <section class="mb-4" aria-labelledby="rencontre-titre">
                            <h2 class="h5" id="rencontre-titre">
                                <i class="fas fa-map-marker-alt text-success" aria-hidden="true"></i>
                                <span class="ms-1">Point de rencontre</span>
                            </h2>
                            <address class="mb-0" itemprop="meetingPoint">
                                <?= htmlspecialchars($trajet['point_rencontre']) ?>
                            </address>
                        </section>
                    <?php endif; ?>

                    <!-- Conditions particulières -->
                    <?php if (!empty($trajet['conditions_particulieres'])): ?>
                        <section class="mb-4" aria-labelledby="conditions-titre">
                            <h2 class="h5" id="conditions-titre">
                                <i class="fas fa-info-circle text-success" aria-hidden="true"></i>
                                <span class="ms-1">Conditions particulières</span>
                            </h2>
                            <div class="alert alert-info" role="region" aria-label="Conditions particulières du trajet">
                                <?= nl2br(htmlspecialchars($trajet['conditions_particulieres'])) ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Préférences du conducteur -->
                    <section class="mb-4" aria-labelledby="preferences-titre">
                        <h2 class="h5" id="preferences-titre">
                            <i class="fas fa-cog text-success" aria-hidden="true"></i>
                            <span class="ms-1">Préférences du conducteur</span>
                        </h2>
                        <div class="row" role="list" aria-label="Liste des préférences">
                            <div class="col-md-4" role="listitem">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-smoking <?= $trajet['fumeur_accepte'] ? 'text-success' : 'text-danger' ?> me-2" 
                                       aria-hidden="true"></i>
                                    <span><?= $trajet['fumeur_accepte'] ? 'Fumeur accepté' : 'Véhicule non-fumeur' ?></span>
                                </div>
                            </div>
                            <div class="col-md-4" role="listitem">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-paw <?= $trajet['animaux_acceptes'] ? 'text-success' : 'text-danger' ?> me-2" 
                                       aria-hidden="true"></i>
                                    <span><?= $trajet['animaux_acceptes'] ? 'Animaux acceptés' : 'Animaux non acceptés' ?></span>
                                </div>
                            </div>
                            <div class="col-md-4" role="listitem">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-suitcase <?= $trajet['bagages_acceptes'] ? 'text-success' : 'text-danger' ?> me-2" 
                                       aria-hidden="true"></i>
                                    <span><?= $trajet['bagages_acceptes'] ? 'Bagages acceptés' : 'Bagages limités' ?></span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Impact écologique -->
                    <section class="bg-success bg-opacity-10 p-3 rounded" 
                             aria-labelledby="impact-titre" 
                             role="region">
                        <h2 class="h5 text-success mb-2" id="impact-titre">
                            <i class="fas fa-seedling me-2" aria-hidden="true"></i>
                            Impact écologique positif
                        </h2>
                        <p class="mb-0" itemprop="environmentalImpact">
                            En partageant ce trajet, vous contribuez à économiser 
                            <strong><?= $trajet['co2_economise'] ?> de CO₂</strong> 
                            par rapport à un trajet individuel en voiture.
                        </p>
                    </section>
                </div>
            </article>
        </main>

        <!-- Barre latérale - Informations complémentaires -->
        <aside class="col-lg-4" role="complementary" aria-label="Informations sur le conducteur et réservation">
            
            <!-- Profil du conducteur -->
            <section class="card shadow-sm mb-4" aria-labelledby="conducteur-titre">
                <header class="card-header">
                    <h2 class="h5 mb-0" id="conducteur-titre">
                        <i class="fas fa-user me-2" aria-hidden="true"></i>
                        Profil du conducteur
                    </h2>
                </header>
                <div class="card-body text-center" itemscope itemtype="https://schema.org/Person">
                    <!-- Photo du conducteur -->
                    <?php if (!empty($trajet['conducteur_photo'])): ?>
                        <img src="<?= htmlspecialchars($trajet['conducteur_photo']) ?>" 
                             class="rounded-circle mb-3" 
                             width="80" 
                             height="80" 
                             alt="Photo de profil de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>"
                             itemprop="image">
                    <?php else: ?>
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px;"
                             role="img"
                             aria-label="Avatar par défaut de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>">
                            <i class="fas fa-user fa-2x text-white" aria-hidden="true"></i>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Nom du conducteur -->
                    <h3 class="h6" itemprop="name">
                        <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>
                    </h3>
                    
                    <!-- Note du conducteur -->
                    <?php if ($trajet['conducteur_note'] > 0): ?>
                        <div class="mb-2" 
                             role="img" 
                             aria-label="Note du conducteur : <?= number_format($trajet['conducteur_note'], 1) ?> étoiles sur 5"
                             itemprop="aggregateRating" 
                             itemscope 
                             itemtype="https://schema.org/AggregateRating">
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($trajet['conducteur_note'])): ?>
                                        <i class="fas fa-star" aria-hidden="true"></i>
                                    <?php elseif ($i - 0.5 <= $trajet['conducteur_note']): ?>
                                        <i class="fas fa-star-half-alt" aria-hidden="true"></i>
                                    <?php else: ?>
                                        <i class="far fa-star" aria-hidden="true"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">
                                <span itemprop="ratingValue"><?= number_format($trajet['conducteur_note'], 1) ?></span>/5
                                <meta itemprop="bestRating" content="5">
                            </small>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">Nouveau conducteur</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Informations du véhicule -->
            <section class="card shadow-sm mb-4" 
                     aria-labelledby="vehicule-titre" 
                     itemscope 
                     itemtype="https://schema.org/Vehicle">
                <header class="card-header">
                    <h2 class="h5 mb-0" id="vehicule-titre">
                        <i class="fas fa-car me-2" aria-hidden="true"></i>
                        Informations du véhicule
                    </h2>
                </header>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Marque :</dt>
                        <dd class="col-sm-8" itemprop="brand">
                            <?= htmlspecialchars($trajet['vehicule_marque']) ?>
                        </dd>
                        
                        <dt class="col-sm-4">Modèle :</dt>
                        <dd class="col-sm-8" itemprop="model">
                            <?= htmlspecialchars($trajet['vehicule_modele']) ?>
                        </dd>
                        
                        <dt class="col-sm-4">Couleur :</dt>
                        <dd class="col-sm-8" itemprop="color">
                            <?= htmlspecialchars($trajet['vehicule_couleur']) ?>
                        </dd>
                        
                        <dt class="col-sm-4">Énergie :</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= $trajet['vehicule_electrique_detail'] ? 'success' : 'secondary' ?>"
                                  itemprop="fuelType">
                                <?= $trajet['energie_vehicule'] ?>
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Capacité :</dt>
                        <dd class="col-sm-8" itemprop="seatingCapacity">
                            <?= $trajet['vehicule_nb_places'] ?> places
                        </dd>
                    </dl>
                </div>
            </section>

            <!-- Section de réservation -->
            <section class="card shadow-sm" aria-labelledby="reservation-titre">
                <header class="card-header">
                    <h2 class="h5 mb-0" id="reservation-titre">
                        <i class="fas fa-ticket-alt me-2" aria-hidden="true"></i>
                        Réservation
                    </h2>
                </header>
                <div class="card-body">
                    <?php if (!$userConnecte): ?>
                        <!-- Utilisateur non connecté -->
                        <div class="text-center">
                            <p class="text-muted mb-3">
                                <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
                                Vous devez être connecté pour réserver ce trajet
                            </p>
                            <a href="/connexion" 
                               class="btn btn-success btn-lg w-100"
                               aria-describedby="connexion-aide">
                                <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>
                                Se connecter
                            </a>
                            <div id="connexion-aide" class="mt-2">
                                <small class="text-muted">
                                    Pas encore de compte ? 
                                    <a href="/inscription" class="text-decoration-none">S'inscrire gratuitement</a>
                                </small>
                            </div>
                        </div>
                        
                    <?php elseif ($userConnecte['id'] == $trajet['conducteur_id']): ?>
                        <!-- C'est le conducteur -->
                        <div class="text-center">
                            <div class="alert alert-info" role="status">
                                <i class="fas fa-user-check me-2" aria-hidden="true"></i>
                                Vous êtes le conducteur de ce trajet
                            </div>
                            <a href="/mes-trajets" 
                               class="btn btn-outline-success"
                               aria-label="Aller à la gestion de mes trajets">
                                <i class="fas fa-list me-2" aria-hidden="true"></i>
                                Gérer mes trajets
                            </a>
                        </div>
                        
                    <?php elseif ($trajet['places_disponibles'] <= 0): ?>
                        <!-- Plus de places disponibles -->
                        <div class="text-center">
                            <div class="alert alert-warning" role="status">
                                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                                Ce trajet est complet
                            </div>
                            <button class="btn btn-secondary btn-lg w-100" 
                                    disabled 
                                    aria-label="Réservation impossible, trajet complet">
                                <i class="fas fa-times me-2" aria-hidden="true"></i>
                                Trajet complet
                            </button>
                        </div>
                        
                    <?php elseif ($userConnecte['credit'] < $trajet['prix']): ?>
                        <!-- Crédit insuffisant -->
                        <div class="text-center">
                            <div class="alert alert-warning" role="status">
                                <i class="fas fa-coins me-2" aria-hidden="true"></i>
                                Crédit insuffisant
                            </div>
                            <p class="small text-muted mb-3">
                                Vous avez <strong><?= $userConnecte['credit'] ?> crédits</strong>, 
                                il en faut <strong><?= $trajet['prix'] ?></strong>
                            </p>
                            <button class="btn btn-warning btn-lg w-100" 
                                    disabled 
                                    aria-label="Réservation impossible, crédit insuffisant">
                                <i class="fas fa-wallet me-2" aria-hidden="true"></i>
                                Crédit insuffisant
                            </button>
                        </div>
                        
                    <?php elseif ($this->aDejaReserve($trajet['id'], $userConnecte['id'])): ?>
                        <!-- Déjà réservé -->
                        <div class="text-center">
                            <div class="alert alert-success" role="status">
                                <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                                Vous avez déjà réservé ce trajet
                            </div>
                            <a href="/mes-reservations" 
                               class="btn btn-info btn-lg w-100"
                               aria-label="Voir mes réservations">
                                <i class="fas fa-calendar-check me-2" aria-hidden="true"></i>
                                Mes réservations
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <!-- Peut réserver -->
                        <div class="text-center">
                            <!-- Récapitulatif de la réservation -->
                            <div class="mb-4">
                                <h3 class="h6 mb-3">Récapitulatif de la réservation</h3>
                                <dl class="row small">
                                    <dt class="col-6">Prix du trajet :</dt>
                                    <dd class="col-6 text-end">
                                        <strong><?= $trajet['prix'] ?> crédits</strong>
                                    </dd>
                                    
                                    <dt class="col-6">Votre solde actuel :</dt>
                                    <dd class="col-6 text-end">
                                        <?= $userConnecte['credit'] ?> crédits
                                    </dd>
                                    
                                    <dt class="col-6">Solde après réservation :</dt>
                                    <dd class="col-6 text-end text-success">
                                        <strong><?= $userConnecte['credit'] - $trajet['prix'] ?> crédits</strong>
                                    </dd>
                                </dl>
                            </div>
                            
                            <!-- Formulaire de réservation -->
                            <form method="POST" 
                                  action="/reserver-trajet" 
                                  id="formReservation"
                                  aria-labelledby="reservation-titre">
                                <input type="hidden" 
                                       name="trajet_id" 
                                       value="<?= $trajet['id'] ?>">
                                
                                <!-- Message optionnel du passager -->
                                <div class="mb-3">
                                    <label for="message_passager" class="form-label visually-hidden">
                                        Message optionnel pour le conducteur
                                    </label>
                                    <textarea class="form-control" 
                                              id="message_passager" 
                                              name="message_passager" 
                                              rows="2" 
                                              placeholder="Message optionnel pour le conducteur..."
                                              maxlength="500"
                                              aria-describedby="message-aide"></textarea>
                                    <div id="message-aide" class="form-text">
                                        Présentez-vous ou ajoutez des informations utiles (optionnel)
                                    </div>
                                </div>
                                
                                <!-- Bouton de réservation -->
                                <button type="submit" 
                                        class="btn btn-success btn-lg w-100" 
                                        id="btnReserver"
                                        aria-describedby="reservation-aide">
                                    <i class="fas fa-check me-2" aria-hidden="true"></i>
                                    Réserver ce trajet
                                </button>
                                
                                <div id="reservation-aide" class="form-text mt-2">
                                    En cliquant, vous confirmez votre réservation et acceptez d'être débité de <?= $trajet['prix'] ?> crédits
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
</div>

<!-- Modal de confirmation de réservation -->
<div class="modal fade" 
     id="modalConfirmation" 
     tabindex="-1" 
     aria-labelledby="modalConfirmationLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <header class="modal-header">
                <h2 class="modal-title h5" id="modalConfirmationLabel">
                    <i class="fas fa-question-circle text-warning me-2" aria-hidden="true"></i>
                    Confirmer la réservation
                </h2>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal" 
                        aria-label="Fermer la fenêtre de confirmation"></button>
            </header>
            
            <div class="modal-body">
                <p class="mb-3">Êtes-vous sûr de vouloir réserver ce trajet ?</p>
                
                <div class="alert alert-info" role="region" aria-label="Récapitulatif de la réservation">
                    <h3 class="h6 mb-2">
                        <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
                        Récapitulatif final
                    </h3>
                    <dl class="row mb-0 small">
                        <dt class="col-4">Trajet :</dt>
                        <dd class="col-8">
                            <?= htmlspecialchars($trajet['lieu_depart']) ?> → <?= htmlspecialchars($trajet['lieu_arrivee']) ?>
                        </dd>
                        
                        <dt class="col-4">Date :</dt>
                        <dd class="col-8"><?= $trajet['date_depart_formatee'] ?></dd>
                        
                        <dt class="col-4">Prix :</dt>
                        <dd class="col-8"><strong><?= $trajet['prix'] ?> crédits</strong></dd>
                    </dl>
                </div>
                
                <p class="text-muted small mb-0">
                    <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                    Cette action est irréversible. Vous pourrez annuler votre réservation depuis votre espace personnel.
                </p>
            </div>
            
            <footer class="modal-footer">
                <button type="button" 
                        class="btn btn-secondary" 
                        data-bs-dismiss="modal">
                    <i class="fas fa-times me-2" aria-hidden="true"></i>
                    Annuler
                </button>
                <button type="button" 
                        class="btn btn-success" 
                        id="btnConfirmerReservation">
                    <i class="fas fa-check me-2" aria-hidden="true"></i>
                    Confirmer la réservation
                </button>
            </footer>
        </div>
    </div>
</div>

<!-- Script pour la gestion de la réservation -->
<script>
/**
 * Gestion de la réservation avec double confirmation
 * Respecte les bonnes pratiques d'accessibilité et d'UX
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const formReservation = document.getElementById('formReservation');
    const btnReserver = document.getElementById('btnReserver');
    const modalElement = document.getElementById('modalConfirmation');
    const btnConfirmerReservation = document.getElementById('btnConfirmerReservation');
    
    // Vérification de l'existence des éléments
    if (!formReservation || !btnReserver || !modalElement || !btnConfirmerReservation) {
        return; // Sortir si les éléments n'existent pas
    }
    
    // Initialisation de la modal Bootstrap
    const modalConfirmation = new bootstrap.Modal(modalElement, {
        keyboard: true,
        backdrop: true
    });
    
    // Gestion de la soumission du formulaire
    formReservation.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Désactiver le bouton pour éviter les doubles clics
        btnReserver.disabled = true;
        btnReserver.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Préparation...';
        
        // Afficher la modal après un court délai
        setTimeout(function() {
            modalConfirmation.show();
            
            // Réactiver le bouton
            btnReserver.disabled = false;
            btnReserver.innerHTML = '<i class="fas fa-check me-2" aria-hidden="true"></i>Réserver ce trajet';
        }, 500);
    });
    
    // Gestion de la confirmation finale
    btnConfirmerReservation.addEventListener('click', function() {
        // Désactiver le bouton de confirmation
        btnConfirmerReservation.disabled = true;
        btnConfirmerReservation.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Réservation...';
        
        // Soumettre le formulaire
        formReservation.submit();
    });
    
    // Réinitialiser le bouton si la modal est fermée sans confirmation
    modalElement.addEventListener('hidden.bs.modal', function() {
        btnConfirmerReservation.disabled = false;
        btnConfirmerReservation.innerHTML = '<i class="fas fa-check me-2" aria-hidden="true"></i>Confirmer la réservation';
    });
    
    // Gestion du focus pour l'accessibilité
    modalElement.addEventListener('shown.bs.modal', function() {
        btnConfirmerReservation.focus();
    });
});
</script>

<?php
$content = ob_get_clean();

// Métadonnées pour le SEO et les réseaux sociaux
$metaDescription = "Détails du trajet de covoiturage de " . htmlspecialchars($trajet['lieu_depart']) . " à " . htmlspecialchars($trajet['lieu_arrivee']) . " le " . $trajet['date_depart_formatee'] . " - " . $trajet['prix'] . " crédits";
$metaKeywords = "covoiturage, " . htmlspecialchars($trajet['lieu_depart']) . ", " . htmlspecialchars($trajet['lieu_arrivee']) . ", transport écologique, EcoRide";

// Données structurées JSON-LD pour le SEO
$jsonLd = [
    "@context" => "https://schema.org",
    "@type" => "Trip",
    "name" => htmlspecialchars($trajet['lieu_depart']) . " → " . htmlspecialchars($trajet['lieu_arrivee']),
    "description" => $trajet['commentaire'] ? htmlspecialchars($trajet['commentaire']) : "Trajet de covoiturage EcoRide",
    "departureLocation" => [
        "@type" => "Place",
        "name" => htmlspecialchars($trajet['lieu_depart']),
        "address" => htmlspecialchars($trajet['code_postal_depart'])
    ],
    "arrivalLocation" => [
        "@type" => "Place", 
        "name" => htmlspecialchars($trajet['lieu_arrivee']),
        "address" => htmlspecialchars($trajet['code_postal_arrivee'])
    ],
    "departureTime" => date('c', strtotime($trajet['date_depart'])),
    "provider" => [
        "@type" => "Organization",
        "name" => "EcoRide",
        "url" => "https://ecoride.fr"
    ],
    "offers" => [
        "@type" => "Offer",
        "price" => $trajet['prix'],
        "priceCurrency" => "CREDITS",
        "availability" => $trajet['places_disponibles'] > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock"
    ]
];

// Fichiers CSS et JS spécifiques
$cssFiles = ['css/trips.css', 'css/trip-details.css'];
$jsFiles = ['js/trip-details.js'];

// Inclusion du layout principal
require __DIR__ . '/../layouts/main.php';
?>
