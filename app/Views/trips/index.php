<?php
/**
 * Vue de recherche et affichage des trajets EcoRide avec autocomplete de lieux
 */

ob_start();
?>




<!-- Hero Section avec formulaire de recherche intelligent -->
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
        
        <!-- Formulaire de recherche avec autocomplete intelligent -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg">
                    <div class="card-body p-4">
                        <form method="GET" class="row g-3" id="searchForm">
                            
                            <!-- Point de départ avec autocomplete -->
                            <div class="col-md-3">
                                <label for="lieu_depart" class="form-label">
                                    <i class="fas fa-map-marker-alt text-success me-1"></i>
                                    Départ
                                </label>
                                <input type="text" 
                                       class="form-control autocomplete-place" 
                                       id="lieu_depart" 
                                       name="lieu_depart" 
                                       placeholder="Rechercher un lieu..."
                                       value="<?= htmlspecialchars($_GET['lieu_depart'] ?? '') ?>">
                                       
                                <!-- Je stocke les coordonnées GPS pour la recherche -->
                                <input type="hidden" id="search_depart_lat" name="depart_lat" value="<?= htmlspecialchars($_GET['depart_lat'] ?? '') ?>">
                                <input type="hidden" id="search_depart_lng" name="depart_lng" value="<?= htmlspecialchars($_GET['depart_lng'] ?? '') ?>">
                                
                                <div class="form-text">
                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                    Gare, parking, centre commercial...
                                </div>
                            </div>
                            
                            <!-- Point d'arrivée avec autocomplete -->
                            <div class="col-md-3">
                                <label for="lieu_arrivee" class="form-label">
                                    <i class="fas fa-flag-checkered text-danger me-1"></i>
                                    Arrivée
                                </label>
                                <input type="text" 
                                       class="form-control autocomplete-place" 
                                       id="lieu_arrivee" 
                                       name="lieu_arrivee" 
                                       placeholder="Rechercher un lieu..."
                                       value="<?= htmlspecialchars($_GET['lieu_arrivee'] ?? '') ?>">
                                       
                                <!-- Je stocke les coordonnées GPS pour la recherche -->
                                <input type="hidden" id="search_arrivee_lat" name="arrivee_lat" value="<?= htmlspecialchars($_GET['arrivee_lat'] ?? '') ?>">
                                <input type="hidden" id="search_arrivee_lng" name="arrivee_lng" value="<?= htmlspecialchars($_GET['arrivee_lng'] ?? '') ?>">
                                
                                <div class="form-text">
                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                    Gare, parking, centre commercial...
                                </div>
                            </div>
                            
                            <!-- Date de recherche -->
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
                                <div class="form-text">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    Optionnel
                                </div>
                            </div>
                            
                            <!-- Bouton de recherche -->
                            <div class="col-md-3">
                                <label class="form-label d-block">
                                    <i class="fas fa-search text-success me-1"></i>
                                    Recherche
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
                                    <i class="fas fa-leaf text-success me-1"></i>
                                    Véhicules électriques uniquement
                                </label>
                            </div>
                            
                            <!-- Rayon de recherche -->
                            <div class="form-check form-check-inline ms-3">
                                <label for="rayon" class="form-label me-2">
                                    <i class="fas fa-crosshairs text-info me-1"></i>
                                    Rayon: 
                                </label>
                                <select class="form-select form-select-sm d-inline-block w-auto" id="rayon" name="rayon">
                                    <option value="10" <?= ($_GET['rayon'] ?? '10') == '10' ? 'selected' : '' ?>>10 km</option>
                                    <option value="25" <?= ($_GET['rayon'] ?? '') == '25' ? 'selected' : '' ?>>25 km</option>
                                    <option value="50" <?= ($_GET['rayon'] ?? '') == '50' ? 'selected' : '' ?>>50 km</option>
                                    <option value="100" <?= ($_GET['rayon'] ?? '') == '100' ? 'selected' : '' ?>>100 km</option>
                                </select>
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
                            <p class="text-muted mb-0" aria-live="polite" aria-atomic="true">
                                <span class="sr-only">Nombre de trajets trouvés : </span>
                                <?= ($stats['total_trajets'] ?? 0) ?> trajet(s) trouvé(s)
                                <?php if (($stats['trajets_electriques'] ?? 0) > 0): ?>
                                    <span class="sr-only">, dont </span>• <?= $stats['trajets_electriques'] ?> écologique(s) 
                                <?php endif; ?>
                                <?php if (($stats['total_trajets'] ?? 0) > 0): ?>
                                    <span class="sr-only">, prix moyen : </span>• Prix moyen : <?= $stats['prix_moyen'] ?? 0 ?> crédits
                                <?php endif; ?>
                            </p>
                            
                            <!-- Indicateur recherche par points de rendez-vous -->
                            <?php if (!empty($_GET['depart_lat']) || !empty($_GET['arrivee_lat'])): ?>
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Recherche par points de rendez-vous
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Tri dynamique -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" 
                                    type="button" 
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                <i class="fas fa-sort" aria-hidden="true"></i> 
                                Trier par
                                <?php
                                $triActuel = $criteres['tri'] ?? 'date_depart';
                                $trisLabels = [
                                    'date_depart' => 'Date',
                                    'prix' => 'Prix',
                                    'note' => 'Note',
                                    'ecologique' => 'Écologique',
                                    'distance' => 'Distance'
                                ];
                                echo ' (' . ($trisLabels[$triActuel] ?? 'Date') . ')';
                                ?>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? 'date_depart') == 'date_depart' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'date_depart', 'direction' => 'ASC'])) ?>">
                                        <i class="fas fa-clock" aria-hidden="true"></i> Date (plus tôt)
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? '') == 'prix' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'prix', 'direction' => 'ASC'])) ?>">
                                        <i class="fas fa-coins" aria-hidden="true"></i> Prix (moins cher)
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? '') == 'distance' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'distance'])) ?>">
                                        <i class="fas fa-route" aria-hidden="true"></i> Distance
                                    </a>
                                </li>
                                <li role="none">
                                    <a class="dropdown-item <?= ($criteres['tri'] ?? '') == 'ecologique' ? 'active' : '' ?>" 
                                       href="?<?= http_build_query(array_merge($criteres, ['tri' => 'ecologique'])) ?>">
                                        <i class="fas fa-leaf" aria-hidden="true"></i> Écologique d'abord
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Affichage des trajets (garde ton code existant) -->
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
                                <section class="driver-info d-flex align-items-center mb-3">
                                    <div class="driver-avatar me-3">
                                        <?php if (!empty($trajet['conducteur_photo'])): ?>
                                            <img src="<?= htmlspecialchars($trajet['conducteur_photo']) ?>" 
                                                 class="rounded-circle" 
                                                 width="40" 
                                                 height="40" 
                                                 alt="Photo de profil de <?= htmlspecialchars($trajet['conducteur_pseudo']) ?>">
                                        <?php else: ?>
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white" aria-hidden="true"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="driver-details flex-grow-1">
                                        <div class="fw-semibold"><?= htmlspecialchars($trajet['conducteur_pseudo']) ?></div>
                                        <div class="text-muted small">
                                            <?php if (($trajet['conducteur_note'] ?? 0) > 0): ?>
                                                <span class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star text-warning" aria-hidden="true"></i>
                                                    <?php endfor; ?>
                                                </span>
                                                <?= number_format($trajet['conducteur_note'], 1) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Nouveau conducteur</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </section>

                                <!-- Véhicule -->
                                <div class="vehicle-info mb-3 p-2 bg-light rounded">
                                    <div class="small">
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

            <!-- Pagination (garde ton code existant) -->
            <?php if (!empty($pagination) && ($pagination['total_pages'] ?? 0) > 1): ?>
                <!-- Ton code pagination actuel -->
            <?php endif; ?>

        <?php elseif (!empty($hasSearch)): ?>
            <!-- Aucun résultat trouvé (garde ton code existant) -->
            
        <?php else: ?>
            <!-- Page d'accueil des trajets (garde ton code existant) -->
        <?php endif; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
$cssFiles = ['css/trips.css', 'css/accessibility.css'];
$jsFiles = ['js/places-autocomplete.js', 'js/nouveau-trajet.js'];


require __DIR__ . '/../layouts/main.php';
?>
