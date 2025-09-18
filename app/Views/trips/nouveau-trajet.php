<?php
// app/Views/trips/nouveau-trajet.php
// Vue pour le formulaire de création d'un nouveau trajet 

ob_start();

// Inclusion du fichier JavaScript spécifique
$jsFiles = ['/public/js/nouveau-trajet.js'];
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

<!-- Messages d'erreur -->
<?php if (!empty($erreurs)): ?>
    <aside class="container mt-3" role="alert" aria-live="polite">
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-2 mt-1" aria-hidden="true"></i>
                <div>
                    <?php if (count($erreurs) === 1): ?>
                        <?= htmlspecialchars($erreurs[0]) ?>
                    <?php else: ?>
                        <h2 class="alert-heading h6 mb-2">Erreurs détectées :</h2>
                        <ul class="mb-0">
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?= htmlspecialchars($erreur) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer le message d'erreur"></button>
        </div>
    </aside>
<?php endif; ?>

<main class="container py-4" role="main">
    <!-- En-tête avec navigation -->
    <header class="row mb-4">
        <div class="col-12">
            <nav aria-label="Fil d'Ariane">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/EcoRide/public/">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="/EcoRide/public/mes-trajets">Mes trajets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nouveau trajet</li>
                </ol>
            </nav>
            <h1 class="text-success">
                <i class="fas fa-plus-circle me-2" aria-hidden="true"></i>Proposer un nouveau trajet
            </h1>
            <p class="text-muted">Partagez votre trajet et gagnez des crédits EcoRide</p>
        </div>
    </header>

    <div class="row">
        <!-- Formulaire principal -->
        <section class="col-lg-8" aria-labelledby="form-titre">
            <article class="card border-0 shadow-sm">
                <header class="card-header bg-success text-white">
                    <h2 id="form-titre" class="mb-0">
                        <i class="fas fa-route me-2" aria-hidden="true"></i>Informations du trajet
                    </h2>
                </header>
                
                <div class="card-body">
                    <form id="formNouveauTrajet" method="POST" action="/EcoRide/public/nouveau-trajet" role="form" aria-labelledby="form-titre">
                        
                        <!-- Section itinéraire -->
                        <fieldset class="row mb-4">
                            <legend class="col-12">
                                <h3 class="text-success mb-3">
                                    <i class="fas fa-map-marked-alt me-2" aria-hidden="true"></i>Itinéraire
                                </h3>
                            </legend>
                            
                            <div class="col-md-6 mb-3">
                                <label for="lieu_depart" class="form-label">Lieu de départ *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lieu_depart" 
                                       name="lieu_depart" 
                                       value="<?= htmlspecialchars($donnees['lieu_depart'] ?? '') ?>" 
                                       placeholder="Paris, Lyon, Marseille..." 
                                       required
                                       aria-describedby="depart-help">
                                <div id="depart-help" class="form-text">Ville ou adresse de départ</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="code_postal_depart" class="form-label">Code postal départ *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="code_postal_depart" 
                                       name="code_postal_depart" 
                                       value="<?= htmlspecialchars($donnees['code_postal_depart'] ?? '') ?>" 
                                       placeholder="75001" 
                                       maxlength="5" 
                                       pattern="\d{5}"
                                       required
                                       aria-describedby="cp-depart-help">
                                <div id="cp-depart-help" class="form-text">Code postal français (5 chiffres)</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="lieu_arrivee" class="form-label">Lieu d'arrivée *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lieu_arrivee" 
                                       name="lieu_arrivee" 
                                       value="<?= htmlspecialchars($donnees['lieu_arrivee'] ?? '') ?>" 
                                       placeholder="Paris, Lyon, Marseille..." 
                                       required
                                       aria-describedby="arrivee-help">
                                <div id="arrivee-help" class="form-text">Ville ou adresse d'arrivée</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="code_postal_arrivee" class="form-label">Code postal arrivée *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="code_postal_arrivee" 
                                       name="code_postal_arrivee" 
                                       value="<?= htmlspecialchars($donnees['code_postal_arrivee'] ?? '') ?>" 
                                       placeholder="69001" 
                                       maxlength="5" 
                                       pattern="\d{5}"
                                       required
                                       aria-describedby="cp-arrivee-help">
                                <div id="cp-arrivee-help" class="form-text">Code postal français (5 chiffres)</div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </fieldset>

                        <!-- Section date et heure -->
                        <fieldset class="row mb-4">
                            <legend class="col-12">
                                <h3 class="text-success mb-3">
                                    <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>Planning
                                </h3>
                            </legend>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date_depart" class="form-label">Date de départ *</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date_depart" 
                                       name="date_depart" 
                                       value="<?= htmlspecialchars($donnees['date_depart'] ?? '') ?>" 
                                       min="<?= date('Y-m-d') ?>" 
                                       required
                                       aria-describedby="date-help">
                                <div id="date-help" class="form-text">Date de départ souhaitée</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="heure_depart" class="form-label">Heure de départ *</label>
                                <input type="time" 
                                       class="form-control" 
                                       id="heure_depart" 
                                       name="heure_depart" 
                                       value="<?= htmlspecialchars($donnees['heure_depart'] ?? '') ?>" 
                                       required
                                       aria-describedby="heure-help">
                                <div id="heure-help" class="form-text">Heure de départ prévue</div>
                                <div class="2invalid-feedback"></div>
                            </div>
                        </fieldset>

                        <!-- Section véhicule et places -->
<fieldset class="row mb-4">
    <legend class="col-12">
        <h3 class="text-success mb-3">
            <i class="fas fa-car me-2" aria-hidden="true"></i>Véhicule et places
        </h3>
    </legend>
    
    <div class="col-md-6 mb-3">
        <label for="places" class="form-label">Nombre de places disponibles *</label>
        <select class="form-select" 
                id="places" 
                name="places" 
                required
                aria-describedby="places-help">
            <option value="">Choisir...</option>
            <?php for($i = 1; $i <= 8; $i++): ?>
                <option value="<?= $i ?>" <?= ($donnees['places'] ?? '') == $i ? 'selected' : '' ?>>
                    <?= $i ?> place<?= $i > 1 ? 's' : '' ?>
                </option>
            <?php endfor; ?>
        </select>
        <div id="places-help" class="form-text">Places disponibles pour les passagers</div>
        <div class="invalid-feedback"></div>
    </div>
    
    <div class="col-md-6 mb-3">
        <label for="vehicule_id" class="form-label">Véhicule (optionnel)</label>
        <select class="form-select" 
                id="vehicule_id" 
                name="vehicule_id"
                aria-describedby="vehicule-help">
            <option value="">Sélectionner un véhicule...</option>
            <?php if (!empty($vehicules) && is_array($vehicules)): ?>
                <?php foreach ($vehicules as $vehicule): ?>
                    <option value="<?= htmlspecialchars($vehicule['id']) ?>" 
                            data-electrique="<?= $vehicule['electrique'] ? 'true' : 'false' ?>"
                            <?= ($donnees['vehicule_id'] ?? '') == $vehicule['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($vehicule['marque']) ?> <?= htmlspecialchars($vehicule['modele']) ?>
                        <?= $vehicule['electrique'] ? ' 🌱' : '' ?>
                        (<?= htmlspecialchars($vehicule['plaque_immatriculation']) ?>)
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="" disabled>Aucun véhicule enregistré</option>
            <?php endif; ?>
        </select>
        <div id="vehicule-help" class="form-text">
            <?php if (empty($vehicules)): ?>
                <a href="/EcoRide/public/profil" class="text-decoration-none">
                    <i class="fas fa-plus me-1" aria-hidden="true"></i>
                    Ajoutez vos véhicules dans votre profil
                </a>
            <?php else: ?>
                Sélectionnez le véhicule pour ce trajet (<?= count($vehicules) ?> véhicule<?= count($vehicules) > 1 ? 's' : '' ?> disponible<?= count($vehicules) > 1 ? 's' : '' ?>)
            <?php endif; ?>
        </div>
    </div>
</fieldset>

                        <!-- Section options écologiques -->
                        <fieldset class="row mb-4">
                            <legend class="col-12">
                                <h3 class="text-success mb-3">
                                    <i class="fas fa-leaf me-2" aria-hidden="true"></i>Options écologiques
                                </h3>
                            </legend>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="vehicule_electrique" 
                                           name="vehicule_electrique" 
                                           value="1" 
                                           <?= isset($donnees['vehicule_electrique']) && $donnees['vehicule_electrique'] ? 'checked' : '' ?>
                                           aria-describedby="electrique-help">
                                    <label class="form-check-label" for="vehicule_electrique">
                                        <i class="fas fa-leaf text-success me-1" aria-hidden="true"></i>
                                        Véhicule électrique ou hybride
                                    </label>
                                    <div id="electrique-help" class="form-text">Réduction automatique du prix pour encourager l'éco-mobilité</div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Section commentaire -->
                        <fieldset class="mb-4">
                            <legend>
                                <h3 class="text-success mb-3">
                                    <i class="fas fa-comment me-2" aria-hidden="true"></i>Informations complémentaires
                                </h3>
                            </legend>
                            
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                <textarea class="form-control" 
                                          id="commentaire" 
                                          name="commentaire" 
                                          rows="3" 
                                          maxlength="500"
                                          placeholder="Informations supplémentaires pour les passagers..."
                                          aria-describedby="commentaire-help"><?= htmlspecialchars($donnees['commentaire'] ?? '') ?></textarea>
                                <div id="commentaire-help" class="form-text">Précisez les conditions particulières, points de rendez-vous, etc. (max 500 caractères)</div>
                            </div>
                        </fieldset>

                        <!-- Actions du formulaire -->
                        <footer class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/EcoRide/public/mes-trajets" class="btn btn-outline-secondary">
                               <i class="fas fa-arrow-left me-2" aria-hidden="true"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2" aria-hidden="true"></i>Créer le trajet
                            </button>
                        </footer>
                    </form>
                </div>2
            </article>
        </section>

        <!-- Informations et aide -->
        <aside class="col-lg-4">
            <!-- Prix automatique -->
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="prix-titre">
                <header class="card-header bg-light">
                    <h2 id="prix-titre" class="h6 mb-0">
                        <i class="fas fa-calculator text-warning me-2" aria-hidden="true"></i>Prix calculé automatiquement
                    </h2>
                </header>
                <div class="card-body">
                    <div class="text-center">
                        <div class="display-6 text-success mb-2" id="prix-estime" aria-live="polite">
                            <i class="fas fa-coins" aria-hidden="true"></i> --
                        </div>
                        <p class="text-muted mb-0">crédits par passager</p>
                        <small class="text-muted">Basé sur la distance et le type de véhicule</small>
                    </div>
                </div>
            </section>

            <!-- Conseils -->
            <section class="card border-0 shadow-sm mb-4" aria-labelledby="conseils-titre">
                <header class="card-header bg-light">
                    <h2 id="conseils-titre" class="h6 mb-0">
                        <i class="fas fa-lightbulb text-warning me-2" aria-hidden="true"></i>Conseils EcoRide
                    </h2>
                </header>
                 <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2" aria-hidden="true"></i>
                            <small>Proposez des horaires flexibles</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2" aria-hidden="true"></i>
                            <small>Précisez le point de rendez-vous</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2" aria-hidden="true"></i>
                            <small>Mentionnez si vous acceptez les bagages</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-leaf text-success me-2" aria-hidden="true"></i>
                            <small>Les véhicules électriques sont mis en avant</small>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Impact écologique -->
            <section class="card border-0 shadow-sm" aria-labelledby="impact-titre">
                <header class="card-header bg-light">
                    <h2 id="impact-titre" class="h6 mb-0">
                        <i class="fas fa-tree text-success me-2" aria-hidden="true"></i>Impact écologique
                    </h2>
                </header>
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-leaf fa-2x" aria-hidden="true"></i>
                    </div>
                    <p class="mb-2">En partageant votre trajet, vous contribuez à :</p>
                    <ul class="list-unstyled text-muted small">
                        <li>• Réduction des émissions CO₂</li>
                        <li>• Diminution du trafic routier</li>
                        <li>• Économies d'énergie</li>
                        <li>• Création de liens sociaux</li>
                    </ul>
                </div>
            </section>
        </aside>
    </div>
</main>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
