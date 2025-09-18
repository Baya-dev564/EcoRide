<?php
// Configuration de la vue
$title = "Détails du trajet #" . $trajet['id'] . " | Admin EcoRide";
$isAdminPage = true;
$cssFiles = ['/css/admin.css'];
$jsFiles = ['/js/admin.js'];

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
        <li class="breadcrumb-item">
            <a href="/admin/trajets" class="text-decoration-none">
                <i class="fas fa-route me-1"></i>
                Trajets
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Détails du trajet #<?= $trajet['id'] ?>
        </li>
    </ol>
</nav>

<!-- En-tête avec actions -->
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h1 class="page-title">
            <i class="fas fa-eye text-info me-2"></i>
            Détails du trajet #<?= $trajet['id'] ?>
        </h1>
        <p class="text-muted mb-0">
            Informations complètes pour la modération
        </p>
    </div>
    <div class="col-md-4 text-end">
        <?php
        $statutActuel = $trajet['statut_moderation'] ?? 'en_attente';
        $statutClass = $statutActuel === 'valide' ? 'bg-success' : 
                      ($statutActuel === 'refuse' ? 'bg-danger' : 'bg-warning text-dark');
        $statutLabel = $statutActuel === 'valide' ? 'Validé' : 
                      ($statutActuel === 'refuse' ? 'Refusé' : 'En attente');
        ?>
        <span class="badge <?= $statutClass ?> fs-6 p-2">
            <?= $statutLabel ?>
        </span>
    </div>
</div>

<div class="row">
    <!-- Informations du trajet -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-route me-2"></i>
                    Informations du trajet
                </h5>
            </div>
            <div class="card-body">
                <!-- Départ -->
                <div class="mb-3">
                    <h6 class="text-success mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Départ
                    </h6>
                    <p class="mb-1"><strong><?= htmlspecialchars($trajet['lieu_depart']) ?></strong></p>
                    <small class="text-muted">Code postal : <?= htmlspecialchars($trajet['code_postal_depart']) ?></small>
                </div>

                <!-- Arrivée -->
                <div class="mb-3">
                    <h6 class="text-danger mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Arrivée
                    </h6>
                    <p class="mb-1"><strong><?= htmlspecialchars($trajet['lieu_arrivee']) ?></strong></p>
                    <small class="text-muted">Code postal : <?= htmlspecialchars($trajet['code_postal_arrivee']) ?></small>
                </div>

                <!-- Date et heure -->
                <div class="mb-3">
                    <h6 class="text-info mb-2">
                        <i class="fas fa-calendar me-2"></i>
                        Date et heure
                    </h6>
                    <p class="mb-1">
                        <strong><?= date('d/m/Y', strtotime($trajet['date_depart'])) ?></strong>
                    </p>
                    <small class="text-muted">
                        Heure : <?= date('H:i', strtotime($trajet['heure_depart'])) ?>
                    </small>
                </div>

                <!-- Places et prix -->
                <div class="mb-3">
                    <h6 class="text-warning mb-2">
                        <i class="fas fa-users me-2"></i>
                        Places et prix
                    </h6>
                    <p class="mb-1">
                        <span class="badge bg-info me-2"><?= $trajet['places'] ?> places</span>
                        <span class="badge bg-success"><?= number_format($trajet['prix'], 2) ?>€</span>
                    </p>
                </div>

                <!-- Métriques -->
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <strong><?= $trajet['distance_estimee'] ?></strong>
                            <br><small class="text-muted">Distance</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <strong><?= $trajet['duree_estimee'] ?></strong>
                            <br><small class="text-muted">Durée</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <strong><?= $trajet['co2_economise'] ?></strong>
                            <br><small class="text-muted">CO2 économisé</small>
                        </div>
                    </div>
                </div>

                <?php if ($trajet['commentaire']): ?>
                <!-- Commentaire -->
                <div class="mt-3">
                    <h6 class="text-secondary mb-2">
                        <i class="fas fa-comment me-2"></i>
                        Commentaire du conducteur
                    </h6>
                    <div class="alert alert-light">
                        <?= nl2br(htmlspecialchars($trajet['commentaire'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Informations du conducteur -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user me-2"></i>
                    Profil du conducteur
                </h5>
            </div>
            <div class="card-body">
                <!-- Infos personnelles -->
                <div class="mb-3">
                    <h6 class="text-primary mb-2">Identité</h6>
                    <p class="mb-1"><strong><?= htmlspecialchars($trajet['pseudo']) ?></strong></p>
                    <p class="mb-1"><?= htmlspecialchars($trajet['prenom'] . ' ' . $trajet['nom']) ?></p>
                    <p class="mb-1">📧 <?= htmlspecialchars($trajet['email']) ?></p>
                    <?php if ($trajet['telephone']): ?>
                        <p class="mb-1">📞 <?= htmlspecialchars($trajet['telephone']) ?></p>
                    <?php endif; ?>
                    <small class="text-muted">Âge : <?= $trajet['age_conducteur'] ?> ans</small>
                </div>

                <!-- Statut EcoRide -->
                <div class="mb-3">
                    <h6 class="text-warning mb-2">Statut EcoRide</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 text-success"><?= $trajet['credit'] ?>€</div>
                                <small class="text-muted">Crédits</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <div class="h4 <?= $trajet['note'] ? 'text-warning' : 'text-muted' ?>">
                                    <?= $trajet['note'] ? $trajet['note'] . '/5 ⭐' : 'N/A' ?>
                                </div>
                                <small class="text-muted">Note</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge <?= $trajet['permis_conduire'] ? 'bg-success' : 'bg-danger' ?>">
                            <?= $trajet['permis_conduire'] ? '✅ Permis validé' : '❌ Pas de permis' ?>
                        </span>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Inscrit le <?= $trajet['anciennete'] ?>
                    </small>
                </div>

                <!-- Statistiques trajets -->
                <div class="mb-3">
                    <h6 class="text-info mb-2">Historique des trajets</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <strong><?= $trajet['nb_trajets_total'] ?></strong>
                                <br><small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-success text-white">
                                <strong><?= $trajet['nb_trajets_valides'] ?></strong>
                                <br><small>Validés</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-danger text-white">
                                <strong><?= $trajet['nb_trajets_refuses'] ?></strong>
                                <br><small>Refusés</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($trajet['marque']): ?>
<!-- Informations véhicule -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-car me-2"></i>
                    Véhicule utilisé
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1">
                            <?= htmlspecialchars($trajet['marque'] . ' ' . $trajet['modele']) ?>
                            <?php if ($trajet['couleur']): ?>
                                <span class="text-muted">- <?= htmlspecialchars($trajet['couleur']) ?></span>
                            <?php endif; ?>
                        </h5>
                        <?php if ($trajet['plaque_immatriculation']): ?>
                            <p class="mb-1">
                                <strong>Plaque :</strong> <?= htmlspecialchars($trajet['plaque_immatriculation']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="mb-0">
                            <span class="badge bg-secondary me-2"><?= $trajet['vehicule_places'] ?> places</span>
                            <span class="badge <?= $trajet['vehicule_electrique'] ? 'bg-success' : 'bg-warning' ?>">
                                <?= $trajet['vehicule_electrique'] ? '🔋 Électrique' : '⛽ Thermique' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Actions de modération -->
<?php if ($trajet['statut_moderation'] === 'en_attente'): ?>
<div class="row">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="fas fa-gavel me-2"></i>
                    Actions de modération
                </h5>
            </div>
            <div class="card-body text-center">
                <p class="mb-4">Ce trajet est en attente de modération. Que souhaitez-vous faire ?</p>
                
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success btn-lg" onclick="modererTrajet(<?= $trajet['id'] ?>, 'valide')">
                        <i class="fas fa-check me-2"></i>
                        Valider le trajet
                    </button>
                    <button type="button" class="btn btn-danger btn-lg" onclick="modererTrajet(<?= $trajet['id'] ?>, 'refuse')">
                        <i class="fas fa-times me-2"></i>
                        Refuser le trajet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ✅ FONCTION DE MODÉRATION SIMPLE
function modererTrajet(trajetId, decision) {
    let motif = '';
    if (decision === 'refuse') {
        motif = prompt('Motif du refus (optionnel) :') || '';
    }
    
    const formData = new FormData();
    formData.append('trajet_id', trajetId);
    formData.append('decision', decision);
    if (motif) formData.append('motif', motif);
    
    fetch('/admin/api/moderer-trajet', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Trajet modéré avec succès !');
            window.location.href = '/admin/trajets';
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur technique');
    });
}
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
