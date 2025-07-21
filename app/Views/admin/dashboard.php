<?php
/**
 * Vue dashboard administrateur EcoRide
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS personnalisé pour l'admin -->
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <!-- Navigation principale admin -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar" role="navigation">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">
                <i class="fas fa-shield-alt me-2" aria-hidden="true"></i>
                <span>Administration EcoRide</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="fas fa-user me-1" aria-hidden="true"></i>
                            <?= htmlspecialchars($admin['pseudo']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/EcoRide/public/">
                            <i class="fas fa-home me-1" aria-hidden="true"></i>
                            Retour au site
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/EcoRide/public/deconnexion">
                            <i class="fas fa-sign-out-alt me-1" aria-hidden="true"></i>
                            Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- En-tête de page -->
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="admin-title">
                        <i class="fas fa-tachometer-alt me-2" aria-hidden="true"></i>
                        Tableau de bord
                    </h1>
                    <p class="admin-subtitle">
                        Gestion et 8supervision de la plateforme EcoRide
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="admin-date">
                        <i class="fas fa-calendar me-1" aria-hidden="true"></i>
                        <?= date('d/m/Y à H:i') ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="admin-main">
        <div class="container">
            
            <!-- Section des statistiques -->
            <section class="statistics-section mb-5">
                <h2 class="section-title">
                    <i class="fas fa-chart-line me-2" aria-hidden="true"></i>
                    Statistiques de la plateforme
                </h2>
                
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <article class="stat-card stat-card-primary">
                            <div class="stat-card-body">
                                <div class="stat-content">
                                    <h3 class="stat-number"><?= $stats['total_users'] ?></h3>
                                    <p class="stat-label">Utilisateurs</p>
                                    <small class="stat-detail">
                                        <?= $stats['users_actifs'] ?> actifs (30j)
                                    </small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users" aria-hidden="true"></i>
                                </div>
                            </div>
                        </article>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4">
                        <article class="stat-card stat-card-success">
                            <div class="stat-card-body">
                                <div class="stat-content">
                                    <h3 class="stat-number"><?= $stats['total_trajets'] ?></h3>
                                    <p class="stat-label">Trajets</p>
                                    <small class="stat-detail">proposés</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-route" aria-hidden="true"></i>
                                </div>
                            </div>
                        </article>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4">
                        <article class="stat-card stat-card-info">
                            <div class="stat-card-body">
                                <div class="stat-content">
                                    <h3 class="stat-number"><?= $stats['total_reservations'] ?></h3>
                                    <p class="stat-label">Réservations</p>
                                    <small class="stat-detail">effectuées</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-calendar-check" aria-hidden="true"></i>
                                </div>
                            </div>
                        </article>
                    </div>
                    
                    <div class="col-lg-3 col-md-6 mb-4">
                        <article class="stat-card stat-card-warning">
                            <div class="stat-card-body">
                                <div class="stat-content">
                                    <h3 class="stat-number"><?= $stats['total_avis'] ?></h3>
                                    <p class="stat-label">Avis</p>
                                    <small class="stat-detail">publiés</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-star" aria-hidden="true"></i>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- Section des actions -->
            <section class="actions-section">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <article class="action-card">
                            <header class="action-card-header">
                                <h3 class="action-card-title">
                                    <i class="fas fa-cogs me-2" aria-hidden="true"></i>
                                    Actions administrateur
                                </h3>
                            </header>
                            <div class="action-card-body">
                                <nav class="admin-actions">
                                    <ul class="actions-list">
                                        <li class="action-item">
                                            <a href="/admin/utilisateurs" class="action-link action-link-primary">
                                                <div class="action-icon">
                                                    <i class="fas fa-users" aria-hidden="true"></i>
                                                </div>
                                                <div class="action-content">
                                                    <h4 class="action-title">Gérer les utilisateurs</h4>
                                                    <p class="action-desc">Voir et modifier les comptes utilisateurs</p>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="action-item">
                                            <a href="/admin/avis" class="action-link action-link-warning">
                                                <div class="action-icon">
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                </div>
                                                <div class="action-content">
                                                    <h4 class="action-title">Modérer les avis</h4>
                                                    <p class="action-desc">Gérer les avis et commentaires</p>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="action-item">
                                            <a href="/EcoRide/public/avis" class="action-link action-link-info">
                                                <div class="action-icon">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                </div>
                                                <div class="action-content">
                                                    <h4 class="action-title">Voir tous les avis</h4>
                                                    <p class="action-desc">Consulter les avis publiés</p>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </article>2
                    </div>
                    
                    <div class="col-lg-6 mb-4">
                        <article class="summary-card">
                            <header class="summary-card-header">
                                <h3 class="summary-card-title">
                                    <i class="fas fa-chart-pie me-2" aria-hidden="true"></i>
                                    Résumé de la plateforme
                                </h3>
                            </header>
                            <div class="summary-card-body">
                                <div class="summary-stats">
                                    <div class="summary-item">
                                        <div class="summary-label">Crédits totaux</div>
                                        <div class="summary-value summary-value-success">
                                            <?= number_format($stats['credits_total']) ?>
                                        </div>
                                        <div class="summary-detail">en circulation</div>
                                    </div>
                                    <div class="summary-item">
                                        <div class="summary-label">Taux d'activité</div>
                                        <div class="summary-value summary-value-info">
                                            <?= $stats['total_users'] > 0 ? round(($stats['users_actifs'] / $stats['total_users']) * 100) : 0 ?>%
                                        </div>
                                        <div class="summary-detail">utilisateurs actifs</div>
                                    </div>
                                </div>
                                <div class="system-status">
                                    <div class="status-indicator status-online">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                                        <span>Système EcoRide opérationnel</span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="admin-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="footer-text">
                        <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                        Administration EcoRide
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="footer-text">
                        <i class="fas fa-clock me-1" aria-hidden="true"></i>
                        Dernière mise à jour : <?= date('d/m/Y à H:i') ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé pour l'admin -->
    <script src="/js/admin.js"></script>
</body>
</html>
