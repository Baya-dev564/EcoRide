-- =============================================
-- Script de création de la base de données EcoRide
-- Projet : TP Développement Web - Covoiturage écologique
-- =============================================

-- Suppression de la base si elle existe déjà
DROP DATABASE IF EXISTS ecoride;

-- Création de la base de données avec encodage UTF-8
CREATE DATABASE EcoRide 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_0900_ai_ci;

-- Utilisation de la base de données
USE EcoRide;

-- Information système
SELECT 'Base de données EcoRide créée avec succès' AS message;
-- =============================================
-- Script de création des tables EcoRide
-- Structure basée sur la base de données réelle
-- 6 tables principales + système NoSQL pour avis
-- =============================================

