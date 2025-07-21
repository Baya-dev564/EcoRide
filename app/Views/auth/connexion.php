<?php
// app/Views/auth/connexion.php
// Formulaire de connexion EcoRide 

ob_start();
?>

<main class="row justify-content-center" role="main">
    <div class="col-lg-5 col-md-7">
        <article class="card shadow-lg border-0">
            <div class="card-body p-5">
                <!-- En-tête du formulaire -->
                <header class="text-center mb-4">
                    <h1 class="text-success fw-bold">🔐 Connexion EcoRide</h1>
                    <p class="text-muted">Accédez à votre compte et vos crédits</p>
                </header>

                <!-- Message de succès (après inscription) -->
                <?php if (!empty($message)): ?>
                    <aside class="alert alert-success border-0 shadow-sm" role="alert" aria-live="polite">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                            <div><?= htmlspecialchars($message) ?></div>
                        </div>
                    </aside>
                <?php endif; ?>

                <!-- Message d'erreur -->
                <?php if (!empty($erreur)): ?>
                    <aside class="alert alert-danger border-0 shadow-sm" role="alert" aria-live="polite">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                            <div><?= htmlspecialchars($erreur) ?></div>
                        </div>
                    </aside>
                <?php endif; ?>

                <!-- Formulaire de connexion  avec AJAX -->
                  <form id="formConnexion"  action="/api/connexion"  method="POST"  novalidate role="form" aria-labelledby="connexion-titre">


                    <h2 id="connexion-titre" class="sr-only">Formulaire de connexion EcoRide</h2>
                    
                    <fieldset>
                        <legend class="sr-only">Identifiants de connexion</legend>
                        
                        <!-- Champ Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                <i class="fas fa-envelope text-success me-1" aria-hidden="true"></i>Adresse email
                            </label>
                            <input type="email" 
                                   class="form-control form-control-lg" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($email ?? '') ?>"
                                   required 
                                   placeholder="votre.email@exemple.com"
                                   autofocus
                                   aria-describedby="email-help">
                            <div id="email-help" class="sr-only">Saisissez l'adresse email de votre compte EcoRide</div>
                            <div class="invalid-feedback">
                                Veuillez saisir une adresse email valide.
                            </div>
                        </div>

                        <!-- Champ Mot de passe -->
                        <div class="mb-4">
                            <label for="mot_de_passe" class="form-label fw-semibold">
                                <i class="fas fa-lock text-success me-1" aria-hidden="true"></i>Mot de passe
                            </label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control form-control-lg" 
                                       id="mot_de_passe" 
                                       name="mot_de_passe" 
                                       required 
                                       placeholder="Votre mot de passe"
                                       aria-describedby="mdp-help">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Afficher ou masquer le mot de passe">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div id="mdp-help" class="sr-only">Saisissez le mot de passe de votre compte EcoRide</div>
                            <div class="invalid-feedback">
                                Le mot de passe est obligatoire.
                            </div>
                        </div>
                    </fieldset>

                    <!-- Actions du formulaire -->
                    <footer>
                        <!-- Bouton de connexion -->
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-success btn-lg py-3 fw-semibold">
                                <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>
                                Se connecter à EcoRide
                            </button>
                        </div>

                        <!-- Liens utiles -->
                        <nav class="text-center">
                            <p class="text-muted mb-2">
                                <a href="/EcoRide/public/mot-de-passe-oublie" class="text-success text-decoration-none">
                                    <i class="fas fa-key" aria-hidden="true"></i> Mot de passe oublié ?
                                </a>
                            </p>
                            <p class="text-muted mb-3">
                                Nouveau sur EcoRide ? 
                                <a href="/EcoRide/public/inscription" class="text-success text-decoration-none fw-semibold">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i> Créez votre compte
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
        
        <!-- Rappel des avantages -->
        <aside class="card mt-4 border-0 bg-light">
            <div class="card-body text-center py-3">
                <h2 class="h6 text-success mb-2">
                    <i class="fas fa-info-circle" aria-hidden="true"></i> Rappel
                </h2>
                <p class="small text-muted mb-0">
                    Votre compte EcoRide vous donne accès à vos crédits, vos trajets et votre historique de covoiturage écologique.
                </p>
            </div>
        </aside>
    </div>
</main>

<?php
$content = ob_get_clean();

$jsFiles = ['/js/connexion.js'];

require __DIR__ . '/../layouts/main.php';
?>
