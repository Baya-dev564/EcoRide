<?php
/**
 * Vue des statistiques utilisateur - Administration EcoRide
 * Vue pure MVC - Pas de CSS/JS intégré
 */

// Sécurisation des données pour éviter les erreurs
$userData = $userData ?? [];
$userStats = $userStats ?? [];

// Je m'assure que toutes les valeurs existent avec des valeurs par défaut
$userData['id'] = $userData['id'] ?? 0;
$userData['pseudo'] = $userData['pseudo'] ?? 'Utilisateur';
$userData['prenom'] = $userData['prenom'] ?? '';
$userData['nom'] = $userData['nom'] ?? '';
$userData['email'] = $userData['email'] ?? '';
$userData['photo_profil'] = $userData['photo_profil'] ?? '';
$userData['credit'] = (int)($userData['credit'] ?? 0);
$userData['role'] = $userData['role'] ?? 'user';
$userData['created_at'] = $userData['created_at'] ?? date('Y-m-d');

// Statistiques sécurisées
$userStats['nb_trajets_proposes'] = (int)($userStats['nb_trajets_proposes'] ?? 0);
$userStats['nb_trajets_termines'] = (int)($userStats['nb_trajets_termines'] ?? 0);
$userStats['nb_reservations'] = (int)($userStats['nb_reservations'] ?? 0);
$userStats['nb_reservations_terminees'] = (int)($userStats['nb_reservations_terminees'] ?? 0);
$userStats['places_totales'] = (int)($userStats['places_totales'] ?? 0);
$userStats['distance_totale'] = (float)($userStats['distance_totale'] ?? 0.0);
$userStats['revenus_totaux'] = (float)($userStats['revenus_totaux'] ?? 0.0);
$userStats['credits_depenses'] = (int)($userStats['credits_depenses'] ?? 0);
$userStats['note_moyenne'] = (float)($userStats['note_moyenne'] ?? 5.0);
$userStats['taux_completion'] = (float)($userStats['taux_completion'] ?? 0.0);
$userStats['evolution'] = $userStats['evolution'] ?? ['trajets' => [], 'reservations' => []];
$userStats['nb_vehicules'] = (int)($userStats['nb_vehicules'] ?? 0);
$userStats['prix_moyen_km'] = (float)($userStats['prix_moyen_km'] ?? 0.0);

// Variables pour le layout
$title = "Statistiques de " . htmlspecialchars($userData['pseudo']) . " - Admin EcoRide";
$currentPage = 'utilisateurs';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Navigation admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">
                <i class="fas fa-shield-alt me-2"></i>
                Administration EcoRide
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/utilisateurs">
                            <i class="fas fa-users me-1"></i>
                            Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/avis">
                            <i class="fas fa-star me-1"></i>
                            Avis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/deconnexion">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-light py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-1">
                        <i class="fas fa-chart-line me-2"></i>
                        Statistiques de <?= htmlspecialchars($userData['pseudo']) ?>
                    </h1>
                    <p class="text-muted mb-2">Analyse détaillée des performances et activités</p>
                    
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="/admin/dashboard">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="/admin/utilisateurs">Utilisateurs</a>
                            </li>
                            <li class="breadcrumb-item active">Statistiques</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/admin/user-edit/<?= $userData['id'] ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>
                        Modifier
                    </a>
                    <a href="/admin/utilisateurs" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="container my-4">

        <!-- Profil utilisateur -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if (!empty($userData['photo_profil'])): ?>
                                <img src="<?= htmlspecialchars($userData['photo_profil']) ?>" 
                                     class="rounded-circle" 
                                     width="80" 
                                     height="80" 
                                     alt="Photo de <?= htmlspecialchars($userData['pseudo']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user-circle text-muted" style="font-size: 5rem;"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h2 class="h4 mb-1"><?= htmlspecialchars($userData['pseudo']) ?></h2>
                        
                        <?php if (!empty($userData['prenom']) || !empty($userData['nom'])): ?>
                            <p class="text-muted mb-3">
                                <?= htmlspecialchars(trim($userData['prenom'] . ' ' . $userData['nom'])) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark me-2">
                                <i class="fas fa-coins me-1"></i>
                                <?= $userData['credit'] ?> crédits
                            </span>
                            <span class="badge bg-info">
                                <i class="fas fa-shield-alt me-1"></i>
                                <?= ucfirst($userData['role']) ?>
                            </span>
                        </div>
                        
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Inscrit le <?= date('d/m/Y', strtotime($userData['created_at'])) ?>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="row g-3">
                    <!-- Note moyenne -->
                    <div class="col-md-6">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="h6 mb-1">Note Globale</h3>
                                    <div class="h4 mb-0">
                                        <?= number_format($userStats['note_moyenne'], 1) ?>/5
                                    </div>
                                </div>
                                <i class="fas fa-star fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Taux completion -->
                    <div class="col-md-6">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="h6 mb-1">Taux Réussite</h3>
                                    <div class="h4 mb-0">
                                        <?= number_format($userStats['taux_completion'], 1) ?>%
                                    </div>
                                </div>
                                <i class="fas fa-chart-line fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Véhicules -->
                    <div class="col-md-6">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="h6 mb-1">Véhicules</h3>
                                    <div class="h4 mb-0"><?= $userStats['nb_vehicules'] ?></div>
                                </div>
                                <i class="fas fa-car fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Prix moyen -->
                    <div class="col-md-6">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="h6 mb-1">Prix Moyen</h3>
                                    <div class="h4 mb-0">
                                        <?= number_format($userStats['prix_moyen_km'], 2) ?>€/km
                                    </div>
                                </div>
                                <i class="fas fa-euro-sign fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        <div class="row mb-4">
            <!-- Activité Conducteur -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0">
                            <i class="fas fa-car me-2"></i>
                            Activité Conducteur
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="h3 text-primary mb-1">
                                    <?= $userStats['nb_trajets_proposes'] ?>
                                </div>
                                <small class="text-muted">Trajets proposés</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-success mb-1">
                                    <?= $userStats['nb_trajets_termines'] ?>
                                </div>
                                <small class="text-muted">Trajets terminés</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-info mb-1">
                                    <?= number_format($userStats['distance_totale']) ?> km
                                </div>
                                <small class="text-muted">Distance totale</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-warning mb-1">
                                    <?= number_format($userStats['revenus_totaux'], 2) ?> €
                                </div>
                                <small class="text-muted">Revenus totaux</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activité Passager -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="h5 mb-0">
                            <i class="fas fa-users me-2"></i>
                            Activité Passager
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="h3 text-primary mb-1">
                                    <?= $userStats['nb_reservations'] ?>
                                </div>
                                <small class="text-muted">Réservations</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-success mb-1">
                                    <?= $userStats['nb_reservations_terminees'] ?>
                                </div>
                                <small class="text-muted">Terminées</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-warning mb-1">
                                    <?= $userStats['places_totales'] ?>
                                </div>
                                <small class="text-muted">Places totales</small>
                            </div>
                            <div class="col-6">
                                <div class="h3 text-info mb-1">
                                    <?= number_format($userStats['credits_depenses']) ?>
                                </div>
                                <small class="text-muted">Crédits dépensés</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique évolution -->
        <div class="card">
            <div class="card-header">
                <h3 class="h5 mb-0">
                    <i class="fas fa-chart-area me-2"></i>
                    Évolution de l'activité (12 derniers mois)
                </h3>
            </div>
            <div class="card-body">
                <canvas id="evolutionChart" width="400" height="200"></canvas>
            </div>
        </div>

    </div>

    <!-- Données pour Chart.js -->
    <script type="application/json" id="chart-data">
        <?= json_encode($userStats['evolution']) ?>
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

