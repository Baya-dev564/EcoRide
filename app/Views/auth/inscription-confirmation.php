<?php
/**
 * Page de confirmation après inscription
 * Informe l'utilisateur qu'il doit vérifier son email
 */

// Récupération du titre et message depuis le contrôleur
$title = $title ?? 'Vérifiez votre email - EcoRide';
$message = $message ?? '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="bg-light">
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-4">
                
                <!-- Logo EcoRide -->
                <div class="text-center mb-4">
                    <a href="/" class="text-decoration-none">
                        <h1 class="text-success">
                            <i class="fas fa-leaf me-2"></i>EcoRide
                        </h1>
                    </a>
                </div>
                
                <!-- Card de confirmation -->
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        
                        <!-- Icône de succès -->
                        <div class="text-center mb-4">
                            <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-envelope-open text-white fs-1"></i>
                            </div>
                        </div>
                        
                        <!-- Titre -->
                        <h2 class="card-title text-center mb-4 text-success fw-bold">
                            Inscription réussie !
                        </h2>
                        
                        <!-- Message principal -->
                        <div class="text-center mb-4">
                            <p class="lead mb-3">
                                <strong>Vérifiez votre adresse email</strong> pour activer votre compte EcoRide.
                            </p>
                            
                            <p class="text-muted">
                                Nous venons de vous envoyer un email de confirmation. 
                                Cliquez sur le lien qu'il contient pour finaliser votre inscription.
                            </p>
                        </div>
                        
                        <!-- Instructions -->
                        <div class="alert alert-info" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle me-2"></i>Que faire maintenant ?
                            </h6>
                            <ul class="mb-0 ps-3">
                                <li>Consultez votre boîte email</li>
                                <li>Cherchez un email de <strong>EcoRide</strong></li>
                                <li>Cliquez sur le lien de vérification</li>
                                <li>Connectez-vous à votre compte !</li>
                            </ul>
                        </div>
                        
                        <!-- Problème ? -->
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>Problème ?
                            </h6>
                            <ul class="mb-2 ps-3">
                                <li>Vérifiez vos <strong>spams/indésirables</strong></li>
                                <li>Le lien expire dans <strong>24 heures</strong></li>
                                <li>Pas reçu ? <a href="/contact" class="text-warning fw-bold">Contactez-nous</a></li>
                            </ul>
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-grid gap-2 mt-4">
                            <a href="/connexion" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Aller à la connexion
                            </a>
                            
                            <a href="/" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>
                                Retour à l'accueil
                            </a>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Message système -->
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
