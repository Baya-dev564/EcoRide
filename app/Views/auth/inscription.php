<?php
// Formulaire d'inscription EcoRide 

ob_start();
?>

<!-- Contenu principal -->
<main class="row justify-content-center" role="main">
    <div class="col-lg-8 col-md-10">
        <article class="card shadow-lg border-0">
            <div class="card-body p-5">
                <!-- En-tête du formulaire -->
                <header class="text-center mb-4">
                    <h1 class="text-success fw-bold">🌱 Rejoignez EcoRide</h1>
                    <p class="text-muted lead">Créez votre compte et recevez <strong>20 crédits de bienvenue</strong> !</p>
                </header>

                <!-- Affichage des erreurs de validation-->
                <?php if (!empty($erreurs)): ?>
                    <aside class="alert alert-danger border-0 shadow-sm" role="alert" aria-live="polite">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-triangle me-2 mt-1" aria-hidden="true"></i>
                            <div>
                                <h2 class="alert-heading h6 mb-2">Erreurs détectées :</h2>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($erreurs as $erreur): ?>
                                        <li><?= htmlspecialchars($erreur) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </aside>
                <?php endif; ?>

                <!-- Formulaire d'inscription  -->
               
                    <form id="formInscription" action="/api/inscription" method="POST" novalidate role="form" aria-labelledby="inscription-titre">

                    <h2 id="inscription-titre" class="sr-only">Formulaire d'inscription EcoRide</h2>
                    
                    <!-- Section informations de base -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-success mb-3">
                            <i class="fas fa-user me-2" aria-hidden="true"></i>Informations de base
                        </legend>
                        
                        <div class="row">
                            <!-- Pseudo -->
                            <div class="col-md-6 mb-3">
                                <label for="pseudo" class="form-label fw-semibold">
                                    <i class="fas fa-user text-success me-1" aria-hidden="true"></i>Pseudo *
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="pseudo" 
                                       name="pseudo" 
                                       value="<?= htmlspecialchars($donnees['pseudo'] ?? '') ?>"
                                       required 
                                       minlength="3" 
                                       maxlength="50"
                                       pattern="[a-zA-Z0-9_-]+"
                                       placeholder="Votre pseudo unique"
                                       aria-describedby="pseudo-help">
                                <div id="pseudo-help" class="form-text">
                                    <i class="fas fa-info-circle text-info" aria-hidden="true"></i>
                                    Utilisez uniquement des lettres, chiffres, tirets et underscores.
                                </div>
                                <div class="invalid-feedback">
                                    Le pseudo doit contenir entre 3 et 50 caractères (lettres, chiffres, - et _ uniquement).
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">
                                    <i class="fas fa-envelope text-success me-1" aria-hidden="true"></i>Adresse email *
                                </label>
                                <input type="email" 
                                       class="form-control form-control-lg" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($donnees['email'] ?? '') ?>"
                                       required 
                                       placeholder="nom@domaine.com"
                                       aria-describedby="email-help">
                                <div id="email-help" class="form-text">
                                    <i class="fas fa-shield-alt text-info" aria-hidden="true"></i>
                                    Votre email ne sera jamais partagé et restera confidentiel.
                                </div>
                                <div class="invalid-feedback">
                                    Veuillez saisir une adresse email valide (exemple : nom@domaine.com).
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Nom -->
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label fw-semibold">
                                    <i class="fas fa-id-badge text-success me-1" aria-hidden="true"></i>Nom
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="nom" 
                                       name="nom" 
                                       value="<?= htmlspecialchars($donnees['nom'] ?? '') ?>"
                                       maxlength="100"
                                       placeholder="Votre nom de famille">
                                <div class="form-text">Optionnel mais recommandé pour la confiance</div>
                            </div>

                            <!-- Prénom -->
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label fw-semibold">
                                    <i class="fas fa-id-badge text-success me-1" aria-hidden="true"></i>Prénom
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="prenom" 
                                       name="prenom" 
                                       value="<?= htmlspecialchars($donnees['prenom'] ?? '') ?>"
                                       maxlength="100"
                                       placeholder="Votre prénom">
                                <div class="form-text">Optionnel mais recommandé pour la confiance</div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Section mot de passe -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-success mb-3">
                            <i class="fas fa-lock me-2" aria-hidden="true"></i>Sécurité
                        </legend>
                        
                        <div class="row">
                            <!-- Mot de passe -->
                            <div class="col-md-6 mb-3">
                                <label for="mot_de_passe" class="form-label fw-semibold">
                                    <i class="fas fa-lock text-success me-1" aria-hidden="true"></i>Mot de passe *
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           id="mot_de_passe" 
                                           name="mot_de_passe" 
                                           required 
                                           minlength="8"
                                           placeholder="Au moins 8 caractères"
                                           aria-describedby="mdp-help">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Afficher/masquer le mot de passe">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div id="mdp-help" class="form-text">
                                    <i class="fas fa-info-circle text-info" aria-hidden="true"></i>
                                    Minimum : 8 caractères, 1 minuscule, 1 majuscule, 1 chiffre
                                </div>
                                <div class="invalid-feedback">
                                    Le mot de passe doit contenir au moins 8 caractères avec une minuscule, une majuscule et un chiffre.
                                </div>
                            </div>

                            <!-- Confirmation mot de passe -->
                            <div class="col-md-6 mb-3">
                                <label for="confirmer_mot_de_passe" class="form-label fw-semibold">
                                    <i class="fas fa-check-double text-success me-1" aria-hidden="true"></i>Confirmer le mot de passe *
                                </label>
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="confirmer_mot_de_passe" 
                                       name="confirmer_mot_de_passe" 
                                       required 
                                       placeholder="Retapez exactement le même mot de passe"
                                       aria-describedby="confirm-help">
                                <div id="confirm-help" class="form-text">
                                    <i class="fas fa-info-circle text-info" aria-hidden="true"></i>
                                    Retapez exactement le même mot de passe pour confirmation.
                                </div>
                                <div class="invalid-feedback">
                                    Les mots de passe ne correspondent pas. Vérifiez votre saisie.
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Section informations complémentaires -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-success mb-3">
                            <i class="fas fa-map-marker-alt me-2" aria-hidden="true"></i>Informations complémentaires
                        </legend>
                        
                        <div class="row">
                            <!-- Téléphone -->
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label fw-semibold">
                                    <i class="fas fa-phone text-success me-1" aria-hidden="true"></i>Téléphone
                                </label>
                                <input type="tel" 
                                       class="form-control form-control-lg" 
                                       id="telephone" 
                                       name="telephone" 
                                       value="<?= htmlspecialchars($donnees['telephone'] ?? '') ?>"
                                       placeholder="01 23 45 67 89"
                                       pattern="[0-9+\-\s\.]{10,15}"
                                       aria-describedby="tel-help">
                                <div id="tel-help" class="form-text">Optionnel - Facilite le contact entre covoitureurs</div>
                            </div>

                            <!-- Ville -->
                            <div class="col-md-6 mb-3">
                                <label for="ville" class="form-label fw-semibold">
                                    <i class="fas fa-city text-success me-1" aria-hidden="true"></i>Ville
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="ville" 
                                       name="ville" 
                                       value="<?= htmlspecialchars($donnees['ville'] ?? '') ?>"
                                       placeholder="Paris, Lyon, Marseille..."
                                       aria-describedby="ville-help">
                                <div id="ville-help" class="form-text">Optionnel - Aide à trouver des trajets locaux</div>
                            </div>
                        </div>

                        <!-- Permis de conduire -->
                        <div class="mb-3">
                            <fieldset>
                                <legend class="form-label fw-semibold">Permis de conduire</legend>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="permis_conduire" 
                                           name="permis_conduire"
                                           <?= isset($donnees['permis_conduire']) && $donnees['permis_conduire'] ? 'checked' : '' ?>
                                           aria-describedby="permis-help">
                                    <label class="form-check-label" for="permis_conduire">
                                        <i class="fas fa-id-card text-success me-1" aria-hidden="true"></i>
                                        J'ai le permis de conduire
                                    </label>
                                    <div id="permis-help" class="form-text">Obligatoire pour proposer des trajets en tant que conducteur</div>
                                </div>
                            </fieldset>
                        </div>
                    </fieldset>

                    <!-- Consentement RGPD -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-success mb-3">
                            <i class="fas fa-shield-alt me-2" aria-hidden="true"></i>Consentement
                        </legend>
                        
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="consentement_rgpd" 
                                   name="consentement_rgpd" 
                                   required
                                   <?= isset($donnees['consentement_rgpd']) && $donnees['consentement_rgpd'] ? 'checked' : '' ?>
                                   aria-describedby="rgpd-help">
                            <label class="form-check-label" for="consentement_rgpd">
                                J'accepte la <a href="/EcoRide/public/confidentialite" target="_blank" class="text-success text-decoration-none">
                                    <i class="fas fa-external-link-alt" aria-hidden="true"></i> politique de confidentialité
                                </a> 
                                et le traitement de mes données personnelles. *
                            </label>
                            <div id="rgpd-help" class="invalid-feedback">
                                Vous devez accepter la politique de confidentialité pour créer votre compte.
                            </div>
                        </div>
                    </fieldset>

                    <!-- Actions du formulaire -->
                    <footer>
                        <!-- Bouton d'inscription -->
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg py-3 fw-semibold">
                                <i class="fas fa-user-plus me-2" aria-hidden="true"></i>
                                Créer mon compte EcoRide
                                <span class="badge bg-light text-success ms-2">20 crédits offerts !</span>
                            </button>
                        </div>

                        <!-- Liens de navigation -->
                        <nav class="text-center">
                            <p class="text-muted mb-2">
                                Déjà membre d'EcoRide ? 
                                <a href="/EcoRide/public/connexion" class="text-success text-decoration-none fw-semibold">
                                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Connectez-vous ici
                                </a>
                            </p>
                            <a href="/EcoRide/public/" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left" aria-hidden="true"></i> Retour à l'accueil
                            </a>
                        </nav>
                    </footer>
                </form>
            </div>
        </article>
        
        <!-- Informations sur les avantages -->
        <aside class="card mt-4 border-0 bg-light">
            <div class="card-body text-center">
                <h2 class="text-success mb-3">
                    <i class="fas fa-gift" aria-hidden="true"></i> Avantages de votre inscription
                </h2>
                <div class="row">
                    <article class="col-md-4">
                        <i class="fas fa-coins text-warning fa-2x mb-2" aria-hidden="true"></i>
                        <h3 class="h6 mb-0"><strong>20 crédits gratuits</strong></h3>
                        <p class="small">Pour vos premiers trajets</p>
                    </article>
                    <article class="col-md-4">
                        <i class="fas fa-leaf text-success fa-2x mb-2" aria-hidden="true"></i>
                        <h3 class="h6 mb-0"><strong>Impact écologique</strong></h3>
                        <p class="small">Réduisez votre empreinte carbone</p>
                    </article>
                    <article class="col-md-4">
                        <i class="fas fa-users text-primary fa-2x mb-2" aria-hidden="true"></i>
                        <h3 class="h6 mb-0"><strong>Communauté</strong></h3>
                        <p class="small">Rencontrez des éco-citoyens</p>
                    </article>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php
$content = ob_get_clean();

$jsFiles = ['/js/inscription.js'];

require __DIR__ . '/../layouts/main.php';
?>
