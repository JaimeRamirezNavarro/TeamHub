-- MySQL dump 10.13  Distrib 8.0.45, for Linux (aarch64)
--
-- Host: localhost    Database: proyecto_dual
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `status` enum('pendiente','en_progreso','completada') DEFAULT 'pendiente',
  `user_id` int DEFAULT NULL,
  `team_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `team_id` int NOT NULL,
  `role` enum('admin','member') DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`team_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_members`
--

LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
INSERT INTO `team_members` VALUES (1,2,1,'admin','2026-02-12 20:25:26'),(2,3,1,'member','2026-02-12 20:25:26'),(3,4,1,'member','2026-02-12 20:25:26'),(4,3,2,'admin','2026-02-12 20:25:26'),(5,5,2,'member','2026-02-12 20:25:26'),(6,2,3,'member','2026-02-12 20:25:26'),(7,4,3,'admin','2026-02-12 20:25:26'),(8,5,4,'admin','2026-02-12 20:25:26'),(9,3,4,'member','2026-02-12 20:25:26'),(10,6,1,'member','2026-02-16 09:32:08');
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` enum('En Progreso','Completado','Pausado','Cancelado') DEFAULT 'En Progreso',
  `gather_space_id` varchar(255) DEFAULT NULL,
  `gather_space_url` varchar(500) DEFAULT NULL,
  `gather_enabled` tinyint(1) DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Proyecto Alpha','Desarrollo de API REST para integración con sistemas externos','En Progreso',NULL,NULL,0,1,'2026-02-12 20:25:26'),(2,'Marketing Q1','Campaña publicitaria digital para el primer trimestre','En Progreso',NULL,NULL,0,1,'2026-02-12 20:25:26'),(3,'Infraestructura Cloud','Migración a arquitectura cloud y optimización de servidores','En Progreso',NULL,NULL,0,1,'2026-02-12 20:25:26'),(4,'Diseño UI/UX','Renovación completa de la identidad visual de la marca','Pausado',NULL,NULL,0,1,'2026-02-12 20:25:26'),(5,'Infraestructura','Mantenimiento y actualización de servidores y redes.','En Progreso','MwUYFMPvLQtJtS9P\\pruebas','https://app.gather.town/app/MwUYFMPvLQtJtS9P/pruebas',1,1,'2026-02-16 10:10:44'),(6,'Recursos Humanos','Gestión de nuevas contrataciones y bienestar laboral.','En Progreso','MwUYFMPvLQtJtS9P\\pruebas','https://app.gather.town/app/MwUYFMPvLQtJtS9P/pruebas',1,1,'2026-02-16 10:10:44');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `remember_token_expiry` datetime DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('Oficina','Teletrabajo','Ausente','Reunión','Desconectado') DEFAULT 'Oficina',
  `last_activity` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@teamhub.com','$2y$10$d4X38C6SFfbLIRfdqp6xyel7WiEYYG0IwYp.PnxPAvVAtJFf4zRMS',NULL,NULL,'admin','Oficina','2026-02-16 09:13:50','2026-02-12 20:25:26'),(2,'Sergio','sergio@teamhub.com','$2y$10$d4X38C6SFfbLIRfdqp6xyel7WiEYYG0IwYp.PnxPAvVAtJFf4zRMS',NULL,NULL,'user','Oficina','2026-02-16 09:13:50','2026-02-12 20:25:26'),(3,'David','david@teamhub.com','$2y$10$d4X38C6SFfbLIRfdqp6xyel7WiEYYG0IwYp.PnxPAvVAtJFf4zRMS',NULL,NULL,'user','Teletrabajo','2026-02-16 09:13:50','2026-02-12 20:25:26'),(4,'Laura','laura@teamhub.com','$2y$10$d4X38C6SFfbLIRfdqp6xyel7WiEYYG0IwYp.PnxPAvVAtJFf4zRMS',NULL,NULL,'user','Oficina','2026-02-16 09:13:50','2026-02-12 20:25:26'),(5,'Elena','elena@teamhub.com','$2y$10$d4X38C6SFfbLIRfdqp6xyel7WiEYYG0IwYp.PnxPAvVAtJFf4zRMS',NULL,NULL,'user','Reunión','2026-02-16 09:13:50','2026-02-12 20:25:26'),(6,'Jaime Ramirez Navarro','jaimeramireznavarro@teamhub.com','$2y$10$e400rqm8Au0M0O6fx4xXHObeMgn0Kl5ZnwmMMndEtNGryMbimHHYi','97856839e60fa7f85f412e921df71c5385b513e7020fe3d812d2b4fc472a395d','2026-03-18 09:32:03','user','Oficina','2026-02-16 10:11:51','2026-02-16 09:32:03');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-16 10:20:55
