<?php
// app/Views/home/index.php
// Vue de la page d'accueil 

ob_start();
?>

<!-- Contenu principal de la page d'accueil -->
<main role="main">
    <!-- Section héro avec présentation principale -->
    <!-- Section héro avec background image -->
<section class="hero-section mb-5" aria-labelledby="hero-titre">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 col-lg-6">
                <header>
                    <h1 id="hero-titre" class="display-4 fw-bold mb-4">
                        Bienvenue sur EcoRide !
                    </h1>
                    <p class="lead mb-4">
                        La plateforme de covoiturage qui protège la planète.<br>
                        Réduisez votre empreinte carbone tout en économisant sur vos déplacements !
                    </p>
                </header>
                
                <?php if ($user): ?>
                    <aside class="alert alert-light bg-white bg-opacity-75 text-dark mb-4" role="region" aria-labelledby="user-welcome">
                        <h2 id="user-welcome" class="h5">🎉 Bienvenue <?= htmlspecialchars($user['pseudo']) ?> !</h2>
                        <p>Vous avez <strong><?= $user['credit'] ?> crédits</strong> disponibles.</p>
                        <nav>
                            <a href="/trajets" class="btn btn-success">Rechercher un trajet</a>
                            <a href="/nouveau-trajet" class="btn btn-outline-success">Proposer un trajet</a>
                        </nav>
                    </aside>
                <?php else: ?>
                    <nav class="d-flex gap-3 mt-4">
                        <a href="/trajets" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-search me-2"></i>Rechercher un trajet
                        </a>
                        <a href="/inscription" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>Rejoindre EcoRide
                        </a>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

    <!-- Section des statistiques -->
    <section class="stats-section mb-5" aria-labelledby="stats-titre">
        <div class="container">
            <h2 id="stats-titre" class="sr-only">Statistiques de la plateforme EcoRide</h2>
            <div class="row text-center">
                <article class="col-md-3">
                    <div class="stat-item">
                        <h3 class="text-success fw-bold"><?= number_format($stats['trajets_total']) ?></h3>
                        <p class="text-muted">Trajets partagés</p>
                    </div>
                </article>
                <article class="col-md-3">
                    <div class="stat-item">
                        <h3 class="text-success fw-bold"><?= $stats['co2_economise'] ?></h3>
                        <p class="text-muted">CO₂ économisé</p>
                    </div>
                </article>
                <article class="col-md-3">
                    <div class="stat-item">
                        <h3 class="text-success fw-bold"><?= number_format($stats['utilisateurs_actifs']) ?></h3>
                        <p class="text-muted">Utilisateurs actifs</p>
                    </div>
                </article>
                <article class="col-md-3">
                    <div class="stat-item">
                        <h3 class="text-success fw-bold"><?= $stats['vehicules_electriques'] ?>%</h3>
                        <p class="text-muted">Véhicules électriques</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- Section de présentation des avantages -->
    <section class="features-section mb-5" aria-labelledby="features-titre">
        <div class="container">
            <header class="row">
                <div class="col-12 text-center mb-4">
                    <h2 id="features-titre" class="text-success">Pourquoi choisir EcoRide ?</h2>
                    <p class="lead text-muted">Votre solution de covoiturage éco-responsable</p>
                </div>
            </header>
            
            <div class="row g-4">
                <article class="col-md-4 text-center">
                    <div class="feature-card p-4">
                        <h3 class="text-success">🌱 Écologique</h3>
                        <p>Réduisez votre empreinte carbone en partageant vos trajets. Privilégiez les véhicules électriques !</p>
                    </div>
                </article>
                <article class="col-md-4 text-center">
                    <div class="feature-card p-4">
                        <h3 class="text-success">💰 Économique</h3>
                        <p>Partagez les frais de transport et gagnez des crédits. Voyagez plus pour moins cher !</p>
                    </div>
                </article>
                <article class="col-md-4 text-center">
                    <div class="feature-card p-4">
                        <h3 class="text-success">🤝 Convivial</h3>
                        <p>Rencontrez de nouvelles personnes et créez des liens lors de vos déplacements.</p>
                    </div>
                </article>
            </div>
        </div>
<div class="container py-5">
    <div class="row justify-content-center"> 
        <div class="col-md-6 mb-4" >
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Avis des utilisateurs</h5>
                    <p class="card-text">Consultez les évaluations des conducteurs et partagez votre expérience.</p>
                    <a href="/avis" class="btn btn-warning">
                        <i class="fas fa-eye me-2"></i>Voir les avis
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

    </section>

    
</main>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
