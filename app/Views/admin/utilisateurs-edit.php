<?php
/**
 * Vue de modification d'un utilisateur - Administration EcoRide
 * Je respecte ton style et ta structure utilisateurs.php
 */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier <?= htmlspecialchars($userData['pseudo']) ?> - Admin EcoRide</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS personnalisé pour l'admin -->
    <link rel="stylesheet" href="/css/admin.css">
    
    <!-- CSS spécifique aux utilisateurs -->
    <link rel="stylesheet" href="/css/admin-users.css">
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
                        <a class="nav-link" href="/admin/dashboard">
                            <i class="fas fa-tachometer-alt me-1" aria-hidden="true"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/utilisateurs">
                            <i class="fas fa-users me-1" aria-hidden="true"></i>
                            Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/avis">
                            <i class="fas fa-star me-1" aria-hidden="true"></i>
                            Avis
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
                        <i class="fas fa-user-edit me-2" aria-hidden="true"></i>
                        Modifier <?= htmlspecialchars($userData['pseudo']) ?>
                    </h1>
                    <p class="admin-subtitle">
                        Modification des informations utilisateur
                    </p>
                    <!-- Fil d'Ariane -->
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="/admin/dashboard">
                                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="/admin/utilisateurs">
                                    <i class="fas fa-users" aria-hidden="true"></i>
                                    Utilisateurs
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Modifier
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-end">
                    <div class="admin-stats">
                        <a href="/admin/user-stats/<?= $userData['id'] ?>" class="btn btn-info me-2">
                            <i class="fas fa-chart-bar me-1" aria-hidden="true"></i>
                            Statistiques
                        </a>
                        <a href="/admin/utilisateurs" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="admin-main">
        <div class="container">
            
            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer l'alerte"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer l'alerte"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Formulaire de modification -->
            <section class="edit-user-section">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <i class="fas fa-edit me-2" aria-hidden="true"></i>
                                    Informations utilisateur
                                </h2>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="/admin/user-update/<?= $userData['id'] ?>" id="editUserForm" novalidate>
                                    <div class="row">
                                        <!-- Colonne gauche -->
                                        <div class="col-md-6">
                                            <!-- Pseudo -->
                                            <div class="mb-3">
                                                <label for="pseudo" class="form-label">
                                                    <i class="fas fa-user me-1" aria-hidden="true"></i>
                                                    Pseudo <span class="text-danger">*</span>
                                                </label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="pseudo" 
                                                    name="pseudo" 
                                                    value="<?= htmlspecialchars($userData['pseudo']) ?>" 
                                                    required
                                                    maxlength="50"
                                                    aria-describedby="pseudoHelp"
                                                >
                                                <div class="form-text" id="pseudoHelp">
                                                    Entre 3 et 50 caractères, unique sur la plateforme
                                                </div>
                                                <div class="invalid-feedback" id="pseudoError"></div>
                                            </div>

                                            <!-- Prénom -->
                                            <div class="mb-3">
                                                <label for="prenom" class="form-label">
                                                    <i class="fas fa-id-card me-1" aria-hidden="true"></i>
                                                    Prénom
                                                </label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="prenom" 
                                                    name="prenom" 
                                                    value="<?= htmlspecialchars($userData['prenom'] ?? '') ?>"
                                                    maxlength="50"
                                                >
                                            </div>

                                            <!-- Nom -->
                                            <div class="mb-3">
                                                <label for="nom" class="form-label">
                                                    <i class="fas fa-id-card me-1" aria-hidden="true"></i>
                                                    Nom
                                                </label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="nom" 
                                                    name="nom" 
                                                    value="<?= htmlspecialchars($userData['nom'] ?? '') ?>"
                                                    maxlength="50"
                                                >
                                            </div>

                                            <!-- Date de naissance -->
                                            <div class="mb-3">
                                                <label for="date_naissance" class="form-label">
                                                    <i class="fas fa-birthday-cake me-1" aria-hidden="true"></i>
                                                    Date de naissance
                                                </label>
                                                <input 
                                                    type="date" 
                                                    class="form-control" 
                                                    id="date_naissance" 
                                                    name="date_naissance" 
                                                    value="<?= htmlspecialchars($userData['date_naissance'] ?? '') ?>"
                                                >
                                            </div>
                                        </div>

                                        <!-- Colonne droite -->
                                        <div class="col-md-6">
                                            <!-- Email -->
                                            <div class="mb-3">
                                                <label for="email" class="form-label">
                                                    <i class="fas fa-envelope me-1" aria-hidden="true"></i>
                                                    Email <span class="text-danger">*</span>
                                                </label>
                                                <input 
                                                    type="email" 
                                                    class="form-control" 
                                                    id="email" 
                                                    name="email" 
                                                    value="<?= htmlspecialchars($userData['email']) ?>" 
                                                    required
                                                    maxlength="100"
                                                    aria-describedby="emailHelp"
                                                >
                                                <div class="form-text" id="emailHelp">
                                                    Adresse email unique et valide
                                                </div>
                                                <div class="invalid-feedback" id="emailError"></div>
                                            </div>

                                            <!-- Téléphone -->
                                            <div class="mb-3">
                                                <label for="telephone" class="form-label">
                                                    <i class="fas fa-phone me-1" aria-hidden="true"></i>
                                                    Téléphone
                                                </label>
                                                <input 
                                                    type="tel" 
                                                    class="form-control" 
                                                    id="telephone" 
                                                    name="telephone" 
                                                    value="<?= htmlspecialchars($userData['telephone'] ?? '') ?>"
                                                    maxlength="20"
                                                    pattern="[0-9+\-\s\(\)]+"
                                                    aria-describedby="phoneHelp"
                                                >
                                                <div class="form-text" id="phoneHelp">
                                                    Format : 0123456789 ou +33123456789
                                                </div>
                                            </div>

                                            <!-- Crédits -->
                                            <div class="mb-3">
                                                <label for="credit" class="form-label">
                                                    <i class="fas fa-coins me-1" aria-hidden="true"></i>
                                                    Crédits <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <input 
                                                        type="number" 
                                                        class="form-control" 
                                                        id="credit" 
                                                        name="credit" 
                                                        value="<?= $userData['credit'] ?>" 
                                                        required
                                                        min="0"
                                                        max="9999"
                                                        aria-describedby="creditHelp"
                                                    >
                                                    <span class="input-group-text">
                                                        <i class="fas fa-coins" aria-hidden="true"></i>
                                                    </span>
                                                </div>
                                                <div class="form-text" id="creditHelp">
                                                    Entre 0 et 9999 crédits
                                                </div>
                                            </div>

                                            <!-- Ville -->
                                            <div class="mb-3">
                                                <label for="ville" class="form-label">
                                                    <i class="fas fa-map-marker-alt me-1" aria-hidden="true"></i>
                                                    Ville
                                                </label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="ville" 
                                                    name="ville" 
                                                    value="<?= htmlspecialchars($userData['ville'] ?? '') ?>"
                                                    maxlength="100"
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Ligne complète -->
                                    <div class="row">
                                        <div class="col-md-8">
                                            <!-- Adresse -->
                                            <div class="mb-3">
                                                <label for="adresse" class="form-label">
                                                    <i class="fas fa-home me-1" aria-hidden="true"></i>
                                                    Adresse
                                                </label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="adresse" 
                                                    name="adresse" 
                                                    value="<?= htmlspecialchars($userData['adresse'] ?? '') ?>"
                                                    maxlength="255"
                                                >
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <!-- Code postal -->
                                            <div class="mb-3">
                                                <label for="code_postal" class="form-label">
                                                    <i class="fas fa-map-pin me-1" aria-hidden="true"></i>
                                                    Code postal
                                                </label>
                                                <input 
                                                    type="text" 
                                                    class="form-control" 
                                                    id="code_postal" 
                                                    name="code_postal" 
                                                    value="<?= htmlspecialchars($userData['code_postal'] ?? '') ?>"
                                                    maxlength="5"
                                                    pattern="[0-9]{5}"
                                                    aria-describedby="codePostalHelp"
                                                >
                                                <div class="form-text" id="codePostalHelp">
                                                    5 chiffres (ex: 75001)
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Biographie -->
                                    <div class="mb-4">
                                        <label for="bio" class="form-label">
                                            <i class="fas fa-quote-left me-1" aria-hidden="true"></i>
                                            Biographie
                                        </label>
                                        <textarea 
                                            class="form-control" 
                                            id="bio" 
                                            name="bio" 
                                            rows="3"
                                            maxlength="500"
                                            aria-describedby="bioHelp"
                                        ><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
                                        <div class="form-text" id="bioHelp">
                                            Description personnelle (maximum 500 caractères)
                                        </div>
                                    </div>

                                    <!-- Informations en lecture seule -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="info-readonly">
                                                <label class="form-label">
                                                    <i class="fas fa-calendar me-1" aria-hidden="true"></i>
                                                    Date d'inscription
                                                </label>
                                                <div class="form-control-plaintext">
                                                    <?= date('d/m/Y à H:i', strtotime($userData['created_at'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-readonly">
                                                <label class="form-label">
                                                    <i class="fas fa-edit me-1" aria-hidden="true"></i>
                                                    Dernière modification
                                                </label>
                                                <div class="form-control-plaintext">
                                                    <?= $userData['updated_at'] ? date('d/m/Y à H:i', strtotime($userData['updated_at'])) : 'Jamais' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <a href="/admin/utilisateurs" class="btn btn-secondary">
                                                <i class="fas fa-times me-1" aria-hidden="true"></i>
                                                Annuler
                                            </a>
                                        </div>
                                        <div>
                                            <button type="reset" class="btn btn-outline-secondary me-2">
                                                <i class="fas fa-undo me-1" aria-hidden="true"></i>
                                                Réinitialiser
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1" aria-hidden="true"></i>
                                                Enregistrer les modifications
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section informations supplémentaires -->
            <section class="additional-info-section mt-4">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="row">
                            <!-- Statistiques rapides -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h3 class="card-title h6 mb-0">
                                            <i class="fas fa-chart-bar me-1" aria-hidden="true"></i>
                                            Statistiques rapides
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center g-3">
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <div class="stat-number text-primary h5 mb-1">
                                                        <?= $userData['nb_trajets_proposes'] ?? 0 ?>
                                                    </div>
                                                    <small class="stat-label text-muted">Trajets proposés</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <div class="stat-number text-success h5 mb-1">
                                                        <?= $userData['nb_reservations'] ?? 0 ?>
                                                    </div>
                                                    <small class="stat-label text-muted">Réservations</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <div class="stat-number text-info h5 mb-1">
                                                        <?= $userData['nb_vehicules'] ?? 0 ?>
                                                    </div>
                                                    <small class="stat-label text-muted">Véhicules</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <div class="stat-number text-warning h5 mb-1">
                                                        <?= number_format($userData['note'], 1) ?>/5
                                                    </div>
                                                    <small class="stat-label text-muted">Note moyenne</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions rapides -->
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h3 class="card-title h6 mb-0">
                                            <i class="fas fa-tools me-1" aria-hidden="true"></i>
                                            Actions rapides
                                        </h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="/admin/user-stats/<?= $userData['id'] ?>" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-chart-line me-1" aria-hidden="true"></i>
                                                Voir statistiques détaillées
                                            </a>
                                            
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-warning btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#quickEditCreditsModal"
                                            >
                                                <i class="fas fa-coins me-1" aria-hidden="true"></i>
                                                Modifier les crédits uniquement
                                            </button>
                                            
                                            <button 
                                                type="button" 
                                                class="btn btn-outline-danger btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmSuspendModal"
                                            >
                                                <i class="fas fa-ban me-1" aria-hidden="true"></i>
                                                Suspendre le compte
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal modification rapide des crédits -->
    <div class="modal fade" id="quickEditCreditsModal" tabindex="-1" aria-labelledby="quickEditCreditsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quickEditCreditsModalLabel">
                        <i class="fas fa-coins me-2" aria-hidden="true"></i>
                        Modification rapide des crédits
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="quickEditCreditsForm">
                        <div class="mb-3">
                            <label for="quickCurrentCredits" class="form-label">Crédits actuels</label>
                            <input type="number" class="form-control" id="quickCurrentCredits" value="<?= $userData['credit'] ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="quickNewCredits" class="form-label">Nouveaux crédits</label>
                            <input type="number" class="form-control" id="quickNewCredits" min="0" max="9999" value="<?= $userData['credit'] ?>" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1" aria-hidden="true"></i>
                        Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="quickSaveCreditsBtn">
                        <i class="fas fa-save me-1" aria-hidden="true"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal confirmation suspension -->
    <div class="modal fade" id="confirmSuspendModal" tabindex="-1" aria-labelledby="confirmSuspendModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmSuspendModalLabel">
                        <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                        Confirmer la suspension
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir suspendre le compte de <strong><?= htmlspecialchars($userData['pseudo']) ?></strong> ?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                        Cette action empêchera l'utilisateur de se connecter et d'utiliser la plateforme.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1" aria-hidden="true"></i>
                        Annuler
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmSuspendBtn">
                        <i class="fas fa-ban me-1" aria-hidden="true"></i>
                        Suspendre le compte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé pour l'admin -->
    <script src="/js/admin.js"></script>
    
    <!-- JavaScript spécifique à la modification utilisateur -->
    <script src="/js/admin.js"></script>

    <!-- Script inline pour les données -->
    <script>
        // Je prépare les données pour les actions AJAX
        window.userData = {
            id: <?= $userData['id'] ?>,
            pseudo: <?= json_encode($userData['pseudo']) ?>,
            currentCredits: <?= $userData['credit'] ?>
        };
    </script>
</body>
</html>
