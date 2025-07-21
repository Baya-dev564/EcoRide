-- =============================================
-- Données réelles du système EcoRide
-- Basé sur les données existantes de la base
-- Utilisateurs, véhicules et trajets réels
-- =============================================

USE ecoride;

-- ======= UTILISATEURS RÉELS =======
-- Insertion des utilisateurs avec mots de passe hashés
INSERT INTO utilisateurs (id, pseudo, nom, prenom, email, mot_de_passe, credit, ville, code_postal, permis_conduire, role, created_at) VALUES
(1, 'Baya', 'PAYAN', 'Baya', 'baya@ecoride.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 20, 'CASTELNAU LE LEZ', '34170', 1, 'user', '2025-07-11 14:31:57'),
(2, 'alex', NULL, NULL, 'alex@ecoride.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 25, NULL, NULL, 0, 'user', '2025-07-11 14:44:29'),
(3, 'marie', NULL, NULL, 'marie@ecoride.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, NULL, NULL, 0, 'user', '2025-07-11 14:44:29'),
(4, 'julien', NULL, NULL, 'julien@ecoride.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 18, NULL, NULL, 0, 'user', '2025-07-11 14:44:29'),
(5, 'TestUser1', NULL, NULL, 'testuser1@ecoride.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 20, NULL, NULL, 0, 'user', '2025-07-12 16:23:48'),
(6, 'testeur85', 'Dupont', 'Thomas', 'thomas.dupont@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 50, 'Montpellier', '34000', 0, 'user', '2025-07-14 22:31:19'),
(7, 'admin', 'Administrateur', 'EcoRide', 'admin@ecoride.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 100, NULL, NULL, 0, 'admin', '2025-07-17 17:53:50');

-- ======= VÉHICULES RÉELS =======
-- Parc automobile de Baya (utilisateur principal)
INSERT INTO vehicules (id, utilisateur_id, marque, modele, plaque_immatriculation, date_premiere_immatriculation, couleur, electrique, nb_places, created_at) VALUES
(2, 1, 'Renault', 'Clio', 'AB-123-CD', NULL, 'Blanc', 0, 4, '2025-07-13 17:18:05'),
(3, 1, 'Tesla', 'Model 3', 'EF-456-GH', NULL, 'Noir', 1, 4, '2025-07-14 15:47:51'),
(4, 1, 'Toyota', 'Prius', 'IJ-789-KL', NULL, 'Gris', 1, 4, '2025-07-14 15:47:51');

-- ======= TRAJETS RÉELS =======
-- Trajets proposés par Baya depuis Montpellier
INSERT INTO trajets (id, conducteur_id, vehicule_id, lieu_depart, code_postal_depart, lieu_arrivee, code_postal_arrivee, date_depart, heure_depart, places, prix, commission, vehicule_electrique, distance_km, duree_estimee, statut, commentaire, point_rencontre, conditions_particulieres, fumeur_accepte, animaux_acceptes, bagages_acceptes, created_at) VALUES
(1, 1, 2, 'montpellier', '34000', 'paris', '75000', '2025-07-20 00:00:00', '08:30:00', 2, 56.00, 2.00, 0, 370.00, NULL, 'ouvert', '', NULL, NULL, 0, 0, 1, '2025-07-13 18:09:58'),
(2, 1, NULL, 'Montpellier', '34000', 'Paris', '75001', '2025-07-20 08:00:00', '08:00:00', 3, 65.00, 2.00, 0, 0.00, NULL, 'ouvert', NULL, NULL, NULL, 0, 0, 1, '2025-07-14 16:01:25'),
(3, 1, 3, 'Montpellier', '34000', 'Paris', '75001', '2025-07-20 08:00:00', '08:00:00', 3, 65.00, 2.00, 1, 750.00, 420, 'ouvert', 'Trajet confortable en Tesla', 'Gare Saint-Roch - Parking niveau 2', 'Départ ponctuel, véhicule non-fumeur', 0, 0, 1, '2025-07-14 16:07:56'),
(4, 1, 2, 'Castelnau-le-Lez', '34170', 'Marseille', '13001', '2025-07-18 14:30:00', '14:30:00', 2, 35.00, 2.00, 0, 280.00, 180, 'ouvert', 'Départ depuis Castelnau', 'Centre commercial Odysseum', 'Musique autorisée', 0, 1, 1, '2025-07-14 16:07:56'),
(5, 1, 4, 'Montpellier', '34000', 'Lyon', '69001', '2025-07-22 09:15:00', '09:15:00', 2, 45.00, 2.00, 1, 465.00, 270, 'ouvert', 'Trajet écologique en Prius', 'Place de la Comédie', 'Véhicule hybride', 0, 0, 1, '2025-07-14 16:07:56'),
(20, 2, 1, 'Cannes', '06400', 'Nice', '06000', '2025-08-30 19:30:00', '19:30:00', 3, 15.00, 1.00, 0, 45.00, 35, 'ouvert', 'Retour de soirée Cannes-Nice', 'Palais des Festivals', 'Trajet nocturne sécurisé', 0, 0, 0, '2025-07-21 20:00:00'),
(18, 3, 2, 'Toulouse', '31000', 'Paris', '75001', '2025-08-25 13:20:00', '13:20:00', 3, 68.00, 3.00, 0, 680.00, 410, 'ouvert', 'Retour de vacances du Sud-Ouest', 'Gare Matabiau', 'Trajet longue distance, pauses prévues', 0, 1, 1, '2025-07-21 18:00:00'),
(14, 2, 2, 'Nantes', '44000', 'Paris', '75001', '2025-08-15 08:15:00', '08:15:00', 2, 48.00, 2.50, 0, 385.00, 230, 'ouvert', 'Retour de vacances bretonnes', 'Gare SNCF - Parking Nord', 'Véhicule familial, sièges confortables', 0, 1, 1, '2025-07-21 14:10:00');

-- ======= CRÉDITS DE BIENVENUE =======
-- Transactions initiales pour les utilisateurs
INSERT INTO credits (user_id, montant, type, description, date) VALUES
(1, 20.00, 'credit', 'Crédits de bienvenue EcoRide', '2025-07-11 14:31:57'),
(2, 25.00, 'credit', 'Crédits de bienvenue EcoRide', '2025-07-11 14:44:29'),
(3, 30.00, 'credit', 'Crédits de bienvenue EcoRide', '2025-07-11 14:44:29'),
(4, 18.00, 'credit', 'Crédits de bienvenue EcoRide', '2025-07-11 14:44:29'),
(5, 20.00, 'credit', 'Crédits de bienvenue EcoRide', '2025-07-12 16:23:48'),
(6, 50.00, 'credit', 'Crédits de bienvenue EcoRide', '2025-07-14 22:31:19'),
(7, 100.00, 'credit', 'Crédits administrateur système', '2025-07-17 17:53:50');

-- ======= STATISTIQUES ÉCOLOGIQUES =======
-- Impact environnemental des trajets de Baya
INSERT INTO statistiques_ecologiques (utilisateur_id, trajet_id, distance_km, co2_economise_kg, carburant_economise_litres, type_action, bonus_ecologique, date_calcul) VALUES
(1, 1, 370.00, 44.4, 16.3, 'trajet_propose', 3.00, '2025-07-13 18:09:58'),
(1, 3, 750.00, 90.0, 0.0, 'vehicule_electrique', 10.00, '2025-07-14 16:07:56'),
(1, 4, 280.00, 33.6, 12.3, 'trajet_propose', 2.50, '2025-07-14 16:07:56'),
(1, 5, 465.00, 55.8, 0.0, 'vehicule_electrique', 6.00, '2025-07-14 16:07:56');

-- Messages de confirmation
SELECT 'Données réelles EcoRide insérées avec succès' AS message;
SELECT 'Utilisateurs créés: 7 (dont 1 admin)' AS info_users;
SELECT 'Véhicules créés: 3 (Clio, Tesla, Prius)' AS info_vehicles;
SELECT 'Trajets créés: 5 (Montpellier vers Paris/Lyon/Marseille)' AS info_trips;
SELECT 'Système hybride: SQL + NoSQL (avis)' AS info_system;
