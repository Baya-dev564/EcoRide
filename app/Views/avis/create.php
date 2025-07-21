<?php
/**
 *  Formulaire de création d'avis EcoRide
 * Interface pour permettre aux utilisateurs de donner leur avis
 */

// Vérification que les variables sont définies avec des valeurs par défaut en chaîne vide
$pageTitle = $pageTitle ?? "Donner un avis - EcoRide";
$trajet_id = $trajet_id ?? '';
$conducteur_id = $conducteur_id ?? '';
$errors = $errors ?? [];
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
    
    <!-- CSS spécifique au formulaire -->
    <link rel="stylesheet" href="/css/form-avis.css">
</head>
<body>
    <!-- En-tête principal de la page -->
    <header class="bg-success text-white py-3 mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-star-half-alt text-warning me-2" aria-hidden="true"></i>
                        Donner votre avis
                    </h1>
                    <p class="mb-0 small">Partagez votre expérience de covoiturage</p>
                </div>
                <div class="col-md-4 text-end">
                    <!-- Bouton retour -->
               <a href="/avis" class="btn btn-light btn-sm">
    <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>
    Retour aux avis
              </a>

                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <!-- Affichage des erreurs -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Erreurs dans le formulaire
                        </h4>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulaire principal -->
                <form id="avisForm"   class="needs-validation" novalidate>
                    <!-- Champs cachés -->
                    <input type="hidden" name="trajet_id" value="<?= htmlspecialchars($trajet_id) ?>">
                    <input type="hidden" name="conducteur_id" value="<?= htmlspecialchars($conducteur_id) ?>">
                    
                    <!-- Carte du formulaire -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h2 class="h5 mb-0">
                                <i class="fas fa-edit me-2 text-primary"></i>
                                Votre évaluation
                            </h2>
                        </div>
                        
                        <div class="card-body">
                            
                            <!-- Section Note globale -->
                            <section class="mb-4">
                                <h3 class="h6 mb-3">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    Note globale *
                                </h3>
                                
                                <div class="star-rating-container text-center mb-3">
                                    <div class="star-rating" data-rating="0">
                                        <input type="hidden" name="note_globale" id="note_globale" value="0" required>
                                        <span class="star" data-rating="1">
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <span class="star" data-rating="2">
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <span class="star" data-rating="3">
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <span class="star" data-rating="4">
                                            <i class="fas fa-star"></i>
                                        </span>
                                        <span class="star" data-rating="5">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0">
                                        Cliquez sur les étoiles pour noter
                                    </p>
                                </div>
                                
                                <div class="invalid-feedback">
                                    Veuillez sélectionner une note entre 1 et 5 étoiles.
                                </div>
                            </section>
                            
                            <!-- Section Critères détaillés -->
                            <section class="mb-4">
                                <h3 class="h6 mb-3">
                                    <i class="fas fa-list-ul text-info me-2"></i>
                                    Critères détaillés *
                                </h3>
                                
                                <div class="row">
                                    <!-- Ponctualité -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-clock text-primary me-2"></i>
                                            Ponctualité
                                        </label>
                                        <div class="star-rating-critere" data-critere="ponctualite">
                                            <input type="hidden" name="ponctualite" value="0" required>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star-small" data-rating="<?= $i ?>">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Conduite -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-car text-success me-2"></i>
                                            Conduite
                                        </label>
                                        <div class="star-rating-critere" data-critere="conduite">
                                            <input type="hidden" name="conduite" value="0" required>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star-small" data-rating="<?= $i ?>">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Propreté -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-sparkles text-warning me-2"></i>
                                            Propreté
                                        </label>
                                        <div class="star-rating-critere" data-critere="proprete">
                                            <input type="hidden" name="proprete" value="0" required>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star-small" data-rating="<?= $i ?>">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Ambiance -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label d-flex align-items-center">
                                            <i class="fas fa-smile text-danger me-2"></i>
                                            Ambiance
                                        </label>
                                        <div class="star-rating-critere" data-critere="ambiance">
                                            <input type="hidden" name="ambiance" value="0" required>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star-small" data-rating="<?= $i ?>">
                                                    <i class="fas fa-star"></i>
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            
                            <!-- Section Commentaire -->
                            <section class="mb-4">
                                <h3 class="h6 mb-3">
                                    <i class="fas fa-comment text-secondary me-2"></i>
                                    Votre commentaire *
                                </h3>
                                
                                <div class="form-floating">
                                    <textarea 
                                        class="form-control" 
                                        id="commentaire" 
                                        name="commentaire" 
                                        placeholder="Décrivez votre expérience..."
                                        style="height: 120px"
                                        required
                                        minlength="10"
                                        maxlength="500"
                                    ><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                                    <label for="commentaire">Décrivez votre expérience de covoiturage</label>
                                    <div class="form-text">
                                        <span id="charCount">0</span>/500 caractères (minimum 10)
                                    </div>
                                    <div class="invalid-feedback">
                                        Le commentaire doit contenir entre 10 et 500 caractères.
                                    </div>
                                </div>
                            </section>
                            
                            <!-- Section Tags -->
                            <section class="mb-4">
                                <h3 class="h6 mb-3">
                                    <i class="fas fa-tags text-info me-2"></i>
                                    Tags (optionnel)
                                </h3>
                                
                                <div class="mb-3">
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="tags" 
                                        name="tags" 
                                        placeholder="Ex: sympa, ponctuel, musique..."
                                        value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>"
                                    >
                                    <div class="form-text">
                                        Séparez les tags par des virgules. Ex: sympa, ponctuel, bonne musique
                                    </div>
                                </div>
                                
                                <!-- Tags suggérés -->
                                <div class="suggested-tags">
                                    <small class="text-muted me-2">Tags suggérés:</small>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="sympa">sympa</span>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="ponctuel">ponctuel</span>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="propre">propre</span>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="sécuritaire">sécuritaire</span>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="bavard">bavard</span>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="silencieux">silencieux</span>
                                    <span class="badge bg-light text-dark me-1 suggested-tag" data-tag="musique">musique</span>
                                </div>
                            </section>
                            
                            <!-- Informations sur le trajet -->
                            <section class="mb-4">
                                <div class="alert alert-info">
                                    <h4 class="alert-heading h6">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Informations sur le trajet
                                    </h4>
                                    <p class="mb-1">
                                        <strong>Trajet:</strong> <?= !empty($trajet_id) ? '#' . htmlspecialchars($trajet_id) : 'Non spécifié' ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Conducteur:</strong> <?= !empty($conducteur_id) ? '#' . htmlspecialchars($conducteur_id) : 'Non spécifié' ?>
                                    </p>
                                </div>
                            </section>
                            
                        </div>
                        
                        <!-- Pied de formulaire -->
                        <div class="card-footer bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="/ecoride/avis" class="btn btn-secondary w-100">
                                        <i class="fas fa-times me-1"></i>
                                        Annuler
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success w-100" id="submitBtn">
                                        <i class="fas fa-paper-plane me-1"></i>
                                        Publier l'avis
                                    </button>
                                    
                                </div>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Votre avis sera modéré avant publication
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>
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
    
    <!-- JavaScript spécifique au formulaire -->
   <script src="/js/form-avis.js"></script>

</body>
</html>
