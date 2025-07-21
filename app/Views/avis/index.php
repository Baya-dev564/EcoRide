<?php
/**
 * - Liste des avis EcoRide
 * Affiche tous les avis avec une interface responsive Bootstrap
 */

// Vérification que les variables sont définies
$pageTitle = $pageTitle ?? "Avis des utilisateurs - EcoRide";
$avis = $avis ?? [];
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
    <link rel="stylesheet" href="/ecoride/public/css/avis.css">
</head>
<body>
    <!-- En-tête principal de la page -->
    <!-- En-tête principal de la page -->
<header class="bg-success text-white py-3 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3 mb-0">
                    <i class="fas fa-star text-warning me-2" aria-hidden="true"></i>
                    Avis des utilisateurs
                </h1>
            </div>
            <div class="col-md-6 text-end">
                <!-- Bouton retour à l'accueil -->
                <a href="/EcoRide/public/" class="btn btn-light btn-sm me-2">
                    <i class="fas fa-home me-1" aria-hidden="true"></i>
                    Accueil
                </a>
                <!-- Bouton pour donner un avis -->
                <a href="/avis/create" class="btn btn-warning btn-sm">
                    <i class="fas fa-plus me-1" aria-hidden="true"></i>
                    Donner un avis
                </a>
            </div>
        </div>
    </div>
</header>


    <!-- Contenu principal -->
    <main class="container">
        <!-- Section des filtres -->
        <section class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5 mb-0">
                            <i class="fas fa-filter me-2" aria-hidden="true"></i>
                            Filtrer les avis
                        </h2>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" class="row g-3">
                            <!-- Filtre par note -->
                            <div class="col-md-4">
                                <label for="noteFilter" class="form-label">Note minimale</label>
                                <select id="noteFilter" class="form-select">
                                    <option value="">Toutes les notes</option>
                                    <option value="5">5 étoiles</option>
                                    <option value="4">4 étoiles et plus</option>
                                    <option value="3">3 étoiles et plus</option>
                                    <option value="2">2 étoiles et plus</option>
                                    <option value="1">1 étoile et plus</option>
                                </select>
                            </div>
                            
                            <!-- Filtre par date -->
                            <div class="col-md-4">
                                <label for="dateFilter" class="form-label">Période</label>
                                <select id="dateFilter" class="form-select">
                                    <option value="">Toutes les dates</option>
                                    <option value="week">Cette semaine</option>
                                    <option value="month">Ce mois</option>
                                    <option value="year">Cette année</option>
                                </select>
                            </div>
                            
                            <!-- Bouton de recherche -->
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1" aria-hidden="true"></i>
                                    Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section des avis -->
        <section class="row">
             <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4">
                        <i class="fas fa-comments me-2 text-primary" aria-hidden="true"></i>
                        Liste des avis
                        <span class="badge bg-secondary ms-2" id="avisCount">
                            <?= count($avis) ?> avis
                        </span>
                    </h2>
                    
                    <!-- Tri des avis -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-sort me-1" aria-hidden="true"></i>
                            Trier par
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-sort="date">Date récente</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="note">Note croissante</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="note-desc">Note décroissante</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Conteneur des avis -->
                <div id="avisContainer">
                    <?php if (empty($avis)): ?>
                        <!-- Message si aucun avis -->
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-info-circle fa-3x mb-3 text-muted"></i>
                            <h3 class="h5">Aucun avis disponible</h3>
                            <p class="text-muted">Soyez le premier à donner votre avis sur un trajet !</p>
                            <a href="/avis/create" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-1" aria-hidden="true"></i>
                                Donner un avis
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Boucle pour afficher chaque avis -->
                        <?php foreach ($avis as $index => $avis_item): ?>
                            <article class="card mb-4 avis-card" data-note="<?= $avis_item->getNoteGlobale() ?>">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Colonne gauche : Note et critères -->
                                        <div class="col-md-4">
                                            <div class="text-center mb-3">
                                                <!-- Note globale -->
                                                <div class="note-globale mb-2">
                                                   <span class="h2 text-primary"><?= $avis_item->getNoteGlobale() ?></span>
                                                    <span class="text-muted">/5</span>
                                                </div>
                                                
                                                <!-- Affichage étoiles -->
                                                <div class="stars mb-2">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $avis_item->getNoteGlobale() ? 'text-warning' : 'text-muted' ?>" 
                                                           aria-hidden="true"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Critères détaillés -->
                                            <div class="criteres">
                                                <h4 class="h6 mb-2">Critères détaillés :</h4>
                                                <?php foreach ($avis_item->getCriteres() as $critere => $note): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted"><?= ucfirst($critere) ?>:</small>
                                                        <div class="stars-small">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="fas fa-star <?= $i <= $note ? 'text-warning' : 'text-muted' ?>" 
                                                                   aria-hidden="true"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Colonne droite : Commentaire et informations -->
                                        <div class="col-md-8">
                                            <!-- En-tête de l'avis -->
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                  <h3 class="h6 mb-1">
                                                   <i class="fas fa-user me-1 text-secondary"></i>
                                                     <?= htmlspecialchars($avis_item->getPseudo() ?? ('#' . $avis_item->getUserId())) ?>
                                                   </h3>

                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1" aria-hidden="true"></i>
                                                        <?= date('d/m/Y à H:i', strtotime($avis_item->getDateCreation())) ?>
                                                    </small>
                                                </div>
                                                
                                                <!-- Statut de l'avis -->
                                                <span class="badge bg-<?= $avis_item->getStatut() === 'validé' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($avis_item->getStatut()) ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Commentaire -->
                                            <blockquote class="blockquote">
                                                <p class="mb-2"><?= htmlspecialchars($avis_item->getCommentaire()) ?></p>
                                            </blockquote>
                                            
                                            <!-- Tags -->
                                            <?php if (!empty($avis_item->getTags())): ?>
                                                <div class="tags mt-3">
                                                    <small class="text-muted me-2">Tags:</small>
                                                    <?php foreach ($avis_item->getTags() as $tag): ?>
                                                        <span class="badge bg-light text-dark me-1">
                                                            #<?= htmlspecialchars($tag) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Informations sur le trajet -->
                                            <div class="mt-3 pt-3 border-top">
                                                <small class="text-muted">
                                                    <i class="fas fa-route me-1" aria-hidden="true"></i>
                                                    Trajet #<?= $avis_item->getTrajetId() ?> - 
                                                    Conducteur #<?= $avis_item->getConducteurId() ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
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
</body>
</html>
