#  EcoRide - Plateforme de Covoiturage Écologique

##  Description du Projet

**EcoRide** est une plateforme de covoiturage axée sur l'écologie, développée en **PHP natif** avec une architecture **MVC**. Elle permet aux utilisateurs de partager leurs trajets tout en réduisant leur empreinte carbone grâce à des incitations pour les véhicules électriques.

##  Contexte

Ce projet constitue mon **premier projet de développement web** réalisé dans le cadre d'un **ECF (Évaluation en Cours de Formation)** de mon école Studi.

##  Fonctionnalités Principales

###  Gestion des Utilisateurs
- **Inscription/Connexion** sécurisée avec validation temps réel
- **Profils complets** avec informations personnelles
- **Système de crédits** pour les paiements

###  Gestion des Trajets
- **Création de trajets** avec calcul automatique des prix
- **Recherche avancée** par lieu, date et critères écologiques
- **Calculs automatiques** : distance, durée, prix, impact CO₂

###  Système de Réservations
- **Réservation en temps réel** avec validation des crédits
- **Gestion des places disponibles** dynamique
- **Annulation** avec remboursement automatique

###  Système d'Avis
- **Évaluation des conducteurs** après chaque trajet
- **Notes détaillées** (ponctualité, conduite, propreté, ambiance)
- **Commentaires et tags** descriptifs

###  Gestion des Véhicules
- **Ajout/suppression** de véhicules personnels
- **Tarifs préférentiels** pour véhicules électriques (-10%)

##  Technologies Utilisées

- **PHP 8.1+** (natif, architecture MVC)
- **MySQL** pour la base de données
- **HTML5** sémantique et accessible
- **CSS3** + **Bootstrap 5.3** pour le design responsive
- **JavaScript natif** pour l'interactivité
- **PDO** pour les interactions base de données

## 📁 Structure du Projet

EcoRide/
├── 📂 app/
│ ├── 📂 Controllers/ # Contrôleurs MVC
│ ├── 📂 Models/ # Modèles de données
│ └── 📂 Views/ # Vues et templates
├── 📂 public/ # Point d'entrée public
│ ├── index.php # Router principal
│ ├── 📂 css/ # Feuilles de style
│ ├── 📂 js/ # Scripts JavaScript
│ └── 📂 assets/ # Images uploadées
├── 📂 config/ # Configuration
│ └── database.php # Configuration BDD
└── 📂 sql/ # Scripts de base de données


## 🚀 Installation

### Prérequis
- **PHP 8.1+** avec extensions PDO et MySQL
- **MySQL 8.0+**
- **Laragon/XAMPP/WAMP** pour le développement

### Étapes d'Installation

1. **Cloner le projet**
URL repo git clone https://github.com/Baya-dev564/EcoRide.git
cd EcoRide


2. **Créer la base de données**
CREATE DATABASE EcoRide CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


3. **Importer la structure**

4. **Configurer la connexion**
// config/database.php
$Host: localhost (127.0.0.1)
Port: 3306
User: root
Password: (vide par défaut avec Laragon)
Base: EcoRide


5. **Configurer le serveur web**
- Pointer le DocumentRoot vers `/public/`
- Ou placer dans `C:\laragon\www\` pour Laragon

## Utilisation

### Interface Utilisateur
1. **S'inscrire** avec pseudo, email et mot de passe
2. **Rechercher un trajet** par lieu et date
3. **Réserver** selon vos besoins
4. **Proposer vos trajets** si vous avez un véhicule
5. **Évaluer** les conducteurs après chaque trajet

### Fonctionnalités Écologiques
- **Réduction tarifaire** de 10% pour véhicules électriques
- **Calcul CO₂** économisé par rapport au transport individuel
- **Badge écologique** pour les conducteurs verts

##  Sécurité

- **Hashage des mots de passe** avec `password_hash()`
- **Validation côté serveur** pour toutes les entrées
- **Échappement des données** contre les injections XSS
- **Architecture MVC** pour séparer logique et présentation

##  Problèmes Courants

### Erreur de Connexion BDD
Vérifier `config/database.php` et les identifiants MySQL

### Page Blanche
Activer l'affichage des erreurs PHP :
ini_set('display_errors', 1);
error_reporting(E_ALL);



##  Évolutions Futures

- **Intégration Google Maps** pour trajets précis
- **Messagerie intégrée** entre utilisateurs
- **Application mobile**
- **Notifications push** en temps réel
- **Système de fidélité** avec récompenses

##  Contribution

1. Fork le projet
2. Créer une branche feature
3. Commiter les changements
4. Pousser vers la branche
5. Ouvrir une Pull Request

##  Contact

**Développeur** : Baya AMELLAL  
**Email** : amellbaya@gmail.com  
**GitHub** : [@Baya-dev564](https://github.com/Baya-dev564)

##  Licence

Ce projet est sous licence **MIT**.

---

**EcoRide -  Premier projet développé dans le cadre de ma formation en développement web **
