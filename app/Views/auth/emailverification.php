<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Email - EcoRide</title>
    
    <!-- Link vers le CSS externe -->
    <link rel="stylesheet" href="/css/email-verification.css">
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1 class="logo">🚗 EcoRide</h1>
            <p class="welcome">Bienvenue dans la communauté !</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <h2 class="greeting">Bonjour <?= htmlspecialchars($pseudo) ?> ! 👋</h2>
            
            <p>Merci de vous être inscrit(e) sur <strong>EcoRide</strong>, la plateforme de covoiturage écologique !</p>
            
            <p>Pour finaliser votre inscription et commencer à utiliser EcoRide, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous :</p>
            
            <div class="button-container">
                <a href="<?= htmlspecialchars($lienVerification) ?>" class="verify-button">
                    ✅ Vérifier mon email
                </a>
            </div>
            
            <div class="link-fallback">
                <p><small>Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :<br>
                <a href="<?= htmlspecialchars($lienVerification) ?>" class="backup-link"><?= htmlspecialchars($lienVerification) ?></a></small></p>
            </div>
            
            <hr class="divider">
            
            <div class="important-info">
                <p><strong>⚠️ Important :</strong></p>
                <ul>
                    <li>Ce lien expire dans 24 heures</li>
                    <li>Sans vérification, vous ne pourrez pas vous connecter</li>
                    <li>Si vous n'avez pas créé de compte, ignorez cet email</li>
                </ul>
            </div>
            
            <p class="closing">À bientôt sur EcoRide ! 🌱</p>
            
            <p class="signature"><small>L'équipe EcoRide</small></p>
        </div>
    </div>
</body>
</html>
