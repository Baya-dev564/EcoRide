<?php
/**
 * Vue gestion des avis administrateur EcoRide
 * Interface complète de modération des avis MongoDB
 */

// Configuration des variables pour le layout
$title = "Gestion des avis | Admin EcoRide";
$isAdminPage = true;
$cssFiles = ['/css/admin.css'];
$jsFiles = ['/js/admin.js', '/js/admin-avis.js'];

// Début de la capture du contenu
ob_start();
?>

<!-- En-tête de la gestion des avis -->
<header class="admin-page-header">
    <div class="container-fluid">
        <div class="header-content">
            <div class="header-info">
                <h1 class="page-title">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    Gestion des avis
                </h1>
                <p class="page-subtitle">
                    Modération et administration des avis utilisateur MongoDB
                </p>
            </div>
            <div class="header-actions">
                <div class="stats-summary">
                    <div class="stat-item">
                        <span class="stat-number"><?= count($avis) ?></span>
                        <span class="stat-label">Avis total</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?= count(array_filter($avis, function($a) { return $a['statut'] === 'actif'; })) ?>
                        </span>
                        <span class="stat-label">Actifs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Section filtres et recherche -->
<section class="filters-section" aria-labelledby="filters-title">
    <div class="container-fluid">
        <div class="filters-container">
            <h2 id="filters-title" class="section-title">
                <i class="fas fa-filter" aria-hidden="true"></i>
                Filtres de recherche
            </h2>
            
            <form class="filters-form" id="avisFiltersForm" method="GET">
                <div class="filters-grid">
                    <!-- Filtre par note -->
                    <div class="filter-group">
                        <label for="filterNote" class="filter-label">Note</label>
                        <select id="filterNote" name="note" class="form-select filter-select">
                            <option value="">Toutes les notes</option>
                            <option value="5" <?= ($_GET['note'] ?? '') === '5' ? 'selected' : '' ?>>
                                ⭐⭐⭐⭐⭐ (5 étoiles)
                            </option>
                            <option value="4" <?= ($_GET['note'] ?? '') === '4' ? 'selected' : '' ?>>
                                ⭐⭐⭐⭐ (4 étoiles)
                            </option>
                            <option value="3" <?= ($_GET['note'] ?? '') === '3' ? 'selected' : '' ?>>
                                ⭐⭐⭐ (3 étoiles)
                            </option>
                            <option value="2" <?= ($_GET['note'] ?? '') === '2' ? 'selected' : '' ?>>
                                ⭐⭐ (2 étoiles)
                            </option>
                            <option value="1" <?= ($_GET['note'] ?? '') === '1' ? 'selected' : '' ?>>
                                ⭐ (1 étoile)
                            </option>
                        </select>
                    </div>
                    
                    <!-- Filtre par statut -->
                    <div class="filter-group">
                        <label for="filterStatut" class="filter-label">Statut</label>
                        <select id="filterStatut" name="statut" class="form-select filter-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif" <?= ($_GET['statut'] ?? '') === 'actif' ? 'selected' : '' ?>>
                                🟢 Actif
                            </option>
                            <option value="masque" <?= ($_GET['statut'] ?? '') === 'masque' ? 'selected' : '' ?>>
                                🟡 Masqué
                            </option>
                            <option value="signale" <?= ($_GET['statut'] ?? '') === 'signale' ? 'selected' : '' ?>>
                                🟠 Signalé
                            </option>
                            <option value="supprime" <?= ($_GET['statut'] ?? '') === 'supprime' ? 'selected' : '' ?>>
                                🔴 Supprimé
                            </option>
                        </select>
                    </div>
                    
                    <!-- Actions filtres -->
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Filtrer
                        </button>
                        <a href="/admin/avis" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Section principale des avis -->
<section class="avis-management-section" aria-labelledby="avis-title">
    <div class="container-fluid">
        <h2 id="avis-title" class="section-title">
            <i class="fas fa-comments" aria-hidden="true"></i>
            Liste des avis
        </h2>
        
        <?php if (empty($avis)): ?>
            <!-- État vide -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-star" aria-hidden="true"></i>
                </div>
                <h3 class="empty-state-title">Aucun avis trouvé</h3>
                <p class="empty-state-description">
                    Aucun avis ne correspond aux critères de recherche actuels.
                </p>
            </div>
        <?php else: ?>
            <!-- Grille des avis -->
            <div class="avis-grid" role="list">
                <?php foreach ($avis as $avisItem): ?>
                    <article class="avis-card" data-avis-id="<?= htmlspecialchars($avisItem['id']) ?>" role="listitem">
                        <!-- En-tête de l'avis -->
                        <header class="avis-card-header">
                            <div class="avis-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= ($avisItem['note'] ?? 0) ? 'star-filled' : 'star-empty' ?>">
                                        ⭐
                                    </span>
                                <?php endfor; ?>
                                <span class="rating-number">(<?= $avisItem['note'] ?? 0 ?>/5)</span>
                            </div>
                            
                            <div class="avis-status">
                                <span class="status-badge status-badge--<?= $avisItem['statut'] ?? 'actif' ?>">
                                    <?php 
                                    $statusIcons = [
                                        'actif' => '🟢',
                                        'masque' => '🟡', 
                                        'signale' => '🟠',
                                        'supprime' => '🔴'
                                    ];
                                    echo $statusIcons[$avisItem['statut'] ?? 'actif'] ?? '🟢';
                                    ?>
                                    <?= ucfirst($avisItem['statut'] ?? 'actif') ?>
                                </span>
                            </div>
                        </header>
                        
                        <!-- Contenu de l'avis -->
                        <div class="avis-card-content">
                            <div class="avis-meta">
                                <div class="avis-users">
                                    <div class="user-info">
                                        <strong>De :</strong> 
                                        <span class="username"><?= htmlspecialchars($avisItem['auteur_pseudo'] ?? 'Utilisateur inconnu') ?></span>
                                    </div>
                                    <div class="user-info">
                                        <strong>Pour :</strong> 
                                        <span class="username"><?= htmlspecialchars($avisItem['cible_pseudo'] ?? 'Utilisateur inconnu') ?></span>
                                    </div>
                                </div>
                                <div class="avis-date">
                                    <i class="fas fa-clock" aria-hidden="true"></i>
                                    <?php 
                                    $date = $avisItem['date_creation'] ?? null;
                                    echo $date ? date('d/m/Y à H:i', strtotime($date)) : 'Date inconnue';
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Commentaire -->
                            <div class="avis-comment">
                                <p class="comment-text">
                                    <?= htmlspecialchars($avisItem['commentaire'] ?? 'Aucun commentaire') ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Actions admin -->
                        <footer class="avis-card-actions">
                            <div class="action-buttons">
                                <!-- Modifier statut -->
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-success change-status-btn" 
                                            data-avis-id="<?= htmlspecialchars($avisItem['id']) ?>"
                                            data-status="actif"
                                            title="Marquer comme actif">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-warning change-status-btn" 
                                            data-avis-id="<?= htmlspecialchars($avisItem['id']) ?>"
                                            data-status="masque"
                                            title="Masquer l'avis">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-orange change-status-btn" 
                                            data-avis-id="<?= htmlspecialchars($avisItem['id']) ?>"
                                            data-status="signale"
                                            title="Marquer comme signalé">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </button>
                                </div>
                                
                                <!-- Supprimer -->
                                <button type="button" 
                                        class="btn btn-sm btn-danger delete-avis-btn" 
                                        data-avis-id="<?= htmlspecialchars($avisItem['id']) ?>"
                                        title="Supprimer définitivement">
                                    <i class="fas fa-trash"></i>
                                    Supprimer
                                </button>
                            </div>
                        </footer>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal de confirmation suppression -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <strong>Attention :</strong> Vous êtes sur le point de supprimer définitivement cet avis.
                </p>
                <p class="text-muted mb-0">
                    Cette action est irréversible et l'avis sera complètement effacé de la base de données MongoDB.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Annuler
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i>
                    Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Fin de la capture du contenu
$content = ob_get_clean();

// Inclusion du layout principal
require_once __DIR__ . '/../layouts/main.php';
?>
