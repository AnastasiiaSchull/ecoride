-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: ecoride
-- ------------------------------------------------------
-- Server version	9.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `avis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conducteur_id` int DEFAULT NULL,
  `passager_id` int DEFAULT NULL,
  `note` int DEFAULT NULL,
  `commentaire` text,
  `approuve` tinyint(1) DEFAULT '0',
  `is_problem` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `conducteur_id` (`conducteur_id`),
  KEY `passager_id` (`passager_id`),
  CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`conducteur_id`) REFERENCES `users` (`id`),
  CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`passager_id`) REFERENCES `users` (`id`),
  CONSTRAINT `avis_chk_1` CHECK ((`note` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (1,6,1,5,'Excellent conducteur !',1,0),(2,7,2,4,'Sympa et ponctuel',1,0),(3,8,3,3,'Trajet correct',1,0),(4,7,4,2,'Conduite un peu rapide',1,0),(5,6,5,1,'Retard au départ',1,0);
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `preferences`
--

DROP TABLE IF EXISTS `preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `preferences`
--

LOCK TABLES `preferences` WRITE;
/*!40000 ALTER TABLE `preferences` DISABLE KEYS */;
INSERT INTO `preferences` VALUES (2,'animaux'),(5,'climatisation'),(1,'fumeur'),(3,'musique'),(4,'silence');
/*!40000 ALTER TABLE `preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trajet_id` int DEFAULT NULL,
  `passager_id` int DEFAULT NULL,
  `statut` enum('en_attente','confirmee','annulee') DEFAULT 'en_attente',
  `date_reservation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trajet_id` (`trajet_id`),
  KEY `passager_id` (`passager_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`trajet_id`) REFERENCES `trajets` (`id`),
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`passager_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,46,3,'confirmee','2025-06-01 08:00:00'),(2,47,4,'confirmee','2025-06-01 08:00:00'),(3,48,5,'confirmee','2025-06-01 08:00:00'),(4,49,3,'confirmee','2025-06-01 08:00:00'),(5,50,4,'confirmee','2025-06-01 08:00:00'),(6,51,5,'confirmee','2025-06-01 08:00:00'),(7,52,3,'confirmee','2025-06-01 08:00:00'),(8,53,4,'confirmee','2025-06-01 08:00:00'),(9,54,5,'confirmee','2025-06-01 08:00:00'),(10,55,3,'confirmee','2025-06-01 08:00:00');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (4,'admin'),(1,'conducteur'),(3,'employe'),(2,'passager');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trajets`
--

DROP TABLE IF EXISTS `trajets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trajets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `conducteur_id` int DEFAULT NULL,
  `vehicule_id` int DEFAULT NULL,
  `ville_depart` varchar(100) DEFAULT NULL,
  `ville_arrivee` varchar(100) DEFAULT NULL,
  `date_depart` datetime DEFAULT NULL,
  `date_arrivee` datetime DEFAULT NULL,
  `prix` decimal(6,2) DEFAULT NULL,
  `places_dispo` int DEFAULT NULL,
  `eco` tinyint(1) DEFAULT '0',
  `statut` enum('à_venir','en_cours','terminé') DEFAULT 'à_venir',
  PRIMARY KEY (`id`),
  KEY `conducteur_id` (`conducteur_id`),
  KEY `vehicule_id` (`vehicule_id`),
  CONSTRAINT `trajets_ibfk_1` FOREIGN KEY (`conducteur_id`) REFERENCES `users` (`id`),
  CONSTRAINT `trajets_ibfk_2` FOREIGN KEY (`vehicule_id`) REFERENCES `vehicules` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trajets`
--

LOCK TABLES `trajets` WRITE;
/*!40000 ALTER TABLE `trajets` DISABLE KEYS */;
INSERT INTO `trajets` VALUES (37,1,2,'Paris','Lyon','2025-12-05 10:00:00','2025-12-05 14:00:00',25.00,3,1,'à_venir'),(38,2,15,'Paris','Lyon','2025-12-12 14:30:00','2025-12-12 18:00:00',28.00,2,0,'à_venir'),(39,8,1,'Paris','Lyon','2025-12-19 09:00:00','2025-12-19 13:00:00',22.00,4,0,'à_venir'),(40,8,13,'Lille','Paris','2025-12-03 08:00:00','2025-12-03 12:00:00',20.00,3,1,'à_venir'),(41,8,14,'Lille','Paris','2025-12-10 11:00:00','2025-12-10 15:00:00',23.00,2,0,'à_venir'),(42,7,11,'Lille','Paris','2025-12-17 13:00:00','2025-12-17 17:00:00',25.00,3,1,'à_venir'),(43,7,12,'Lyon','Marseille','2025-12-06 15:00:00','2025-12-06 19:00:00',30.00,2,0,'à_venir'),(44,6,10,'Lyon','Marseille','2025-12-13 10:00:00','2025-12-13 14:00:00',32.00,3,1,'à_venir'),(45,6,9,'Lyon','Marseille','2025-12-20 16:30:00','2025-12-20 20:30:00',28.00,2,0,'à_venir'),(46,1,2,'Lille','Paris','2025-06-03 08:00:00','2025-06-03 12:00:00',25.00,3,0,'terminé'),(47,1,2,'Paris','Lyon','2025-06-04 09:30:00','2025-06-04 14:00:00',30.00,4,1,'terminé'),(48,3,15,'Reims','Paris','2025-06-12 09:00:00','2025-06-12 11:00:00',12.00,1,1,'terminé'),(49,2,15,'Lyon','Marseille','2025-06-05 07:00:00','2025-06-05 11:30:00',28.50,2,0,'terminé'),(50,2,15,'Paris','Bordeaux','2025-06-06 10:00:00','2025-06-06 16:00:00',35.00,3,1,'terminé'),(51,4,14,'Toulouse','Nice','2025-06-07 06:00:00','2025-06-07 13:00:00',40.00,1,0,'terminé'),(52,4,13,'Marseille','Lille','2025-06-08 09:00:00','2025-06-08 19:00:00',50.00,4,1,'terminé'),(53,3,15,'Nantes','Strasbourg','2025-06-09 08:00:00','2025-06-09 18:00:00',45.00,2,0,'terminé'),(54,1,2,'Lyon','Reims','2025-06-10 07:30:00','2025-06-10 11:00:00',22.00,3,1,'terminé'),(55,2,15,'Paris','Toulouse','2025-06-11 08:00:00','2025-06-11 14:30:00',38.00,2,1,'terminé');
/*!40000 ALTER TABLE `trajets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (3,1),(4,1),(5,1),(6,1),(7,1),(1,2),(2,2),(6,2),(7,2),(8,2),(11,2),(9,3),(10,3),(12,3),(11,4);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `credits` int DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `photo` varchar(255) DEFAULT 'default.png',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Jean Dupont','jean@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:18:38',120,1,'default.png'),(2,'Alice Martin','alice@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:18:38',50,1,'default.png'),(3,'Luc Moreau','luc@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:18:38',150,0,'default.png'),(4,'Emma Lefevre','emma@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:18:38',40,1,'default.png'),(5,'Francis Dupont','francis@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:27:20',120,1,'default.png'),(6,'Alice Spivak','spivak@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:27:20',50,1,'default.png'),(7,'Luc Schull','schull@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:27:20',150,1,'default.png'),(8,'Nastia Schull','nastia@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:27:20',40,1,'default.png'),(9,'Employé1','employe1@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:33:49',0,1,'default.png'),(10,'Employé2','employe2@gmail.com','$2y$10$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i','2025-07-21 17:33:49',0,1,'default.png'),(11,'Admin','admin@gmail.com','$2y$10$bDvAHDnxx2NlHVCmFdpLP.0oVmqWiSCirbJVrqAGakGSa92rsPw/i','2025-07-22 00:18:41',0,1,'default.png'),(12,'Employé3','employe3@gmail.com','$2y$10$S2pXNkw13ebLibyoUHqzweCXXkCMkbdRmNnBtn8ZkKEWhf0r9mAh.','2025-07-22 00:32:21',0,1,'default.png');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicule_preferences`
--

DROP TABLE IF EXISTS `vehicule_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicule_preferences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vehicule_id` int NOT NULL,
  `preference_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicule_id` (`vehicule_id`),
  KEY `preference_id` (`preference_id`),
  CONSTRAINT `vehicule_preferences_ibfk_1` FOREIGN KEY (`vehicule_id`) REFERENCES `vehicules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vehicule_preferences_ibfk_2` FOREIGN KEY (`preference_id`) REFERENCES `preferences` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicule_preferences`
--

LOCK TABLES `vehicule_preferences` WRITE;
/*!40000 ALTER TABLE `vehicule_preferences` DISABLE KEYS */;
INSERT INTO `vehicule_preferences` VALUES (1,1,1),(2,1,3),(3,2,2),(4,2,5),(5,9,4),(6,10,5),(7,11,1),(8,12,2),(9,13,3),(10,14,4),(11,15,5),(12,15,1);
/*!40000 ALTER TABLE `vehicule_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vehicules`
--

DROP TABLE IF EXISTS `vehicules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `marque` varchar(50) DEFAULT NULL,
  `modele` varchar(50) DEFAULT NULL,
  `couleur` varchar(30) DEFAULT NULL,
  `energie` enum('electrique','essence','diesel') DEFAULT 'essence',
  `places` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicules`
--

LOCK TABLES `vehicules` WRITE;
/*!40000 ALTER TABLE `vehicules` DISABLE KEYS */;
INSERT INTO `vehicules` VALUES (1,8,'Peugeot','308','noir','essence',4),(2,1,'Renault','Clio','gris','electrique',4),(9,6,'Toyota','Yaris','rouge','essence',4),(10,6,'Tesla','Model 3','noir','electrique',5),(11,7,'Peugeot','208','gris','diesel',4),(12,7,'Renault','ZOE','bleu','electrique',4),(13,8,'Volkswagen','Golf','blanc','essence',4),(14,8,'Nissan','Leaf','vert','electrique',5),(15,2,'Citroën','C4','bleu','essence',4);
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

-- Dump completed on 2025-07-22 13:28:31
