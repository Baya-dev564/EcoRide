<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="text-center mb-4">
                    <i class="fas fa-balance-scale me-2"></i>
                    Mentions légales
                </h1>
                
                <div class="card">
                    <div class="card-body">
                        <h2>1. Éditeur du site</h2>
                        <p><strong>Nom :</strong> EcoRide</p>
                        <p><strong>Statut :</strong> Projet étudiant - TP Développement Web</p>
                        <p><strong>Email :</strong> contact@ecoride.test</p>
                        
                        <h2>2. Hébergement</h2>
                        <p><strong>Hébergeur :</strong> Laragon (développement local)</p>
                        <p><strong>Type :</strong> Environnement de développement</p>
                        
                        <h2>3. Propriété intellectuelle</h2>
                        <p>
                            Ce site a été développé dans le cadre d'un travail pratique 
                            de développement web et n'a pas vocation commerciale.
                        </p>
                        
                        <h2>4. Responsabilité</h2>
                        <p>
                            EcoRide est un projet pédagogique. L'éditeur ne saurait être 
                            tenu responsable des éventuels dysfonctionnements du site.
                        </p>
                        
                        <h2>5. Contact</h2>
                        <p>
                            Pour toute question : 
                            <a href="mailto:contact@ecoride.test">contact@ecoride.test</a>
                        </p>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="/EcoRide/public/" class="btn btn-success">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour à l'accueil
                    </a>
                </div>
                
                <p class="text-muted text-center mt-3">
                    <small>Dernière mise à jour : <?= date('d/m/Y') ?></small>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
