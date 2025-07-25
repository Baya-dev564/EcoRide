<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'EcoRide - Covoiturage Écologique' ?></title>
    
    <!-- Meta tags pour le SEO et l'accessibilité -->
    <meta name="description" content="EcoRide - Plateforme de covoiturage écologique. Partagez vos trajets, économisez et protégez l'environnement.">
    <meta name="keywords" content="covoiturage, écologique, transport, environnement, économie, partage">
    <meta name="author" content="EcoRide">
    <meta name="robots" content="index, follow">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- CSS personnalisé EcoRide -->
     
   <link rel="stylesheet" href="/css/style.css">
   <link rel="stylesheet" href="/css/accessibility.css">

<!-- CSS spécifique à la page -->
<?php if (isset($cssFiles) && is_array($cssFiles)): ?>
    <?php foreach ($cssFiles as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
    <?php endforeach; ?>
<?php endif; ?>

</head>
<body>
    <?php
    // Récupération sécurisée de l'utilisateur connecté et des messages
    $userConnecte = $_SESSION['user'] ?? null;
    $messageGlobal = $_SESSION['message'] ?? '';
    unset($_SESSION['message']);
    ?>
    
    <!-- Lien d'évitement pour l'accessibilité -->
    <a href="#main-content" class="skip-link visually-hidden-focusable">Aller au contenu principal</a>
    
    <!-- Navigation principale -->
    <header role="banner">
        <nav class="navbar navbar-expand-lg navbar-dark bg-success shadow" role="navigation" aria-label="Navigation principale EcoRide">
            <div class="container">
                <!-- Logo et nom de l'application -->
                <a class="navbar-brand fw-bold fs-3" href="/" aria-label="EcoRide - Retour à l'accueil">
                    <i class="fas fa-leaf me-2" aria-hidden="true"></i>EcoRide
                </a>
                
                <!-- Bouton menu mobile avec accessibilité -->
                <button class="navbar-toggler" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#navbarNav"
                        aria-controls="navbarNav" 
                        aria-expanded="false" 
                        aria-label="Basculer la navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <!-- Menu de navigation  -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Menu principal -->
                    <ul class="navbar-nav me-auto" role="menubar">
                        <li class="nav-item" role="none">
                            <a class="nav-link" href="/" role="menuitem" aria-current="<?= $_SERVER['REQUEST_URI'] === '/' ? 'page' : 'false' ?>">
                                <i class="fas fa-home" aria-hidden="true"></i> Accueil
                            </a>
                        </li>
                        <li class="nav-item" role="none">
                            <a class="nav-link" href="/trajets" role="menuitem" aria-current="<?= strpos($_SERVER['REQUEST_URI'], '/trajets') === 0 ? 'page' : 'false' ?>">
                                <i class="fas fa-car" aria-hidden="true"></i> Covoiturages
                            </a>
                        </li>
                        <?php if ($userConnecte): ?>
                            <li class="nav-item" role="none">
                                <a class="nav-link" href="/mes-trajets" role="menuitem" aria-current="<?= strpos($_SERVER['REQUEST_URI'], '/mes-trajets') === 0 ? 'page' : 'false' ?>">
                                    <i class="fas fa-list" aria-hidden="true"></i> Mes trajets
                                </a>
                            </li>
                            <li class="nav-item" role="none">
                                <a class="nav-link" href="/nouveau-trajet" role="menuitem" aria-current="<?= strpos($_SERVER['REQUEST_URI'], '/nouveau-trajet') === 0 ? 'page' : 'false' ?>">
                                    <i class="fas fa-plus-circle" aria-hidden="true"></i> Proposer un trajet
                                </a>
                            </li>
                            <!-- admin -->
                            <li class="nav-item">
                            <a class="nav-link" href="/avis">
                            <i class="fas fa-star text-warning"></i> Avis
                            </a>
                            
                           <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                           <li class="nav-item">
                           <a class="nav-link admin-link" href="/admin/dashboard">
                            <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                            Administration
                             </a>
                            </li>
                        <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Menu utilisateur selon l'état de connexion -->
                    <ul class="navbar-nav" role="menubar">
                        <?php if ($userConnecte): ?>
                            <!-- Utilisateur connecté avec menu dropdown accessible -->
                            <li class="nav-item dropdown" role="none">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" 
                                   href="#" 
                                   role="button" 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false"
                                   aria-haspopup="true"
                                   aria-label="Menu utilisateur - <?= htmlspecialchars($userConnecte['pseudo']) ?>">
                                    <i class="fas fa-user-circle me-2" aria-hidden="true"></i>
                                    <span class="me-2"><?= htmlspecialchars($userConnecte['pseudo']) ?></span>
                                    <span class="badge bg-warning text-dark" role="img" aria-label="<?= $userConnecte['credit'] ?> crédits disponibles">
                                        <i class="fas fa-coins" aria-hidden="true"></i> <?= $userConnecte['credit'] ?> crédits
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow" role="menu" aria-labelledby="Menu utilisateur">
                                    <li role="none">
                                        <h6 class="dropdown-header" role="presentation">
                                            <i class="fas fa-user" aria-hidden="true"></i> Mon compte
                                        </h6>
                                    </li>
                                    <li role="none">
                                        <a class="dropdown-item" href="/profil" role="menuitem">
                                            <i class="fas fa-user-edit" aria-hidden="true"></i> Mon profil
                                        </a>
                                    </li>
                                    <li role="none">
                                        <a class="dropdown-item" href="/mes-reservations" role="menuitem">
                                            <i class="fas fa-calendar-check" aria-hidden="true"></i> Mes réservations
                                        </a>
                                    </li>
                                    <li role="none">
                                        <a class="dropdown-item" href="/historique" role="menuitem">
                                            <i class="fas fa-history" aria-hidden="true"></i> Historique
                                        </a>
                                    </li>
                                    <li role="none"><hr class="dropdown-divider"></li>
                                    <li role="none">
                                        <a class="dropdown-item text-danger" href="/deconnexion" role="menuitem">
                                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Déconnexion
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Utilisateur non connecté -->
                            <li class="nav-item" role="none">
                                <a class="nav-link" href="/connexion" role="menuitem" aria-current="<?= strpos($_SERVER['REQUEST_URI'], '/connexion') === 0 ? 'page' : 'false' ?>">
                                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Connexion
                                </a>
                            </li>
                            <li class="nav-item" role="none">
                                <a class="nav-link btn btn-outline-light ms-2 px-3" href="/inscription" role="menuitem" aria-current="<?= strpos($_SERVER['REQUEST_URI'], '/inscription') === 0 ? 'page' : 'false' ?>">
                                    <i class="fas fa-user-plus" aria-hidden="true"></i> Inscription
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Messages globaux  -->
    <?php if (!empty($messageGlobal)): ?>
        <aside class="container mt-3" role="complementary" aria-label="Messages système">
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" aria-live="polite">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                    <div><?= htmlspecialchars($messageGlobal) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer le message"></button>
            </div>
        </aside>
    <?php endif; ?>

    <!-- Contenu principal  -->
    <main class="container-fluid py-4" id="main-content" role="main" aria-label="Contenu principal">
        <?php if (isset($content)) echo $content; ?>
    </main>

    <!-- Pied de page  -->
    <footer class="footer bg-light py-4 mt-5 border-top" role="contentinfo" aria-label="Informations du site">
        <div class="container">
            <div class="row">
                <!-- Section contact -->
                <section class="col-md-6" aria-labelledby="contact-titre">
                    <h2 id="contact-titre" class="h5 text-success">
                        <i class="fas fa-envelope" aria-hidden="true"></i> Contact EcoRide
                    </h2>
                    <address class="mb-1">
                        <strong>Email :</strong> 
                        <a href="mailto:contact@ecoride.fr" class="text-decoration-none">contact@ecoride.fr</a>
                    </address>
                    <p class="mb-0">
                        <strong>Téléphone :</strong> 
                        <a href="tel:+33123456789" class="text-decoration-none">01 23 45 67 89</a>
                    </p>
                </section>
                
                <!-- Section informations légales -->
                <section class="col-md-6 text-md-end" aria-labelledby="legal-titre">
                    <h2 id="legal-titre" class="h5 text-success">
                        <i class="fas fa-file-alt" aria-hidden="true"></i> Informations légales
                    </h2>
                    <nav aria-label="Liens légaux">
                        <p class="mb-1">
                            <a href="/mentions-legales" class="text-decoration-none">Mentions légales</a>
                        </p>
                        <p class="mb-0">
                            <a href="/confidentialite" class="text-decoration-none">Politique de confidentialité</a>
                        </p>
                    </nav>
                </section>
            </div>
            
            <hr class="my-3" role="separator">
            
            <!-- Copyright -->
            <div class="text-center">
                <small class="text-muted">
                    © <?= date('Y') ?> EcoRide - Plateforme de covoiturage écologique
                </small>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript avec intégrité -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <!-- JavaScript global EcoRide  -->
    
<script src="/js/app.js"></script>

<!-- JavaScript spécifique à la page -->
<?php if (!empty($jsFiles)): ?>
    <?php foreach ($jsFiles as $jsFile): ?>
        <script src="<?= htmlspecialchars($jsFile) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
