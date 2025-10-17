
CREATE DATABASE IF NOT EXISTS EcoRide CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE EcoRide;

CREATE TABLE `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(50) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `credit` int DEFAULT '20',
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(5) DEFAULT NULL,
  `permis_conduire` tinyint(1) DEFAULT '0',
  `photo_profil` varchar(255) DEFAULT NULL,
  `bio` text,
  `note` decimal(2,1) DEFAULT '5.0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pseudo` (`pseudo`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `vehicules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `marque` varchar(50) NOT NULL,
  `modele` varchar(50) NOT NULL,
  `plaque_immatriculation` varchar(10) NOT NULL,
  `date_premiere_immatriculation` date DEFAULT NULL,
  `couleur` varchar(30) DEFAULT NULL,
  `electrique` tinyint(1) DEFAULT '0',
  `nb_places` int DEFAULT '5',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `trajets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conducteur_id` int NOT NULL,
  `vehicule_id` int DEFAULT NULL,
  `lieu_depart` varchar(100) NOT NULL,
  `code_postal_depart` varchar(5) NOT NULL,
  `lieu_arrivee` varchar(100) NOT NULL,
  `code_postal_arrivee` varchar(5) NOT NULL,
  `date_depart` datetime NOT NULL,
  `heure_depart` time DEFAULT NULL,
  `places` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `commission` decimal(10,2) DEFAULT '2.00',
  `vehicule_electrique` tinyint(1) DEFAULT '0',
  `distance_km` decimal(8,2) DEFAULT '0.00',
  `duree_estimee` int DEFAULT NULL,
  `statut` enum('ouvert','complet','annule','termine') DEFAULT 'ouvert',
  `commentaire` text,
  `point_rencontre` text,
  `conditions_particulieres` text,
  `fumeur_accepte` tinyint(1) DEFAULT '0',
  `animaux_acceptes` tinyint(1) DEFAULT '0',
  `bagages_acceptes` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `conducteur_id` (`conducteur_id`),
  CONSTRAINT `trajets_ibfk_1` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trajet_id` int NOT NULL,
  `passager_id` int NOT NULL,
  `nb_places` int NOT NULL DEFAULT '1',
  `statut` enum('confirme','annule','termine') DEFAULT 'confirme',
  `date_reservation` datetime DEFAULT CURRENT_TIMESTAMP,
  `credits_utilises` decimal(10,2) NOT NULL,
  `message_passager` text,
  `telephone_contact` varchar(20) DEFAULT NULL,
  `date_annulation` datetime DEFAULT NULL,
  `motif_annulation` text,
  PRIMARY KEY (`id`),
  KEY `trajet_id` (`trajet_id`),
  KEY `passager_id` (`passager_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`passager_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `credits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `description` text,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `credits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `statistiques_ecologiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `trajet_id` int DEFAULT NULL,
  `distance_km` decimal(8,2) NOT NULL,
  `co2_economise_kg` decimal(8,3) NOT NULL,
  `carburant_economise_litres` decimal(8,3) NOT NULL,
  `argent_economise` decimal(8,2) DEFAULT NULL,
  `nb_personnes_transportees` int DEFAULT NULL,
  `taux_occupation_vehicule` decimal(3,2) DEFAULT NULL,
  `type_action` enum('trajet_propose','trajet_pris','vehicule_electrique') NOT NULL,
  `bonus_ecologique` decimal(5,2) DEFAULT '0.00',
  `date_calcul` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `trajet_id` (`trajet_id`),
  CONSTRAINT `statistiques_ecologiques_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `statistiques_ecologiques_ibfk_2` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE INDEX idx_trajets_depart ON trajets(lieu_depart);
CREATE INDEX idx_trajets_arrivee ON trajets(lieu_arrivee);
CREATE INDEX idx_trajets_date ON trajets(date_depart);
CREATE INDEX idx_reservations_statut ON reservations(statut);
CREATE INDEX idx_credits_type ON credits(type);

SELECT 'Tables EcoRide créées avec succès' AS message;
SELECT 'Système hybride : SQL (données) + NoSQL (avis JSON)' AS info;
