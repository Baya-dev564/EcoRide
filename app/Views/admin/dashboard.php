<?php
$title = "Dashboard Admin | EcoRide - Administration";
$isAdminPage = true;
$cssFiles = ['/css/admin.css'];
$jsFiles = ['/js/admin.js'];

ob_start();
?>

<!-- En-tête -->
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h1 class="h2 text-primary">
            <i class="fas fa-tachometer-alt me-2"></i>
            Dashboard Administrateur
        </h1>
        <p class="text-muted">Supervision de la plateforme EcoRide</p>
    </div>
    <div class="col-md-4 text-end">
        <span class="badge bg-success fs-6 p-2">Système actif</span>
    </div>
</div>

<!-- Métriques principales -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="text-primary"><?= $dashboardData['total_utilisateurs'] ?></h3>
                        <p class="text-muted mb-0">Utilisateurs</p>
                        <small class="text-success"><?= $dashboardData['utilisateurs_actifs'] ?> actifs</small>
                    </div>
                    <i class="fas fa-users fa-3x text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="text-success"><?= $dashboardData['total_trajets'] ?></h3>
                        <p class="text-muted mb-0">Trajets</p>
                        <small class="text-warning"><?= $dashboardData['trajets_en_attente'] ?> en attente</small>
                    </div>
                    <i class="fas fa-route fa-3x text-success opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="text-info"><?= $dashboardData['total_reservations'] ?></h3>
                        <p class="text-muted mb-0">Réservations</p>
                        <small class="text-success"><?= $dashboardData['reservations_confirmees'] ?> confirmées</small>
                    </div>
                    <i class="fas fa-calendar-check fa-3x text-info opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="text-warning"><?= $dashboardData['credits_totaux'] ?></h3>
                        <p class="text-muted mb-0">Crédits totaux</p>
                        <small class="text-info">
                            <?php if (!empty($dashboardData['detail_credits'])): ?>
                                <?php foreach($dashboardData['detail_credits'] as $i => $user): ?>
                                    <?= $user['pseudo'] ?>: <?= $user['credit'] ?>€<?= $i < count($dashboardData['detail_credits'])-1 ? ', ' : '' ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <i class="fas fa-coins fa-3x text-warning opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Actions administrateur</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="/admin/utilisateurs" class="btn btn-outline-primary w-100 p-4">
                            <i class="fas fa-users fa-2x d-block mb-2"></i>
                            Utilisateurs<br><small>(<?= $dashboardData['total_utilisateurs'] ?>)</small>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/admin/trajets" class="btn btn-outline-success w-100 p-4 position-relative">
                            <i class="fas fa-route fa-2x d-block mb-2"></i>
                            Trajets
                            <?php if($dashboardData['trajets_en_attente'] > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $dashboardData['trajets_en_attente'] ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="/admin/avis" class="btn btn-outline-warning w-100 p-4">
                            <i class="fas fa-star fa-2x d-block mb-2"></i>
                            Avis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>État du système</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Base de données</span>
                    <span class="badge bg-success">Connectée</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Serveur web</span>
                    <span class="badge bg-success">Opérationnel</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>MongoDB</span>
                    <span class="badge bg-success">Actif</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
