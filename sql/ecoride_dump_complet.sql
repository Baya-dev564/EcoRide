-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: EcoRide
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `credits`
--

DROP TABLE IF EXISTS `credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `credits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `description` text,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_credits_type` (`type`),
  CONSTRAINT `credits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credits`
--

LOCK TABLES `credits` WRITE;
/*!40000 ALTER TABLE `credits` DISABLE KEYS */;
/*!40000 ALTER TABLE `credits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `date_debut_trajet` datetime DEFAULT NULL COMMENT 'Date de dmarrage du trajet par le conducteur',
  `date_fin_trajet` datetime DEFAULT NULL COMMENT 'Date de fin du trajet par le conducteur',
  PRIMARY KEY (`id`),
  KEY `trajet_id` (`trajet_id`),
  KEY `passager_id` (`passager_id`),
  KEY `idx_reservations_statut` (`statut`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`passager_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,3,1,'confirme','2025-09-22 14:01:52',12.00,NULL,NULL,NULL,NULL,NULL,NULL),(2,7,1,1,'termine','2025-09-22 17:22:26',9.00,NULL,NULL,NULL,NULL,'2025-09-22 19:09:48','2025-09-22 19:10:03'),(3,7,1,1,'confirme','2025-09-22 19:21:58',9.00,NULL,NULL,NULL,NULL,NULL,NULL),(4,1,6,1,'confirme','2025-09-23 11:12:52',12.00,NULL,NULL,NULL,NULL,NULL,NULL),(5,8,7,1,'termine','2025-09-24 10:45:28',5.00,NULL,NULL,NULL,NULL,'2025-09-24 10:52:30','2025-09-24 10:52:40'),(6,9,8,1,'confirme','2025-09-30 15:40:53',13.00,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statistiques_ecologiques`
--

DROP TABLE IF EXISTS `statistiques_ecologiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statistiques_ecologiques`
--

LOCK TABLES `statistiques_ecologiques` WRITE;
/*!40000 ALTER TABLE `statistiques_ecologiques` DISABLE KEYS */;
/*!40000 ALTER TABLE `statistiques_ecologiques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trajets`
--

DROP TABLE IF EXISTS `trajets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `statut_moderation` enum('en_attente','valide','refuse') DEFAULT 'en_attente',
  `commentaire` text,
  `point_rencontre` text,
  `conditions_particulieres` text,
  `fumeur_accepte` tinyint(1) DEFAULT '0',
  `animaux_acceptes` tinyint(1) DEFAULT '0',
  `bagages_acceptes` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `depart_latitude` decimal(10,8) DEFAULT NULL,
  `depart_longitude` decimal(11,8) DEFAULT NULL,
  `arrivee_latitude` decimal(10,8) DEFAULT NULL,
  `arrivee_longitude` decimal(11,8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conducteur_id` (`conducteur_id`),
  KEY `idx_trajets_depart` (`lieu_depart`),
  KEY `idx_trajets_arrivee` (`lieu_arrivee`),
  KEY `idx_trajets_date` (`date_depart`),
  CONSTRAINT `trajets_ibfk_1` FOREIGN KEY (`conducteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trajets`
--

LOCK TABLES `trajets` WRITE;
/*!40000 ALTER TABLE `trajets` DISABLE KEYS */;
INSERT INTO `trajets` VALUES (1,1,1,'montpellier','34000','marseille','13000','2025-11-25 00:00:00','09:00:00',1,12.00,2.00,0,77.00,NULL,'ouvert','valide','',NULL,NULL,0,0,1,'2025-09-17 09:07:58',NULL,NULL,NULL,NULL),(2,1,1,'motpellier','34000','paris','75000','2025-10-14 00:00:00','10:00:00',2,18.00,2.00,0,114.00,NULL,'ouvert','refuse','',NULL,NULL,0,0,1,'2025-09-17 16:58:08',NULL,NULL,NULL,NULL),(3,1,1,'motpellier','34000','marseille','13000','2025-09-19 00:00:00','10:00:00',1,9.00,2.00,0,58.00,NULL,'ouvert','valide','',NULL,NULL,0,0,1,'2025-09-17 17:10:33',NULL,NULL,NULL,NULL),(4,1,1,'Paris','75000','marseille','13000','2025-09-25 00:00:00','11:00:00',2,56.00,2.00,0,372.00,NULL,'ouvert','valide','',NULL,NULL,0,0,1,'2025-09-18 09:27:15',NULL,NULL,NULL,NULL),(5,1,1,'Paris','75001','Lyon','69000','2025-12-25 00:00:00','10:00:00',3,68.00,2.00,0,448.00,NULL,'ouvert','valide','',NULL,NULL,0,0,1,'2025-09-19 10:06:49',NULL,NULL,NULL,NULL),(6,1,1,'motpellier','34000','Lyon','69000','2025-09-21 00:00:00','10:00:00',3,65.00,2.00,0,429.00,NULL,'ouvert','valide','',NULL,NULL,0,0,1,'2025-09-19 10:17:06',NULL,NULL,45.75781370,4.83201140),(7,3,2,'Béziers','34000','Montpellier','34000','2025-09-22 00:00:00','20:00:00',0,9.00,2.00,1,61.31,NULL,'termine','valide','',NULL,NULL,0,0,1,'2025-09-22 17:20:46',43.34265620,3.21313070,43.61124220,3.87673370),(8,1,1,'Sète','34200','Montpellier','34000','2025-09-24 00:00:00','13:00:00',0,5.00,2.00,0,27.51,NULL,'termine','valide','',NULL,NULL,0,0,1,'2025-09-24 10:38:43',43.40144340,3.69597710,43.61124220,3.87673370),(9,7,3,'Toulouse','31000','Lyon','69000','2025-09-30 00:00:00','15:50:00',3,13.00,2.00,0,86.00,NULL,'ouvert','valide','Je n\'ai pas de sièges.',NULL,NULL,0,0,1,'2025-09-24 10:49:41',NULL,NULL,NULL,NULL),(10,1,1,'Toulouse','31000','Montpel\'','34000','2025-12-20 00:00:00','09:00:00',2,30.00,2.00,0,195.82,NULL,'ouvert','en_attente','',NULL,NULL,0,0,1,'2025-09-30 15:37:29',43.60446380,1.44424330,43.60807730,3.87642550);
/*!40000 ALTER TABLE `trajets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `email_verifie` tinyint(1) DEFAULT '0',
  `token_verification` varchar(255) DEFAULT NULL,
  `token_expire_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pseudo` (`pseudo`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Baya','payan','Baya',NULL,'amellbaya@gmail.com','$2y$10$lWOZnFqbn0uWlEKkDsMaquw7eVcVvuPunqSGN7LIL.PXISKncZ2b.',51,'0751991635','11 Rue Camille Pelletan','CASTELNAU LE LEZ','34170',1,NULL,'',5.0,'2025-09-13 16:49:43','2025-09-30 15:34:56','user',0,NULL,NULL),(2,'admin','Admin','EcoRide',NULL,'admin@ecoride.fr','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',100,NULL,NULL,NULL,NULL,1,NULL,NULL,5.0,'2025-09-15 17:27:32','2025-09-27 14:41:05','admin',0,NULL,NULL),(3,'Guillaume','payan','guillaume',NULL,'guillaumepayan8@gmail.com','$2y$10$9qqyqe.lnIF4GHUHUGJ7JeaSPA0IACtW2ddIWkroRAzYWr5I8c7nK',28,'0611036579','','Montpellier','',1,NULL,'',5.0,'2025-09-20 10:05:47','2025-09-22 19:58:55','user',0,NULL,NULL),(4,'eline','gerard','eline',NULL,'elinegerard9@gmail.com','$2y$10$HA.ktw1nUJyy1UuNTyeVKuq7JnwQMIlBZwDApJak03qbUEn4wyQta',20,'0752369514','','paris','',1,NULL,'',5.0,'2025-09-22 20:45:35',NULL,'user',0,'05775d9ef84585478fd303759a3a5d13a61ccf160cdcafa9d7dae994e5ded74f','2025-09-23 20:45:35'),(5,'TESTEUR','email','test3',NULL,'sissaalba7@gmail.com','$2y$10$31yeAQT4se5LVgR6VMClA.Kibca.icBumxJjokPn9cBJTBbpe2GCa',20,'0785452636','','Paris','',1,NULL,'',5.0,'2025-09-22 20:55:31','2025-09-22 20:59:31','user',0,'da60430fae5669ddd97a559e82065bcabf5c5849bf486aa83913e62b5e7bff9b','2025-09-23 20:55:31'),(6,'TestUser1','USER','Testeur',NULL,'testuser1@ecoride.fr','$2y$10$7GUhK4I2WsF2WT7YxCeQCu5pP83VmoQmJN6PhOLsmOh5ZhzPhqAwa',8,'0452123687','','Lyon','',1,NULL,'',5.0,'2025-09-23 11:12:26','2025-09-23 11:12:34','user',0,NULL,NULL),(7,'Guillaume34000','Galibert','Guillaume',NULL,'guillaume_versace@hotmail.fr','$2y$10$qY18RNPmYqvud0zzZ/bTietyRrGA/T91kA/2TcU9jURbyzBmjyx4u',15,'0625487936','','Montpellier','',1,NULL,'',5.0,'2025-09-24 10:43:10','2025-09-28 09:59:09','user',0,NULL,NULL),(8,'TestUser3','teste3','user',NULL,'test3@ecoride.com','$2y$10$mK8GRfJ/VDL.xjLh9Gzf4OtVFHBNDg5zuTs7lBZjEX4za.kvxjiHa',7,'0254136894','','Toulouse','',1,NULL,'',5.0,'2025-09-30 15:40:03','2025-09-30 15:40:10','user',0,NULL,NULL);
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicules`
--

DROP TABLE IF EXISTS `vehicules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicules`
--

LOCK TABLES `vehicules` WRITE;
/*!40000 ALTER TABLE `vehicules` DISABLE KEYS */;
INSERT INTO `vehicules` VALUES (1,1,'BMW','serie3','AB-123-CD',NULL,'noire',0,4,'2025-09-15 13:52:10'),(2,3,'Renault','zoe','MK-676-DZ',NULL,'Blanc',1,4,'2025-09-22 17:19:34'),(3,7,'Peugeot','206','CC-555-ZZ',NULL,'Jaune',0,4,'2025-09-24 10:47:36');
/*!40000 ALTER TABLE `vehicules` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-15 10:32:26
