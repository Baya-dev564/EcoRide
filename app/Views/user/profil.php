<?php
// app/Views/user/profil.php
// Vue pour le profil utilisateur 

ob_start();

// Inclusion du fichier JavaScript spécifique 
$jsFiles = ['/js/profil.js'];
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

<!-- Contenu principal -->
<main class="container py-4" role="main">
    <!-- En-tête du profil utilisateur -->
    <header class="row mb-4">
        <div class="col-12">
            <section class="card border-0 shadow-sm" aria-labelledby="profil-titre">
                <header class="card-body">
                    <div class="row align-items-center">
                        <!-- Photo de profil avec sémantique appropriée -->
                        <aside class="col-md-2 text-center">
                            <?php if (!empty($user['photo_profil'] ?? '')): ?>
                                <img src="<?= htmlspecialchars($user['photo_profil']) ?>" 
                                     class="rounded-circle" 
                                     width="80" height="80" 
                                     alt="Photo de profil de <?= htmlspecialchars($user['pseudo']) ?>"
                                     role="img">
                            <?php else: ?>
                                <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px;"
                                     role="img"
                                     aria-label="Avatar par défaut de <?= htmlspecialchars($user['pseudo']) ?>">
                                    <i class="fas fa-user fa-2x text-white" aria-hidden="true"></i>
                                </div>
                            <?php endif; ?>
                        </aside>
                        
                        <!-- Informations principales avec hiérarchie correcte -->
                        <section class="col-md-6">
                            <h1 id="profil-titre" class="text-success mb-1">
                                <?= htmlspecialchars($user['prenom'] ?? '') ?> <?= htmlspecialchars($user['nom'] ?? '') ?>
                                <small class="text-muted">(@<?= htmlspecialchars($user['pseudo']) ?>)</small>
                            </h1>
                            
                            <address class="text-muted mb-1">
                                <i class="fas fa-envelope me-2" aria-hidden="true"></i>
                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($user['email']) ?>
                                </a>
                            </address>
                            
                            <?php if (!empty($user['telephone'] ?? '')): ?>
                                <address class="text-muted mb-1">
                                    <i class="fas fa-phone me-2" aria-hidden="true"></i>
                                    <a href="tel:<?= htmlspecialchars($user['telephone']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($user['telephone']) ?>
                                    </a>
                                </address>
                            <?php endif; ?>
                            
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar me-2" aria-hidden="true"></i>
                                <time datetime="<?= $user['created_at'] ?>">
                                    Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                </time>
                            </p>
                        </section>
                        
                        <!-- Actions et badges -->
                        <aside class="col-md-4 text-md-end">
                            <div class="d-flex flex-column align-items-md-end">
                                <div class="badge bg-warning text-dark fs-6 mb-2" role="img" aria-label="Solde de crédits : <?= $user['credit'] ?> crédits">
                                    <i class="fas fa-coins me-1" aria-hidden="true"></i><?= $user['credit'] ?> crédits
                                </div>
                                
                                <?php if (!empty($user['permis_conduire'])): ?>
                                    <div class="badge bg-success mb-2" role="img" aria-label="Permis de conduire validé">
                                        <i class="fas fa-id-card me-1" aria-hidden="true"></i>Permis de conduire
                                    </div>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-success btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalModifierProfil"
                                        aria-label="Ouvrir le formulaire de modification du profil">
                                    <i class="fas fa-edit me-1" aria-hidden="true"></i>Modifier le profil
                                </button>
                            </div>
                        </aside>
                    </div>
                </header>
            </section>
        </div>
    </header>

    <!-- Section des statistiques -->
    <section class="row mb-4" aria-labelledby="statistiques-titre">
        <div class="col-12">
            <h2 id="statistiques-titre" class="sr-only">Statistiques de votre activité EcoRide</h2>
        </div>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="text-primary mb-2" aria-hidden="true">
                        <i class="fas fa-route fa-2x"></i>
                    </div>
                    <h3 class="text-primary mb-1"><?= $stats['trajets_proposés'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Trajets proposés</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="text-info mb-2" aria-hidden="true">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h3 class="text-info mb-1"><?= $stats['reservations_effectuées'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Réservations effectuées</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="text-success mb-2" aria-hidden="true">
                        <i class="fas fa-coins fa-2x"></i>
                    </div>
                    <h3 class="text-success mb-1"><?= $stats['credits_gagnés'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Crédits gagnés</p>
                </div>
            </div>
        </article>
        
        <article class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <div class="text-warning mb-2" aria-hidden="true">
                        <i class="fas fa-leaf fa-2x"></i>
                    </div>
                    <h3 class="text-warning mb-1"><?= $stats['co2_economise'] ?? 0 ?> kg</h3>
                    <p class="text-muted mb-0">CO₂ économisé</p>
                </div>
            </div>
        </article>
    </section>

    <!-- Contenu principal -->
    <div class="row">
        <!-- Section des informations personnelles -->
        <section class="col-md-8" aria-labelledby="infos-titre">
            <article class="card border-0 shadow-sm">
                <header class="card-header bg-white">
                    <h2 id="infos-titre" class="mb-0">
                        <i class="fas fa-user me-2 text-success" aria-hidden="true"></i>
                        Informations personnelles
                    </h2>
                </header>
                
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-md-6 mb-3">Pseudo</dt>
                        <dd class="col-md-6 mb-3 fw-bold"><?= htmlspecialchars($user['pseudo']) ?></dd>
                        
                        <dt class="col-md-6 mb-3">Email</dt>
                        <dd class="col-md-6 mb-3 fw-bold"><?= htmlspecialchars($user['email']) ?></dd>
                        
                        <dt class="col-md-6 mb-3">Nom</dt>
                        <dd class="col-md-6 mb-3 fw-bold">
                            <?= !empty($user['nom'] ?? '') ? htmlspecialchars($user['nom']) : 'Non renseigné' ?>
                        </dd>
                        
                        <dt class="col-md-6 mb-3">Prénom</dt>
                        <dd class="col-md-6 mb-3 fw-bold">
                            <?= !empty($user['prenom'] ?? '') ? htmlspecialchars($user['prenom']) : 'Non renseigné' ?>
                        </dd>
                        
                        <dt class="col-md-6 mb-3">Téléphone</dt>
                        <dd class="col-md-6 mb-3 fw-bold">
                            <?= !empty($user['telephone'] ?? '') ? htmlspecialchars($user['telephone']) : 'Non renseigné' ?>
                        </dd>
                        
                        <dt class="col-md-6 mb-3">Permis de conduire</dt>
                        <dd class="col-md-6 mb-3 fw-bold">
                            <?php if (!empty($user['permis_conduire'])): ?>
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Oui, validé
                                </span>
                            <?php else: ?>
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1" aria-hidden="true"></i>Non renseigné
                                </span>
                                <small class="text-muted d-block">Requis pour proposer des trajets</small>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-12 mb-3">Adresse complète</dt>
                        <dd class="col-12 mb-3 fw-bold">
                            <?php if (!empty($user['adresse'] ?? '') || !empty($user['ville'] ?? '')): ?>
                                <address>
                                    <?= htmlspecialchars($user['adresse'] ?? '') ?><br>
                                    <?= htmlspecialchars($user['code_postal'] ?? '') ?> <?= htmlspecialchars($user['ville'] ?? '') ?>
                                </address>
                            <?php else: ?>
                                <span class="text-muted">Adresse non renseignée</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-12">Biographie</dt>
                        <dd class="col-12 fw-bold">
                            <?= !empty($user['bio'] ?? '') ? nl2br(htmlspecialchars($user['bio'])) : 'Aucune biographie renseignée.' ?>
                        </dd>
                    </dl>
                </div>
            </article>
        </section>
        
        <!-- Section des véhicules -->
        <section class="col-md-4" aria-labelledby="vehicules-titre">
            <article class="card border-0 shadow-sm">
                <header class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 id="vehicules-titre" class="mb-0">
                        <i class="fas fa-car me-2 text-primary" aria-hidden="true"></i>
                        Mes véhicules
                    </h2>
                    <button class="btn btn-sm btn-primary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalAjouterVehicule"
                            aria-label="Ajouter un nouveau véhicule">
                        <i class="fas fa-plus me-1" aria-hidden="true"></i>Ajouter
                    </button>
                </header>
                
                <div class="card-body">
                    <div id="listeVehicules" role="region" aria-live="polite" aria-label="Liste de vos véhicules">
                        <!-- Les véhicules seront chargés ici via JavaScript -->
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-car fa-2x mb-2" aria-hidden="true"></i>
                            <p>Aucun véhicule enregistré</p>
                            <small>Ajoutez vos véhicules pour proposer des trajets</small>
                        </div>
                    </div>
                </div>
            </article>
        </section>
        <!-- avis -->
<div class="col-md-6 mb-4">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-star text-warning"></i> Mes avis</h5>
        </div>
        <div class="card-body">
            <p>Donnez votre avis sur vos derniers trajets ou consultez les évaluations.</p>
            <a href="/avis" class="btn btn-outline-warning btn-sm me-2">
                <i class="fas fa-list"></i> Voir tous les avis
            </a>
            <a href="/avis/creer" class="btn btn-warning btn-sm">
                <i class="fas fa-plus"></i> Donner un avis
            </a>
        </div>
    </div>
</div>

    </div>
</main>
<!-- Modal de modification du profil -->
<div class="modal fade" id="modalModifierProfil" tabindex="-1" aria-labelledby="modalModifierProfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <header class="modal-header">
                <h2 id="modalModifierProfilLabel" class="modal-title">
                    <i class="fas fa-edit me-2" aria-hidden="true"></i>
                    Modifier mon profil complet
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer la fenêtre de modification"></button>
            </header>
            
            <form id="formModifierProfil" role="form" aria-labelledby="modalModifierProfilLabel">
                <div class="modal-body">
                    <fieldset>
                        <legend class="sr-only">Informations personnelles</legend>
                        
                        <div class="row">
                            <!-- Informations de base -->
                            <div class="col-md-6 mb-3">
                                <label for="pseudo" class="form-label">Pseudo *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="pseudo" 
                                       name="pseudo" 
                                       value="<?= htmlspecialchars($user['pseudo']) ?>" 
                                       required
                                       autocomplete="username"
                                       aria-describedby="pseudo-help">
                                <div id="pseudo-help" class="form-text">Votre nom d'utilisateur sur EcoRide</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
                                       required
                                       autocomplete="email"
                                       aria-describedby="email-help">
                                <div id="email-help" class="form-text">Votre adresse email de connexion</div>
                            </div>
                            
                            <!-- Nom et prénom -->
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nom" 
                                       name="nom" 
                                       value="<?= htmlspecialchars($user['nom'] ?? '') ?>"
                                       autocomplete="family-name">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="prenom" 
                                       name="prenom" 
                                       value="<?= htmlspecialchars($user['prenom'] ?? '') ?>"
                                       autocomplete="given-name">
                            </div>
                            
                            <!-- Contact -->
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telephone" 
                                       name="telephone" 
                                       value="<?= htmlspecialchars($user['telephone'] ?? '') ?>"
                                       autocomplete="tel"
                                       aria-describedby="telephone-help">
                                <div id="telephone-help" class="form-text">Format : 01 23 45 67 89</div>
                            </div>
                            
                            <!-- Permis de conduire -->
                            <div class="col-md-6 mb-3">
                                <fieldset>
                                    <legend class="form-label">Permis de conduire</legend>
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="permis_conduire" 
                                               name="permis_conduire" 
                                               <?= !empty($user['permis_conduire']) ? 'checked' : '' ?>
                                               aria-describedby="permis-help">
                                        <label class="form-check-label" for="permis_conduire">
                                            <i class="fas fa-id-card text-success me-1" aria-hidden="true"></i>
                                            J'ai le permis de conduire
                                        </label>
                                        <div id="permis-help" class="form-text">Obligatoire pour proposer des trajets</div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </fieldset>
                    
                    <fieldset>
                        <legend class="h6 text-muted">Adresse</legend>
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="adresse" 
                                       name="adresse" 
                                       value="<?= htmlspecialchars($user['adresse'] ?? '') ?>"
                                       placeholder="Numéro et nom de rue"
                                       autocomplete="street-address">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="code_postal" class="form-label">Code postal</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="code_postal" 
                                       name="code_postal" 
                                       value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>"
                                       placeholder="75001" 
                                       maxlength="5"
                                       pattern="\d{5}"
                                       autocomplete="postal-code"
                                       aria-describedby="cp-help">
                                <div id="cp-help" class="form-text">5 chiffres</div>
                            </div>
                            
                            <div class="col-md-8 mb-3">
                                <label for="ville" class="form-label">Ville</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ville" 
                                       name="ville" 
                                       value="<?= htmlspecialchars($user['ville'] ?? '') ?>"
                                       placeholder="Paris"
                                       autocomplete="address-level2">
                            </div>
                        </div>
                    </fieldset>
                    
                    <fieldset>
                        <legend class="h6 text-muted">À propos de vous</legend>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Biographie</label>
                            <textarea class="form-control" 
                                      id="bio" 
                                      name="bio" 
                                      rows="3" 
                                      maxlength="500"
                                      placeholder="Parlez-nous de vous, vos habitudes de voyage..."
                                      aria-describedby="bio-help"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            <div id="bio-help" class="form-text">Maximum 500 caractères</div>
                        </div>
                    </fieldset>
                </div>
                
                <footer class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer
                    </button>
                </footer>
            </form>
        </div>
    </div>
</div>


<!-- Modal ajout véhicule  -->
<div class="modal fade" id="modalAjouterVehicule" tabindex="-1" aria-labelledby="modalAjouterVehiculeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <header class="modal-header">
                <h2 id="modalAjouterVehiculeLabel" class="modal-title">
                    <i class="fas fa-car me-2" aria-hidden="true"></i>
                    Ajouter un véhicule
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer la fenêtre d'ajout de véhicule"></button>
            </header>
            
            <form id="formAjouterVehicule" role="form" aria-labelledby="modalAjouterVehiculeLabel">
                <div class="modal-body">
                    <fieldset>
                        <legend class="sr-only">Informations du véhicule</legend>
                        
                        <div class="mb-3">
                            <label for="marque" class="form-label">Marque *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="marque" 
                                   name="marque" 
                                   placeholder="Renault, Peugeot, Tesla..." 
                                   required
                                   aria-describedby="marque-help">
                            <div id="marque-help" class="form-text">Marque du constructeur</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modele" class="form-label">Modèle *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="modele" 
                                   name="modele" 
                                   placeholder="Clio, 308, Model 3..." 
                                   required
                                   aria-describedby="modele-help">
                            <div id="modele-help" class="form-text">Modèle du véhicule</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="couleur" class="form-label">Couleur</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="couleur" 
                                   name="couleur"
                                   placeholder="Blanc, Noir, Rouge...">
                        </div>
                        
                        <div class="mb-3">
                            <label for="immatriculation" class="form-label">Plaque d'immatriculation *</label>
                           2
                           <input type="text" 
                           class="form-control" 
                           id="immatriculation" 
                           name="plaque_immatriculation" 
                           placeholder="AB-123-CD" 
                           pattern="[A-Z]{2}-\d{3}-[A-Z]{2}"
                           required
                           aria-describedby="immat-help">

                            <div id="immat-help" class="form-text">Format français : AB-123-CD</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="places_disponibles" class="form-label">Nombre de places disponibles</label>
                            <select class="form-select" 
                                    id="places_disponibles" 
                                    name="places_disponibles"
                                    aria-describedby="places-help">
                                <option value="1">1 place</option>
                                <option value="2">2 places</option>
                                <option value="3">3 places</option>
                                <option value="4" selected>4 places</option>
                                <option value="5">5 places</option>
                                <option value="6">6 places</option>
                                <option value="7">7 places</option>
                                <option value="8">8 places</option>
                            </select>
                            <div id="places-help" class="form-text">Places disponibles pour les passagers</div>
                        </div>
                        
                        <div class="mb-3">
                            <fieldset>
                                <legend class="form-label">Type de véhicule</legend>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="electrique" 
                                           name="electrique"
                                           aria-describedby="electrique-help">
                                    <label class="form-check-label" for="electrique">
                                        <i class="fas fa-leaf text-success me-1" aria-hidden="true"></i>
                                        Véhicule électrique ou hybride
                                    </label>
                                    <div id="electrique-help" class="form-text">Les véhicules écologiques sont mis en avant sur EcoRide</div>
                                </div>
                            </fieldset>
                        </div>
                    </fieldset>
                </div>
                
                <footer class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2" aria-hidden="true"></i>Enregistrer
                    </button>
                </footer>
            </form>
        </div>
    </div>
</div>

<?php

$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
