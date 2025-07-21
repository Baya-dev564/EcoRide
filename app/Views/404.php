<?php
// app/Views/errors/404.php
// Page d'erreur 404

ob_start();
?>

<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="display-1 text-success">404</h1>
            <h2 class="mb-4">Page non trouvée</h2>
            <p class="lead mb-4">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            <a href="/EcoRide/public/" class="btn btn-success btn-lg">
                ← Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Erreur 404 | EcoRide";
require __DIR__ . '/../layouts/main.php';
?>
