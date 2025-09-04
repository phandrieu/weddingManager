-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: app
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.24.04.1

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
-- Table structure for table `doctrine_migration_versions`
--

drop database if exists app;

create database app
  default character set utf8mb4
  default collate utf8mb4_unicode_ci;

use app;

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20250903143129','2025-09-03 23:00:52',364),('DoctrineMigrations\\Version20250903191806','2025-09-03 23:00:53',63);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `song`
--

DROP TABLE IF EXISTS `song`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `song` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type_id` int NOT NULL,
  `preview_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lyrics` longtext COLLATE utf8mb4_unicode_ci,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_33EDEEA1C54C8C93` (`type_id`),
  CONSTRAINT `FK_33EDEEA1C54C8C93` FOREIGN KEY (`type_id`) REFERENCES `song_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `song`
--

LOCK TABLES `song` WRITE;
/*!40000 ALTER TABLE `song` DISABLE KEYS */;
INSERT INTO `song` VALUES (1,5,'https://youtu.be/-PqxDNXqHZg?si=x9-WQoVxfPGARRWL',NULL,'Alleluia - Messe de Saint Paul'),(2,5,'https://youtu.be/ndI0r-LmMKk?si=npStse1jtu9BueHr',NULL,'Alleluia - Psaume 117'),(3,5,'https://youtu.be/az4jBmvd3mc?si=iI7ny_MKzp__zQvv',NULL,'Alleluia - Taize'),(4,5,'https://youtu.be/q2G5xXRyGSg?si=xLL7IIRhjtAZqnIR',NULL,'Alleluia - Messe de Saint Boniface'),(5,5,'https://youtu.be/J14RLPPHWmA?si=9dYSdh14E-HWTuiQ',NULL,'Alleluia - Messe de Saint Jean'),(6,5,'https://youtu.be/kozmpe-qGcs?si=5ZvEUkibampuK4nl',NULL,'Alleluia - Messe du Frat'),(7,5,'https://youtu.be/JAleY40oNsI?si=rWTwn8RRkoOVUmgd',NULL,'Alleluia - Messe de la Grâce'),(8,5,'https://youtu.be/-PqxDNXqHZg?si=7F483Z-iFJTjvnLC',NULL,'Alleluia - Messe de Saint Paul'),(9,7,'https://youtu.be/H8vFcoTxezI?si=4tJkijSWTPSYqKN3',NULL,'Esprit de lumière, esprit créateur'),(10,7,'https://youtu.be/snKoJTmVrxg?si=VDhuxFDWffiIJuJL',NULL,'Inonde ce lieu de ta présence'),(11,7,'https://youtu.be/3f6lVPH7MnY?si=FYopm98LbD2k6LYD',NULL,'Saint-Esprit (voici mon coeur)'),(12,7,'https://youtu.be/aoz425unmew?si=qZpe_-ozRkLePtgD',NULL,'Viens esprit de sainteté'),(13,7,'https://youtu.be/X8TdQeThRbg?si=gddVNOnIvxl8rS5X',NULL,'Jésus, toi qui a promis'),(14,7,'https://youtu.be/RTaEMQgrNqo?si=VoP8_ASTYgDE_3v8',NULL,'Feu dévorant'),(15,7,'https://youtu.be/v2iRuO_u4aY?si=-KGpG0p8K19z3anV',NULL,'À jamais tu es saint'),(16,7,'https://youtu.be/ivUb1K0B0zE?si=J0yu4DntpIoW0_C0',NULL,'Yeshua'),(17,7,'https://youtu.be/pR2yEnMpYmE?si=rL4fZ_vCdzJ8-U_o',NULL,'Oceans'),(18,18,'https://youtu.be/OvXIv8tKbgE?si=IyCbTYc-OoWbCGrd',NULL,'Couronnée d\'étoiles'),(19,18,'https://youtu.be/EgNozp8Q4QY?si=qfjsc9F8KVdZ8vdO',NULL,'Ave Maria'),(20,18,'https://youtu.be/54xs4rJr3eg?si=xhYJ_3Vvv1G749vi',NULL,'Ô mère bien aimée'),(21,18,'https://youtu.be/6dlCmAWZ8q4?si=elpO3l1rWs3X0qik',NULL,'Regarde l\'étoile'),(22,10,'https://youtu.be/ijHz0HfRt10?si=f2DpbTLUft6K2pRT',NULL,'Magnificat (Taize)'),(23,10,'https://youtu.be/0i59nz7iNnw?si=09s4ne2_JhNUvvmm',NULL,'Chantez avec moi le Seigneur'),(24,10,'https://youtu.be/3YoTvPYCJbE?si=XZbiTAiGxbv4FMGX',NULL,'Evenou Shalom'),(25,10,'https://youtu.be/3lpgygl9Uq0?si=MUir9rR-UAceM4hu',NULL,'Rendons gloire à notre Dieu'),(26,10,'https://youtu.be/FxAFXXa2310?si=4jV7uwnVgrJ2YLMn',NULL,'Pour tes merveilles'),(27,2,'https://youtu.be/MiSEn6a5W0Y?si=5TJZ-SfmABn_kYz8',NULL,'Bienvenue'),(28,2,'https://youtu.be/SUvjVNLtwhY?si=ZeucuxJqzY7zXKE1',NULL,'Que vive mon âme à te louer (C513)'),(29,2,'https://youtu.be/XvGpsEsVEPg?si=Tn3xgqLvnR6MSXD1',NULL,'Jubliez, criez de joie'),(30,2,'https://youtu.be/bXa5gJtrdeg?si=V-X5a0Auk0A3VuYO',NULL,'Écoute ton Dieu t\'appelle'),(31,2,'https://youtu.be/XGZeLG0xLb4?si=abeRl33fH1OOlNWP',NULL,'Venez chantons notre Dieu (A509)'),(32,2,'https://youtu.be/onPVN4Mu6bg?si=WV3WLSLv-Ie6T-DI',NULL,'Que ma bouche chante ta louange'),(33,2,'https://youtu.be/UxJnCBp6HkA?si=DPvQ_yyLYyTCIq16',NULL,'Chantez priez célébrez le Seigneur (A40-73)'),(34,2,'https://youtu.be/XKFnqWFGScA?si=HrmZ0HLwaLBPDurq',NULL,'Qu\'exulte tout l\'univers (DEV 44-72)'),(35,2,'https://youtu.be/dEgy7JS7uCM?si=OuxQfsQatm70KLPl',NULL,'Christ est la lumière'),(36,2,'https://youtu.be/Lrx1UVtftYc?si=mObtzmi2x_35Z--R',NULL,'Hosanna, ouvrons les portes'),(37,2,'https://youtu.be/GWhy_fEg4mU?si=rxv6cet_pHLD-cTs',NULL,'Yahwe (l\'éternel est mon berger)'),(38,2,'https://youtu.be/3s3lh6rWpYg?si=6-yZdxNfbzgbW4cr',NULL,'Louez Adonaï'),(39,2,'https://youtu.be/WS12Smxk9o8?si=4507KQgJJFIvJID7',NULL,'Tu es bon'),(40,19,'https://youtu.be/CpWILwqjICs?si=uEbiKEHvSIEbVbqM',NULL,'Par toute la terre'),(41,19,'https://youtu.be/FBlBWdWYgdk?si=T6IiU1FptDTG916g',NULL,'Il est temps de quitter vos tombeaux'),(42,19,'https://youtu.be/RfcNHdVosus?si=A5KpNTuyY0DtG6HB',NULL,'Comment ne pas te louer'),(43,19,'https://youtu.be/ISpcSDiqCsY?si=bM5xM1GEoiq_LpOu',NULL,'Vivre comme le Christ'),(44,1,'https://youtu.be/v7x9jqsmwf8?si=_Xk3worNOh_2OOFd',NULL,'Aimer c\'est tout donner'),(45,1,'https://youtu.be/JiH5IXCO7VU?si=p9zLHaE21xaQSXNr',NULL,'L\'amour jamais ne passera'),(46,1,'https://youtu.be/NHbBDCGa4sU?si=DvU13tn0og_y8Jgb',NULL,'Je te donne tout'),(47,1,'https://youtu.be/mzvJVoHEDH4?si=35jj686vxkIx3XYH',NULL,'Cantique des cantiques'),(48,1,'https://youtu.be/SxxXK05hjXA?si=EJSakUUDkiJ28Rtu',NULL,'Comme l\'argile'),(49,1,'https://youtu.be/l7P0Q6qI7cw?si=I7KxYEoM7JSJfwh6',NULL,'Ce nom est si merveilleux'),(50,1,'https://youtu.be/SaDXENya6OA?si=P-G_2VhrX3GGoxOZ',NULL,'Eveille toi mon âme'),(51,1,'https://youtu.be/y8AWFf7EAc4?si=n4aX4Y9PVGtK_Dxb',NULL,'Hallelujah'),(52,1,'https://youtu.be/HsCp5LG_zNE?si=HXnRTnS52lsAnTZl',NULL,'Amazing grace');
/*!40000 ALTER TABLE `song` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `song_type`
--

DROP TABLE IF EXISTS `song_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `song_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `song_type`
--

LOCK TABLES `song_type` WRITE;
/*!40000 ALTER TABLE `song_type` DISABLE KEYS */;
INSERT INTO `song_type` VALUES (1,'Entrée des Mariés'),(2,'Entrée'),(3,'Gloria'),(4,'Psaume'),(5,'Acclamation Évangile'),(6,'Chant Méditatif'),(7,'Chant à l\'Esprit'),(8,'Litanie des Saints'),(9,'Credo'),(10,'Chant après l\'échange des consentements'),(11,'Prière Universelle'),(12,'Offertoire'),(13,'Sanctus'),(14,'Anamnèse'),(15,'Notre Père'),(16,'Agnus'),(17,'Chant de communion'),(18,'Chant à la Vierge'),(19,'Chant de sortie'),(20,'Sortie des Mariés');
/*!40000 ALTER TABLE `song_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'paul-henri@andrieu.ch','[\"ROLE_ADMIN\"]','$2y$13$C6p.BOgYN/86Q2ElSwYVH.hr3jiQKLhXqdD1OLFfUx0LGzwnmG2AO','Andrieu','Paul-Henri','0643822294'),(3,'pr.tuffery@gmail.com','[\"ROLE_USER\", \"ROLE_ADMIN\"]','$2y$13$Ksa.dLtC.KkNV0BXzfLdneYaFBPhnuGmkWY.MAxPtKW08ja.FMtma','Tufféry','Pierre-Roger','0647742270'),(4,'pierrerogertuffery@yahoo.fr','[]','$2y$13$LWBSQUlsL56LV1aW7eFr5eEa3gskta/60lmrWhd9uJiLgzozWesWS','Test 1','Utilisateur','0647742270');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wedding`
--

DROP TABLE IF EXISTS `wedding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wedding` (
  `id` int NOT NULL AUTO_INCREMENT,
  `marie_id` int DEFAULT NULL,
  `mariee_id` int DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5BC25C96443D8FB7` (`marie_id`),
  KEY `IDX_5BC25C96C742AEC6` (`mariee_id`),
  CONSTRAINT `FK_5BC25C96443D8FB7` FOREIGN KEY (`marie_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_5BC25C96C742AEC6` FOREIGN KEY (`mariee_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wedding`
--

LOCK TABLES `wedding` WRITE;
/*!40000 ALTER TABLE `wedding` DISABLE KEYS */;
/*!40000 ALTER TABLE `wedding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wedding_song`
--

DROP TABLE IF EXISTS `wedding_song`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wedding_song` (
  `wedding_id` int NOT NULL,
  `song_id` int NOT NULL,
  PRIMARY KEY (`wedding_id`,`song_id`),
  KEY `IDX_7A313AA1FCBBB0ED` (`wedding_id`),
  KEY `IDX_7A313AA1A0BDB2F3` (`song_id`),
  CONSTRAINT `FK_7A313AA1A0BDB2F3` FOREIGN KEY (`song_id`) REFERENCES `song` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_7A313AA1FCBBB0ED` FOREIGN KEY (`wedding_id`) REFERENCES `wedding` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wedding_song`
--

LOCK TABLES `wedding_song` WRITE;
/*!40000 ALTER TABLE `wedding_song` DISABLE KEYS */;
/*!40000 ALTER TABLE `wedding_song` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-04 13:34:40