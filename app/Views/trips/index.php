<?php
/**
 * Vue de recherche et affichage des trajets EcoRide
 * 
 * Interface complète avec formulaire de recherche, filtres avancés,
 * affichage des résultats et pagination - Compatible avec MVC corrigé
 * 
 * @author Équipe EcoRide - TP Développement Web
 * @version 4.0 - Harmonisation complète avec modèle/contrôleur corrigés
 */

ob_start();
?>

<!-- Hero Section avec formulaire de recherche ACCESSIBLE -->
<!-- Section recherche avec background image -->
<section class="search-hero-section py-5" aria-labelledby="search-titre">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h1 id="search-titre" class="display-4 fw-bold mb-3">
                    <i class="fas fa-seedling me-2" aria-hidden="true"></i>
                    Trouvez votre covoiturage écologique
                </h1>
                <p class="lead">
                    Voyagez responsable, économisez et rencontrez de nouvelles personnes
                </p>
            </div>
        </div>
        
        <!-- Formulaire de recherche avec fond semi-transparent -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <!-- Votre formulaire de recherche existant -->
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="lieu_depart" class="form-label">
                                    <i class="fas fa-map-marker-alt text-success me-1"></i>
                                    Départ
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lieu_depart" 
                                       name="lieu_depart" 
                                       placeholder="Ville ou code postal"
                                       value="<?= htmlspecialchars($_GET['lieu_depart'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="lieu_arrivee" class="form-label">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    Arrivée
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="lieu_arrivee" 
                                       name="lieu_arrivee" 
                                       placeholder="Ville ou code postal"
                                       value="<?= htmlspecialchars($_GET['lieu_arrivee'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="date_depart" class="form-label">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>
                                    Date
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date_depart" 
                                       name="date_depart" 
                                       value="<?= htmlspecialchars($_GET['date_depart'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label d-block">
                                    <i class="fas fa-leaf text-success me-1"></i>
                                    Filtres
                                </label>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-search me-2"></i>Rechercher
                                </button>
                            </div>
                        </form>
                        
                        <!-- Filtres avancés -->
                        <div class="mt-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="vehicule_electrique" 
                                       name="vehicule_electrique" 
                                       value="1"
                                       <?= isset($_GET['vehicule_electrique']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="vehicule_electrique">
                                    <i class="fas fa-car text-success me-1"></i>
                                    Véhicules électriques uniquement
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Section des résultats -->
<section class="results-section py-5" aria-labelledby="titre-resultats">
    <div class="container">
        
        <!-- En-tête des résultats -->
        <?php if (!empty($hasSearch)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2 class="h4 mb-1" id="titre-resultats">
                                <i class="fas fa-route text-success" aria-hidden="true"></i> 
                                Résultats de recherche
                            </h2>
                            <!-- ✅ CORRECTION : Gestion sécurisée des stats -->
                            <p class="text-muted mb-0" aria-live="polite" aria-atomic="true">
                                <span class="sr-only">Nombre de trajets trouvés : </span>
                                <?= ($stats['total_trajets'] ?? 0) ?> trajet(s) trouvé(s)
                                <?php if (($stats['trajets_electriques'] ?? 0) > 0): ?>
                                    <span class="sr-only">, dont </span>• <?= $stats['trajets_electriques'] ?> écologique(s) 🌱
                                <?php endif; ?>
                                <?php if (($stats['total_trajets'] ?? 0) > 0): ?>
                                    <span class="sr-only">, prix moyen : </span>• Prix moyen : <?= $stats['prix_moyen'] ?? 0 ?> crédits
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <!-- ✅ CORRECTION : Tri dynamique maintenant fonctionnel -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    aria-label="Options de tri des résultats">
                                <i class="fas fa-sort" aria-hidden="true"></i> 
                                Trier par
                                <?php
                                // Affichage du tri actuel
                                $triActuel = $criteres['tri'] ?? 'date_depart';
                                $trisLabels = [
                                    'date_depart' => 'Date',
                                    'prix' => 'Prix',
                                    'note' => 'Note',
                                    'ecologique' => 'Écologique'
                                ];
                                echo ' (' . ($trisLabels[$triActuel] ?? 'Date') . ')';
                                ?>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? 'date_depart') == 'date_depart' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'date_depart', 'direction' => 'ASC'])) ?>" 
                                       role="menuitem">
                                        <i class="fas fa-clock" aria-hidden="true"></i> Date (plus tôt)
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? '') == 'prix' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'prix', 'direction' => 'ASC'])) ?>" 
                                       role="menuitem">
                                        <i class="fas fa-coins" aria-hidden="true"></i> Prix (moins cher)
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? '') == 'note' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'note', 'direction' => 'DESC'])) ?>" 
                                       role="menuitem">
                                        <i class="fas fa-star" aria-hidden="true"></i> Note conducteur
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? '') == 'ecologique' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'ecologique'])) ?>" 
                                       role="menuitem">
                                        <i class="fas fa-leaf" aria-hidden="true"></i> Écologique d'abord
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Affichage des trajets -->
        <?php if (!empty($trajets)): ?>
            <div class="row">
                <?php foreach ($trajets as $trajet): ?>
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <article class="card trip-card h-100 shadow-sm border-0 hover-shadow" 
                                 role="article"
                                 aria-labelledby="trajet-titre-<?= $trajet['id'] ?>"
                                 aria-describedby="trajet-description-<?= $trajet['id'] ?>">
                            <div class="card-body p-4">
                                
                                <!-- En-tête du trajet -->
                                <header class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="trip-route flex-grow-1">
                                        <h3 class="card-title mb-1 fw-bold h5" id="trajet-titre-<?= $trajet['id'] ?>">
                                            <span class="sr-only">Trajet de covoiturage de </span>
                                            <?= htmlspecialchars($trajet['lieu_depart']) ?>
                                            <i class="fas fa-arrow-right text-success mx-2" aria-hidden="true"></i>
                                            <?= htmlspecialchars($trajet['lieu_arrivee']) ?>
                                        </h3>
                                        <p class="text-muted small mb-0" id="trajet-description-<?= $trajet['id'] ?>">
                                            <span class="sr-only">Distance : </span>
                                            <i class="fas fa-route" aria-hidden="true"></i> <?= $trajet['distance_estimee'] ?? 'N/A' ?>
                                            <span class="sr-only">, Durée estimée : </span>
                                            • <i class="fas fa-clock" aria-hidden="true"></i> <?= $trajet['duree_estimee'] ?? 'N/A' ?>
                                        </p>
                                    </div>
                                    
                                    <!-- ✅ CORRECTION : Badge véhicule électrique harmonisé -->
                                    <?php if (!empty($trajet['vehicule_electrique'])): ?>
                                        <span class="badge bg-success" role="img" aria-label="Véhicule électrique, trajet écologique">
                                            <i class="fas fa-leaf" aria-hidden="true"></i> Électrique
                                        </span>
                                    <?php endif; ?>
                                </header>

                                <!-- Informations du trajet -->
                                <div class="trip-info mb-3">
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <span class="sr-only">Date de départ : </span>
                                            <i class="fas fa-calendar text-success" aria-hidden="true"></i>
                                            <?= $trajet['date_depart_formatee'] ?? 'N/A' ?>
                                        </div>
                                        <div class="col-6">
                                            <span class="sr-only">Places disponibles : </span>
                                            <i class="fas fa-users text-success" aria-hidden="true"></i>
                                            <?= $trajet['places_disponibles'] ?? 0 ?> place(s) libre(s)
                                        </div>
                                    </div>
                                </div>

                                <!-- Conducteur -->
                                <section class="driver-info d-flex align-items-center mb-3" aria-labelledby="conducteur-<?= $trajet['id'] ?>">
                                    <h4 class="sr-only" id="conducteur-<?= $trajet['id'] ?>">Informations du conducteur</h4>
                                    
                                    <div class="driver-avatar me-3">
                                        <?php if (!empty($trajet['conducteur_photo'])): ?>
                                            <img src="<?= htmlspecialchars($trajet['conducteur_photo']) ?>" 
                                                 class="rounded-circle" 
                                                 width="40" 
                                                 height="40" 
                                                 alt="Photo de profil de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>, conducteur du trajet">
                                        <?php else: ?>
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;"
                                                 role="img"
                                                 aria-label="Avatar par défaut de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>">
                                                <i class="fas fa-user text-white" aria-hidden="true"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- ✅ MODIFICATION : Section conducteur avec liens avis intégrés -->
                                    <div class="driver-details flex-grow-1">
                                        <div class="fw-semibold"><?= htmlspecialchars($trajet['conducteur_pseudo']) ?></div>
                                        <div class="text-muted small">
                                            <?php if (($trajet['conducteur_note'] ?? 0) > 0): ?>
                                                <span class="rating" role="img" aria-label="Note du conducteur : <?= number_format($trajet['conducteur_note'], 1) ?> étoiles sur 5">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= floor($trajet['conducteur_note'])): ?>
                                                            <i class="fas fa-star text-warning" aria-hidden="true"></i>
                                                        <?php elseif ($i - 0.5 <= $trajet['conducteur_note']): ?>
                                                            <i class="fas fa-star-half-alt text-warning" aria-hidden="true"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star text-warning" aria-hidden="true"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </span>
                                                <span class="ms-1" aria-hidden="true"><?= number_format($trajet['conducteur_note'], 1) ?></span>
                                                
                                                <!-- ✅ MODIFICATION : Lien vers les avis intégré -->
                                                <a href="/avis/conducteur/<?= $trajet['conducteur_id'] ?>" 
                                                   class="text-warning text-decoration-none ms-2"
                                                   aria-label="Voir tous les avis de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                    <span class="small">Voir avis</span>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Nouveau conducteur</span>
                                                
                                                <!-- ✅ MODIFICATION : Lien pour nouveau conducteur -->
                                                <a href="/avis/conducteur/<?= $trajet['conducteur_id'] ?>" 
                                                   class="text-muted text-decoration-none ms-2"
                                                   aria-label="Voir le profil de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>">
                                                    <i class="fas fa-user" aria-hidden="true"></i>
                                                    <span class="small">Profil</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </section>

                                <!-- Véhicule -->
                                <div class="vehicle-info mb-3 p-2 bg-light rounded">
                                    <div class="small">
                                        <span class="sr-only">Véhicule : </span>
                                        <i class="fas fa-car text-success" aria-hidden="true"></i>
                                        <strong><?= htmlspecialchars($trajet['vehicule_marque'] ?? 'N/A') ?> <?= htmlspecialchars($trajet['vehicule_modele'] ?? '') ?></strong>
                                        <?php if (!empty($trajet['vehicule_couleur'])): ?>
                                            • <?= htmlspecialchars($trajet['vehicule_couleur']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Impact écologique -->
                                <div class="eco-impact mb-3">
                                    <div class="small text-success">
                                        <span class="sr-only">Impact écologique : </span>
                                        <i class="fas fa-seedling" aria-hidden="true"></i>
                                        <strong><?= $trajet['co2_economise'] ?? 'N/A' ?> de CO₂ économisés</strong>
                                        en partageant ce trajet
                                    </div>
                                </div>

                            </div>

                            <!-- Pied de carte avec prix et action -->
                            <footer class="card-footer bg-white border-0 p-4 pt-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price">
                                        <span class="h4 text-success fw-bold mb-0" aria-label="Prix du trajet : <?= $trajet['prix'] ?> crédits">
                                            <?= $trajet['prix'] ?>
                                            <small class="text-muted">crédits</small>
                                        </span>
                                        <?php if (!empty($trajet['vehicule_electrique'])): ?>
                                            <div class="small text-success">
                                                <i class="fas fa-tag" aria-hidden="true"></i> Prix réduit écologique
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="actions">
                                        <a href="/EcoRide/public/trajet/<?= $trajet['id'] ?>" 
                                           class="btn btn-success"
                                           aria-label="Voir les détails du trajet de <?= htmlspecialchars($trajet['lieu_depart']) ?> à <?= htmlspecialchars($trajet['lieu_arrivee']) ?>">
                                            <i class="fas fa-eye" aria-hidden="true"></i> 
                                            <span>Voir détails</span>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Indicateur de disponibilité -->
                                <?php if (!empty($trajet['presque_complet'])): ?>
                                    <div class="mt-2">
                                        <small class="text-warning" role="alert">
                                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                                            Plus que <?= $trajet['places_disponibles'] ?> place(s) !
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </footer>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ✅ CORRECTION : Pagination sécurisée -->
            <?php if (!empty($pagination) && ($pagination['total_pages'] ?? 0) > 1): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Navigation des pages de trajets">
                            <ul class="pagination justify-content-center">
                                
                                <!-- Page précédente -->
                                <?php if (($pagination['page_actuelle'] ?? 1) > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?= http_build_query(array_merge($criteres, ['page' => $pagination['page_actuelle'] - 1])) ?>"
                                           aria-label="Aller à la page précédente">
                                            <i class="fas fa-chevron-left" aria-hidden="true"></i> Précédent
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Numéros de pages -->
                                <?php 
                                $pageActuelle = $pagination['page_actuelle'] ?? 1;
                                $totalPages = $pagination['total_pages'] ?? 1;
                                for ($i = max(1, $pageActuelle - 2); $i <= min($totalPages, $pageActuelle + 2); $i++): 
                                ?>
                                    <li class="page-item <?= $i == $pageActuelle ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?<?= http_build_query(array_merge($criteres, ['page' => $i])) ?>"
                                           aria-label="<?= $i == $pageActuelle ? 'Page actuelle, page ' . $i : 'Aller à la page ' . $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Page suivante -->
                                <?php if ($pageActuelle < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" 
                                           href="?<?= http_build_query(array_merge($criteres, ['page' => $pageActuelle + 1])) ?>"
                                           aria-label="Aller à la page suivante">
                                            Suivant <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <!-- Informations de pagination -->
                        <div class="text-center text-muted mt-3" aria-live="polite">
                            Page <?= $pageActuelle ?> sur <?= $totalPages ?>
                            (<?= $pagination['total_trajets'] ?? 0 ?> trajet(s) au total)
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif (!empty($hasSearch)): ?>
            <!-- Aucun résultat trouvé -->
            <div class="row">
                <div class="col-12 text-center py-5">
                    <div class="no-results" role="region" aria-labelledby="titre-aucun-resultat">
                        <i class="fas fa-search fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h3 class="text-muted" id="titre-aucun-resultat">Aucun trajet trouvé</h3>
                        <p class="text-muted mb-4">
                            Essayez de modifier vos critères de recherche ou 
                            <a href="/EcoRide/public/trajets" class="text-success">voir tous les trajets disponibles</a>
                        </p>
                        
                        <!-- Suggestions -->
                        <div class="suggestions">
                            <h4 class="text-muted mb-3">Suggestions :</h4>
                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                <a href="?lieu_depart=Paris&lieu_arrivee=Lyon" class="btn btn-outline-success btn-sm">
                                    Paris → Lyon
                                </a>
                                <a href="?lieu_depart=Marseille&lieu_arrivee=Nice" class="btn btn-outline-success btn-sm">
                                    Marseille → Nice
                                </a>
                                <a href="?vehicule_electrique=1" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-leaf" aria-hidden="true"></i> Trajets écologiques
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Page d'accueil des trajets (sans recherche) -->
            <div class="row">
                <div class="col-12 text-center py-5">
                    <h2 class="mb-4">🚗 Découvrez nos trajets de covoiturage</h2>
                    <p class="lead text-muted mb-4">
                        Utilisez le formulaire ci-dessus pour rechercher un trajet ou explorez nos suggestions populaires
                    </p>
                    
                    <!-- Trajets populaires -->
                    <div class="popular-routes">
                        <h3 class="mb-3">Trajets populaires</h3>
                        <div class="d-flex justify-content-center flex-wrap gap-3">
                            <a href="?lieu_depart=Paris&lieu_arrivee=Lyon" class="btn btn-outline-success">
                                <i class="fas fa-route" aria-hidden="true"></i> Paris → Lyon
                            </a>
                            <a href="?lieu_depart=Marseille&lieu_arrivee=Nice" class="btn btn-outline-success">
                                <i class="fas fa-route" aria-hidden="true"></i> Marseille → Nice
                            </a>
                            <a href="?lieu_depart=Toulouse&lieu_arrivee=Bordeaux" class="btn btn-outline-success">
                                <i class="fas fa-route" aria-hidden="true"></i> Toulouse → Bordeaux
                            </a>
                            <a href="?vehicule_electrique=1" class="btn btn-success">
                                <i class="fas fa-leaf" aria-hidden="true"></i> Trajets écologiques
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
$cssFiles = ['css/trips.css', 'css/accessibility.css'];
$jsFiles = ['js/trips.js'];
require __DIR__ . '/../layouts/main.php';
?>
