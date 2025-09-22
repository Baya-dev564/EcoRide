<?php
/**
 * Vue de gestion des utilisateurs - Administration EcoRide
 * pas encore finalisé
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
                        <i class="fas fa-users me-2" aria-hidden="true"></i>
                        Gestion des utilisateurs
                    </h1>
                    <p class="admin-subtitle">
                        Administration des comptes utilisateurs EcoRide
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="admin-stats">
                        <span class="stat-badge stat-badge-primary">
                            <i class="fas fa-user-friends me-1" aria-hidden="true"></i>
                            <?= count($utilisateurs) ?> utilisateurs
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenu principal -->
    <main class="admin-main">
        <div class="container">
            
            <!-- Barre de recherche -->
            <section class="search-section mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="search-box">
                            <label for="searchUsers" class="form-label">
                                <i class="fas fa-search me-1" aria-hidden="true"></i>
                                Rechercher un utilisateur
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="searchUsers" 
                                placeholder="Pseudo, nom, email..."
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="filter-box">
                            <label for="filterRole" class="form-label">
                                <i class="fas fa-filter me-1" aria-hidden="true"></i>
                                Filtrer par rôle
                            </label>
                            <select class="form-select" id="filterRole">
                                <option value="">Tous les rôles</option>
                                <option value="user">Utilisateurs</option>
                                <option value="admin">Administrateurs</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tableau des utilisateurs -->
            <section class="users-table-section">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-table me-2" aria-hidden="true"></i>
                            Liste des utilisateurs
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover users-table" id="usersTable">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <i class="fas fa-user me-1" aria-hidden="true"></i>
                                            Utilisateur
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-envelope me-1" aria-hidden="true"></i>
                                            Email
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-coins me-1" aria-hidden="true"></i>
                                            Crédits
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-id-card me-1" aria-hidden="true"></i>
                                            Permis
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-calendar me-1" aria-hidden="true"></i>
                                            Inscription
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                                            Rôle
                                        </th>
                                        <th scope="col">
                                            <i class="fas fa-cogs me-1" aria-hidden="true"></i>
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs as $user): ?>
                                    <tr class="user-row" data-user-id="<?= $user['id'] ?>" data-role="<?= $user['role'] ?>">
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user-circle" aria-hidden="true"></i>
                                                </div>
                                                <div class="user-details">
                                                    <strong class="user-pseudo"><?= htmlspecialchars($user['pseudo']) ?></strong>
                                                    <?php if (!empty($user['nom']) || !empty($user['prenom'])): ?>
                                                        <small class="user-name text-muted">
                                                            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                        </td>
                                        <td>
                                            <span class="credit-badge credit-badge-<?= $user['credit'] >= 10 ? 'success' : 'warning' ?>">
                                                <i class="fas fa-coins me-1" aria-hidden="true"></i>
                                                <?= $user['credit'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['permis_conduire']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1" aria-hidden="true"></i>
                                                    Oui
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times me-1" aria-hidden="true"></i>
                                                    Non
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="date-badge">
                                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="role-badge role-badge-<?= $user['role'] ?>">
                                                <i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?> me-1" aria-hidden="true"></i>
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button 
                                                    class="btn btn-sm btn-primary btn-edit-credits" 
                                                    data-user-id="<?= $user['id'] ?>"
                                                    data-user-pseudo="<?= htmlspecialchars($user['pseudo']) ?>"
                                                    data-current-credits="<?= $user['credit'] ?>"
                                                    title="Modifier les crédits"
                                                >
                                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                                </button>
                                                <button 
                                                    class="btn btn-sm btn-info btn-view-stats" 
                                                    data-user-id="<?= $user['id'] ?>"
                                                    data-user-pseudo="<?= htmlspecialchars($user['pseudo']) ?>"
                                                    title="Voir les statistiques"
                                                >
                                                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal pour modifier les crédits -->
    <div class="modal fade" id="editCreditsModal" tabindex="-1" aria-labelledby="editCreditsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCreditsModalLabel">
                        <i class="fas fa-coins me-2" aria-hidden="true"></i>
                        Modifier les crédits
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="editCreditsForm">
                        <input type="hidden" id="editUserId" name="user_id">
                        <div class="mb-3">
                            <label for="editUserPseudo" class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" id="editUserPseudo" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editCurrentCredits" class="form-label">Crédits actuels</label>
                            <input type="number" class="form-control" id="editCurrentCredits" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editNewCredits" class="form-label">Nouveaux crédits</label>
                            <input type="number" class="form-control" id="editNewCredits" min="0" max="1000" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1" aria-hidden="true"></i>
                        Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="saveCreditsBtn">
                        <i class="fas fa-save me-1" aria-hidden="true"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript personnalisé pour l'admin -->
    <script src="/js/admin.js"></script>
    
    <!-- JavaScript spécifique aux utilisateurs -->
</body>
</html>
