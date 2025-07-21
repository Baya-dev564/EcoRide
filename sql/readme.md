# Scripts SQL EcoRide - Données réelles

## Description
Scripts SQL basés sur les  données du système EcoRide en production. Contient les utilisateurs, véhicules et trajets réels du projet.

## Données incluses

### Utilisateurs réels (7 au total)
- **Baya** (ID 1) : Utilisateur principal avec permis, 3 véhicules
- **alex, marie, julien** (ID 2-4) : Utilisateurs de test
- **TestUser1** (ID 5) : Utilisateur de test supplémentaire  
- **testeur85** (ID 6) : Thomas Dupont, 50 crédits
- **admin** (ID 7) : Administrateur système

### Véhicules réels (3 au total)
- **Renault Clio** (ID 2) : Blanc, thermique, 4 places
- **Tesla Model 3** (ID 3) : Noir, électrique, 4 places
- **Toyota Prius** (ID 4) : Gris, hybride, 4 places

### Trajets réels (5 au total)
- **Montpellier → Paris** : 2 trajets (56€ et 65€)
- **Castelnau-le-Lez → Marseille** : 35€
- **Montpellier → Lyon** : 45€
- **Détails complets** : Points de rencontre, conditions

## Spécificités des données

### Utilisateur principal "Baya"
- **Localisation** : Castelnau-le-Lez (34170)
- **Statut** : Conducteur avec permis
- **Véhicules** : 3 (mix thermique/électrique/hybride)
- **Activité** : 5 trajets proposés

### Système de crédits
- **Débutants** : 18-20 crédits
- **Expérimentés** : 25-50 crédits  
- **Admin** : 100 crédits

### Impact écologique
- **CO2 économisé** : Calculé selon distance
- **Bonus électrique** : Véhicules Tesla et Prius
- **Statistiques** : Suivi automatique

## Installation

