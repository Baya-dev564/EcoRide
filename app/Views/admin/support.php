<?php
$title = "Aide Administration | EcoRide - Support";
$isAdminPage = true;
$cssFiles = ['/css/admin.css'];
$jsFiles = ['/js/admin.js'];

ob_start();
?>

<!-- En-tête Support Admin SIMPLIFIÉ -->
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h1 class="h2 text-primary">
            <i class="fas fa-question-circle me-2"></i>
            Aide & Documentation Admin
        </h1>
        <p class="text-muted">Guide d'utilisation de l'interface d'administration EcoRide</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="/admin/dashboard" class="btn btn-admin-primary">
            <i class="fas fa-arrow-left me-1"></i>
            Retour au dashboard
        </a>
    </div>
</div>


<!-- Guides d'utilisation -->
<div class="row g-4">
    <?php foreach($guidesAdmin as $section => $guide): ?>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header <?= $section === 'utilisateurs' ? 'bg-primary' : ($section === 'trajets' ? 'bg-success' : 'bg-danger') ?> text-white">
                <h5 class="mb-0">
                    <i class="fas <?= $section === 'utilisateurs' ? 'fa-users' : ($section === 'trajets' ? 'fa-route' : 'fa-cogs') ?> me-2"></i>
                    <?= $guide['titre'] ?>
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Procédures courantes :</p>
                <ul class="list-unstyled">
                    <?php foreach($guide['actions'] as $action): ?>
                    <li class="mb-2">
                        <i class="fas fa-chevron-right text-primary me-2"></i>
                        <?= htmlspecialchars($action) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="text-center mt-3">
                    <a href="/admin/<?= $section === 'technique' ? 'dashboard' : $section ?>" class="btn btn-sm btn-outline-<?= $section === 'utilisateurs' ? 'primary' : ($section === 'trajets' ? 'success' : 'danger') ?>">
                        <i class="fas fa-arrow-right me-1"></i>
                        Accéder à la section
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- FAQ Technique -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Questions fréquentes - Administration
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAdmin">
                    
                    <!-- FAQ 1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-user-slash me-2 text-danger"></i>
                                Comment suspendre un utilisateur ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <ol class="mb-0">
                                            <li>Aller dans <strong>Utilisateurs</strong></li>
                                            <li>Trouver l'utilisateur problématique</li>
                                            <li>Cliquer sur le bouton <span class="badge bg-danger">Suspendre</span></li>
                                            <li>Confirmer l'action dans la popup</li>
                                        </ol>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-danger small">
                                            <strong>⚠️ Attention :</strong> L'utilisateur ne pourra plus se connecter.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-check-circle me-2 text-success"></i>
                                Comment valider un trajet en attente ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <ol class="mb-3">
                                    <li>Aller dans <strong>Trajets</strong></li>
                                    <li>Filtrer par <span class="badge bg-warning">En attente</span></li>
                                    <li>Vérifier les informations du trajet</li>
                                    <li>Cliquer sur <span class="badge bg-success">Valider</span></li>
                                </ol>
                                <div class="alert alert-info small">
                                    <strong>💡 Conseil :</strong> Vérifier que les adresses de départ/arrivée sont cohérentes.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-coins me-2 text-warning"></i>
                                Comment ajuster les crédits d'un utilisateur ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Procédure :</h6>
                                        <ol>
                                            <li>Aller dans <strong>Utilisateurs</strong></li>
                                            <li>Cliquer sur l'utilisateur</li>
                                            <li>Modifier le champ "Crédit"</li>
                                            <li>Sauvegarder les modifications</li>
                                        </ol>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Cas d'usage :</h6>
                                        <ul class="small">
                                            <li>Remboursement suite à un problème</li>
                                            <li>Bonus pour fidélité</li>
                                            <li>Correction d'erreur de calcul</li>
                                            <li>Sanction (retrait de crédits)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actions d'urgence -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Actions d'urgence
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <button class="btn btn-danger w-100" onclick="alert('Feature à développer')">
                            <i class="fas fa-ban d-block mb-1"></i>
                            Suspendre tous les trajets
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-warning w-100" onclick="alert('Feature à développer')">
                            <i class="fas fa-sync d-block mb-1"></i>
                            Vider le cache
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info w-100" onclick="alert('Feature à développer')">
                            <i class="fas fa-database d-block mb-1"></i>
                            Sauvegarder BDD
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="mailto:tech@ecoride.fr" class="btn btn-secondary w-100">
                            <i class="fas fa-envelope d-block mb-1"></i>
                            Contacter le dev
                        </a>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Ces actions sont réservées aux situations critiques
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Raccourcis -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="raccourcisOffcanvas">
    <div class="offcanvas-header">
        <h5>Raccourcis clavier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="list-group">
            <?php foreach($raccourcis as $raccourci => $action): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <small class="text-muted"><?= htmlspecialchars($action) ?></small>
                    <kbd class="small"><?= htmlspecialchars($raccourci) ?></kbd>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-3 p-3 bg-light rounded">
            <small class="text-muted">
                <i class="fas fa-lightbulb me-1"></i>
                Utilise ces raccourcis pour naviguer plus rapidement dans l'interface admin.
            </small>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
