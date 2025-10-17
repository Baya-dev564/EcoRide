# EcoRide - Plateforme de Covoiturage Écologique

## Description du Projet

**EcoRide** est une plateforme de covoiturage complète axée sur l'écologie, développée en **PHP natif** avec une architecture **MVC**. Elle permet aux utilisateurs de partager leurs trajets tout en réduisant leur empreinte carbone grâce à des incitations pour les véhicules électriques. Le projet intègre des fonctionnalités avancées de messagerie temps réel, système d'avis, et interface d'administration complète.

## Contexte

Ce projet constitue mon **premier projet de développement web** réalisé dans le cadre d'un **ECF (Évaluation en Cours de Formation)** de mon école Studi. Il a évolué d'un simple système de covoiturage vers une plateforme complète avec des fonctionnalités entreprise.

## Technologies Utilisées

### Stack Technique Complète
- **PHP 8.2+** avec architecture MVC
- **MySQL 8.0** pour la base de données relationnelle
- **MongoDB 4.4** pour la messagerie et les avis (NoSQL)
- **Docker & Docker Compose** pour l'environnement de développement
- **Nginx** comme serveur web haute performance
- **Bootstrap 5.3** + CSS3 pour le design responsive
- **JavaScript natif** (18 fichiers optimisés) pour l'interactivité
- **PDO** et **MongoDB Driver** pour les interactions base de données

## Installation avec Docker

### Prérequis
- Docker et Docker Compose
- Git

### Démarrage Rapide
git clone https://github.com/Baya-dev564/EcoRide.git
cd EcoRide
docker-compose up -d


**Accès :** http://localhost:8080

### Configuration Docker
Le projet utilise 4 conteneurs :
- **PHP 8.1.10-fpm** : Application principale
- **Nginx** : Serveur web
- **MySQL 8.0.43** : Base de données relationnelle
- **MongoDB 4.4.29** : Base NoSQL pour messagerie/avis

## Fonctionnalités Principales

### Authentification Avancée
- Inscription/Connexion sécurisée avec validation temps réel
- **Vérification par email** avec tokens sécurisés
- Système de récupération de mot de passe
- Sessions sécurisées avec protection CSRF

### Gestion Complète des Trajets
- Création de trajets avec **géolocalisation GPS**
- **Autocomplete intelligent** des lieux avec API
- Calculs automatiques : distance, durée, prix, impact CO2
- Recherche avancée multi-critères
- **Workflow de notation** post-trajet

### Système de Réservations
- Réservation en temps réel avec validation des crédits
- Gestion dynamique des places disponibles
- Annulation avec remboursement automatique
- **Historique complet** des réservations

### Messagerie Temps Réel (MongoDB)
- **Chat en temps réel** entre utilisateurs
- Conversations organisées par trajet
- **Notifications de messages non lus**
- Recherche d'utilisateurs intégrée
- Motifs de contact prédéfinis

### Système d'Avis Avancé (MongoDB)
- **Évaluation multicritères** (ponctualité, conduite, propreté, ambiance)
- Commentaires détaillés avec modération
- **Tags descriptifs** automatiques
- Filtrage des avis par note
- **Statistiques de conducteur** complètes

### Interface d'Administration Complète
- **Dashboard avec statistiques** temps réel
- **Gestion des utilisateurs** (modification, suspension)
- **Modération des trajets** et avis
- **Système de crédits** administrable
- **Graphiques interactifs** avec Chart.js

### Profils Utilisateurs
- **Gestion des véhicules** personnels
- Modification des informations profil
- **Historique complet** des activités
- Statistiques personnalisées

## Structure du Projet
EcoRide/
├── app/
│ ├── Controllers/ # Contrôleurs MVC (12 contrôleurs)
│ │ ├── AuthController.php
│ │ ├── AdminController.php
│ │ ├── AdminUserController.php
│ │ ├── MessagerieController.php
│ │ ├── AvisController.php
│ │ └── ...
│ ├── Models/ # Modèles de données
│ └── Views/ # Vues et templates
│ ├── layouts/ # Layouts principaux
│ ├── admin/ # Interface admin
│ ├── auth/ # Authentification
│ └── messages/ # Messagerie
├── public/ # Point d'entrée public
│ ├── index.php # Router principal unifié
│ ├── css/ # Styles personnalisés
│ ├── js/ # 18 fichiers JavaScript optimisés
│ └── assets/ # Images et ressources
├── config/ # Configuration
│ ├── database.php # Config MySQL
│ └── php.ini # Config PHP personnalisée
├── docker/ # Configuration Docker
│ └── nginx/ # Config Nginx
├── docker-compose.yml # Orchestration Docker
└── sql/ # Scripts de base de données


## Fonctionnalités Écologiques

- **Réduction tarifaire** de 10% pour véhicules électriques
- **Calcul CO2 économisé** par rapport au transport individuel
- **Badge écologique** pour les conducteurs verts
- **Incitations financières** pour la mobilité durable

## Sécurité

- **Hashage des mots de passe** avec `password_hash()`
- **Validation côté serveur** pour toutes les entrées
- **Échappement des données** contre les injections XSS/SQL
- **Protection CSRF** sur les formulaires
- **Sessions sécurisées** avec regeneration d'ID
- **Validation email** avec tokens temporaires

## APIs et Intégrations

### APIs Développées
- **API REST complète** pour toutes les fonctionnalités
- **Endpoints AJAX** pour interactions temps réel
- **API de géolocalisation** pour les trajets
- **API de messagerie** temps réel
- **API d'administration** pour la gestion

### JavaScript Avancé
- **18 fichiers JavaScript** optimisés et modulaires
- **Interactions temps réel** sans rechargement
- **Autocomplete avancé** des lieux
- **Animations et transitions** fluides
- **Gestion d'erreurs** sophistiquée

## Utilisation

### Interface Utilisateur
1. **S'inscrire** avec vérification email
2. **Configurer son profil** et ajouter des véhicules
3. **Rechercher des trajets** avec filtres avancés
4. **Réserver** selon vos besoins avec système de crédits
5. **Proposer vos trajets** avec calculs automatiques
6. **Échanger via la messagerie** intégrée
7. **Évaluer les conducteurs** après chaque trajet

### Interface Administrateur
Accès : http://localhost:8080/admin
- **Tableau de bord** avec métriques temps réel
- **Gestion des utilisateurs** et modération
- **Statistiques avancées** avec graphiques
- **Outils de modération** complets

## Problèmes Courants et Solutions

### Erreur de Connexion Docker
docker-compose down
docker-compose up -d

### Problème de Base de Données
Vérifier les logs
docker logs ecoride-mysql
docker logs ecoride-php


### Page Blanche
Vérifier les logs PHP dans le conteneur :
docker exec -it ecoride-php tail -f /var/log/php_errors.log


## Tests et Validation

### Tests Fonctionnels
- Authentification complète
- Création et recherche de trajets
- Système de réservations
- Messagerie temps réel
- Interface d'administration
- Système d'avis

### Performance
- **Optimisations JavaScript** (throttling, debouncing)
- **Requêtes SQL optimisées** avec index
- **Cache des résultats** géolocalisés
- **Lazy loading** des contenus

## Évolutions Futures

### Version 2.0 Planifiée
- **Application mobile native** (React Native)
- **Notifications push** en temps réel
- **Intégration paiement** (Stripe/PayPal)
- **IA pour suggestions** de trajets optimaux
- **API publique** pour développeurs tiers

### Améliorations Techniques
- **Migration vers PHP 8.3**
- **Implémentation GraphQL**
- **Tests automatisés** (PHPUnit)
- **CI/CD Pipeline** (GitHub Actions)
- **Monitoring applicatif** (Prometheus)

## Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commiter les changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Pousser vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrir une Pull Request

## Configuration pour Production

### Variables d'Environnement
Base de données
DB_HOST=localhost
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASS=mot_de_passe_securise

MongoDB
MONGO_HOST=localhost
MONGO_DB=ecoride_messages
MONGO_USER=ecoride_mongo
MONGO_PASS=mot_de_passe_mongo


## Monitoring et Logs

### Logs Disponibles
- **Logs applicatifs** : `/var/log/ecoride/`
- **Logs serveur** : Nginx + PHP-FPM
- **Logs base de données** : MySQL + MongoDB
- **Métriques** : Temps de réponse, usage mémoire

## Contact et Support

**Développeur** : Baya AMELLAL PAYAN 
**Email** : [amellbaya@gmail.com](mailto:amellbaya@gmail.com)  
**GitHub** : [@Baya-dev564](https://github.com/Baya-dev564)  
**LinkedIn** : [Profil LinkedIn](https://linkedin.com/in/baya-amellal)

## Licence

Ce projet est sous licence **MIT**. Voir le fichier `LICENSE` pour plus de détails.

---

**EcoRide** - De projet étudiant à plateforme professionnelle. Développé avec passion dans le cadre de ma formation en développement web chez Studi.

**Version actuelle :** 2.0  
**Dernière mise à jour :** Septembre 2025  
**Statut :** Production Ready


