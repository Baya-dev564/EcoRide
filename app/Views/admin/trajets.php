<?php
// Configuration de la vue
$title = "Gestion des trajets | Admin EcoRide";
$isAdminPage = true;
$cssFiles = ['/css/admin.css'];
$jsFiles = ['/js/admin.js', '/js/admin-trajets.js'];

// Variables par défaut
$trajetsEnAttente = $trajetsEnAttente ?? [];
$stats = $stats ?? ['en_attente' => 0, 'valides' => 0, 'refuses' => 0, 'total' => 0];

ob_start();
?>

<!-- Navigation breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-light p-3 rounded">
        <li class="breadcrumb-item">
            <a href="/admin/dashboard" class="text-decoration-none">
                <i class="fas fa-tachometer-alt me-1"></i>
                Dashboard
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <i class="fas fa-route me-1"></i>
            Modération des trajets
        </li>
    </ol>
</nav>

<!-- En-tête -->
<header class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="page-title">
                <i class="fas fa-route text-success me-2"></i>
                Modération des trajets
            </h1>
            <p class="page-subtitle text-muted mb-0">
                Gérez et modérez tous les trajets de la plateforme EcoRide
            </p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-outline-success" id="refreshStats">
                <i class="fas fa-sync-alt me-1"></i>
                <span>Actualiser</span>
            </button>
        </div>
    </div>
</header>

<!-- Statistiques -->
<section class="stats-section mb-4">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-warning h-100">
                <div class="card-body">
                    <div class="stat-content">
                        <div class="stat-text">
                            <h2 class="stat-title">En attente</h2>
                            <div class="stat-value"><?= $stats['en_attente'] ?></div>
                            <p class="stat-description mb-0">À modérer</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-success h-100">
                <div class="card-body">
                    <div class="stat-content">
                        <div class="stat-text">
                            <h2 class="stat-title">Validés</h2>
                            <div class="stat-value"><?= $stats['valides'] ?></div>
                            <p class="stat-description mb-0">Actifs</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-danger h-100">
                <div class="card-body">
                    <div class="stat-content">
                        <div class="stat-text">
                            <h2 class="stat-title">Refusés</h2>
                            <div class="stat-value"><?= $stats['refuses'] ?></div>
                            <p class="stat-description mb-0">Non conformes</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-info h-100">
                <div class="card-body">
                    <div class="stat-content">
                        <div class="stat-text">
                            <h2 class="stat-title">Total</h2>
                            <div class="stat-value"><?= $stats['total'] ?></div>
                            <p class="stat-description mb-0">Tous trajets</p>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Liste des trajets -->
<section class="trajets-section">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">
                <i class="fas fa-list me-2"></i>
                Liste des trajets (<?= count($trajetsEnAttente) ?>)
            </h2>
        </div>
        
        <div class="card-body p-0">
            <?php if (empty($trajetsEnAttente)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-route empty-icon text-muted mb-3" style="font-size: 4rem;"></i>
                    <h3 class="h4">Aucun trajet trouvé</h3>
                    <p class="text-muted">Aucun trajet n'a été créé sur la plateforme.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Trajet</th>
                                <th scope="col">Conducteur</th>
                                <th scope="col">Date/Heure</th>
                                <th scope="col">Places/Prix</th>
                                <th scope="col">Statut</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trajetsEnAttente as $trajet): ?>
                                <tr data-trajet-id="<?= $trajet['id'] ?>">
                                    <td>
                                        <div class="trajet-info">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-map-marker-alt text-success me-2"></i>
                                                <strong><?= htmlspecialchars($trajet['lieu_depart']) ?></strong>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                <strong><?= htmlspecialchars($trajet['lieu_arrivee']) ?></strong>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div>
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($trajet['conducteur_pseudo'] ?? 'Inconnu') ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($trajet['conducteur_email'] ?? '') ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <div>
                                            <div class="fw-semibold">
                                                <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($trajet['heure_depart'])) ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div>
                                            <span class="badge bg-info mb-1">
                                                <i class="fas fa-users me-1"></i>
                                                <?= $trajet['places'] ?> places
                                            </span>
                                            <div class="fw-bold text-success">
                                                <?= number_format($trajet['prix'], 2) ?>€
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <?php
                                        $statutActuel = $trajet['statut_moderation'] ?? 'en_attente';
                                        $statutClass = $statutActuel === 'valide' ? 'bg-success' : 
                                                      ($statutActuel === 'refuse' ? 'bg-danger' : 'bg-warning text-dark');
                                        $statutLabel = $statutActuel === 'valide' ? 'Validé' : 
                                                      ($statutActuel === 'refuse' ? 'Refusé' : 'En attente');
                                        ?>
                                        <span class="badge <?= $statutClass ?>" id="statut-<?= $trajet['id'] ?>">
                                            <?= $statutLabel ?>
                                        </span>
                                    </td>

                                    <td>
                                        <!-- ✅ BOUTONS AJAX AVEC data-trajet-id -->
                                        <div class="btn-group btn-group-sm" id="actions-<?= $trajet['id'] ?>">
                                            <?php if ($trajet['statut_moderation'] !== 'valide'): ?>
                                                <button type="button" 
                                                        class="btn btn-success btn-sm btn-valider" 
                                                        data-trajet-id="<?= $trajet['id'] ?>"
                                                        title="Valider le trajet">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($trajet['statut_moderation'] !== 'refuse'): ?>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm btn-refuser" 
                                                        data-trajet-id="<?= $trajet['id'] ?>"
                                                        title="Refuser le trajet">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="button" 
                                                    class="btn btn-info btn-sm btn-details" 
                                                    data-trajet-id="<?= $trajet['id'] ?>"
                                                    title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ✅ ZONE POUR AFFICHER LES NOTIFICATIONS -->
<div id="alertContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

<!-- ✅ MODAL DÉTAILS TRAJET -->
<div class="modal fade" id="trajetDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Détails du trajet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="trajetDetailsContent">
                <!-- Contenu chargé dynamiquement -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="modalActions">
                    <!-- Boutons d'action ajoutés dynamiquement -->
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
