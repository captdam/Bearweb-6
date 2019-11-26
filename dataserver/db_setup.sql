-- MySQL dump 10.13  Distrib 5.7.28, for Linux (x86_64)
--
-- Host: localhost    Database: Bearweb
-- ------------------------------------------------------
-- Server version	5.7.28-0ubuntu0.16.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `Bearweb`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `Bearweb` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `Bearweb`;

--
-- Table structure for table `BW_Config`
--

DROP TABLE IF EXISTS `BW_Config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_Config` (
  `Site` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `Key` varchar(255) NOT NULL,
  `Value` varchar(4096) NOT NULL DEFAULT '',
  PRIMARY KEY (`Site`,`Key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `BW_Object`
--

DROP TABLE IF EXISTS `BW_Object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_Object` (
  `Site` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `URL` varchar(255) NOT NULL,
  `MIME` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL DEFAULT 'text/plian',
  `Title` varchar(255) NOT NULL DEFAULT '',
  `Keywords` varchar(255) NOT NULL DEFAULT '',
  `Description` varchar(4096) NOT NULL DEFAULT '',
  `Binary` longblob,
  PRIMARY KEY (`Site`,`URL`),
  CONSTRAINT `BW_Object__URLLink` FOREIGN KEY (`Site`, `URL`) REFERENCES `BW_Sitemap` (`Site`, `URL`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `BW_Session`
--

DROP TABLE IF EXISTS `BW_Session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_Session` (
  `SessionID` char(64) COLLATE latin1_general_cs NOT NULL,
  `CreateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUsed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Expire` tinyint(4) NOT NULL DEFAULT '0',
  `Username` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `JSKey` char(64) COLLATE latin1_general_cs NOT NULL,
  `Salt` char(64) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`SessionID`,`CreateTime`),
  KEY `BW_Session__UsernameLink` (`Username`),
  CONSTRAINT `BW_Session__UsernameLink` FOREIGN KEY (`Username`) REFERENCES `BW_User` (`Username`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `BW_Sitemap`
--

DROP TABLE IF EXISTS `BW_Sitemap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_Sitemap` (
  `Site` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `URL` varchar(255) NOT NULL,
  `Category` varchar(45) NOT NULL,
  `TemplateMain` varchar(45) NOT NULL,
  `TemplateSub` varchar(45) NOT NULL,
  `Author` varchar(16) DEFAULT NULL,
  `CreateTime` datetime DEFAULT NULL,
  `LastModify` datetime DEFAULT NULL,
  `Copyright` varchar(255) DEFAULT NULL,
  `Status` char(1) NOT NULL DEFAULT 'O',
  `Info` json NOT NULL,
  PRIMARY KEY (`Site`,`URL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Bearweb`.`BW_Sitemap_BEFORE_INSERT` BEFORE INSERT ON `BW_Sitemap` FOR EACH ROW
BEGIN
	IF (NEW.Info IS NULL) THEN
		SET NEW.Info = '{}';
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `BW_Transaction`
--

DROP TABLE IF EXISTS `BW_Transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_Transaction` (
  `RecordID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RequestTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `TransactionID` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `SessionID` char(64) COLLATE latin1_general_cs DEFAULT NULL,
  `Username` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `IP` varchar(15) COLLATE latin1_general_cs DEFAULT NULL,
  `URL` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ExecutionTime` decimal(10,2) unsigned DEFAULT NULL,
  `Status` char(3) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`RecordID`),
  KEY `BW_Transaction__UsernameLink` (`Username`),
  KEY `BW_Transaction__SIDLink` (`SessionID`),
  CONSTRAINT `BW_Transaction__SIDLink` FOREIGN KEY (`SessionID`) REFERENCES `BW_Session` (`SessionID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `BW_Transaction__UsernameLink` FOREIGN KEY (`Username`) REFERENCES `BW_User` (`Username`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1860 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `BW_User`
--

DROP TABLE IF EXISTS `BW_User`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_User` (
  `Username` char(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `Nickname` char(16) NOT NULL,
  `Group` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `Password` char(32) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `LastActiveTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `RegisterIP` char(15) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `RegisterTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Email` varchar(128) DEFAULT NULL,
  `Data` json NOT NULL,
  `Photo` blob,
  PRIMARY KEY (`Username`),
  UNIQUE KEY `Username_UNIQUE` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `Bearweb`.`BW_User_BEFORE_INSERT` BEFORE INSERT ON `BW_User` FOR EACH ROW
BEGIN
	IF (NEW.`Data` IS NULL) THEN
		SET NEW.`Data` = '{}';
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `BW_Webpage`
--

DROP TABLE IF EXISTS `BW_Webpage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BW_Webpage` (
  `Site` varchar(45) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `URL` varchar(255) NOT NULL,
  `Language` varchar(5) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `Title` varchar(255) NOT NULL DEFAULT 'Bearweb webpage',
  `Keywords` varchar(255) NOT NULL DEFAULT '',
  `Description` varchar(4096) NOT NULL DEFAULT '',
  `Content` longtext,
  `Source` longtext NOT NULL,
  `Style` varchar(45) NOT NULL DEFAULT 'plaintext',
  PRIMARY KEY (`Site`,`URL`,`Language`),
  CONSTRAINT `BW_Webpage__URLLink` FOREIGN KEY (`Site`, `URL`) REFERENCES `BW_Sitemap` (`Site`, `URL`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'Bearweb'
--
/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;
/*!50106 DROP EVENT IF EXISTS `Session_autoExpire` */;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8 */ ;;
/*!50003 SET character_set_results = utf8 */ ;;
/*!50003 SET collation_connection  = utf8_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = '+00:00' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `Session_autoExpire` ON SCHEDULE EVERY 1 HOUR STARTS '2019-11-11 16:48:03' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
	UPDATE BW_Session SET `Expire` = 1 WHERE `Expire` = 0 AND LastUsed < SUBTIME(CURRENT_TIMESTAMP,'01:00:00');
END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
DELIMITER ;
/*!50106 SET TIME_ZONE= @save_time_zone */ ;

--
-- Dumping routines for database 'Bearweb'
--
/*!50003 DROP PROCEDURE IF EXISTS `Config_get` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Config_get`(
	IN in_sitename	VARCHAR(45)
)
BEGIN
	SELECT * FROM BW_Config C WHERE C.Site = '' OR C.Site = in_sitename;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Object_get` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Object_get`(
	IN in_site		VARCHAR(45),
    IN in_url		VARCHAR(255)
)
BEGIN
	SELECT * FROM BW_Object WHERE (Site = in_site OR Site = '@ALL') AND URL = in_url;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Session_bind` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Session_bind`(
	IN in_sessionid	CHAR(64),
	IN in_username VARCHAR(16)
)
BEGIN
	UPDATE BW_Session SET Username = in_username WHERE SessionID = in_sessionid AND `Expire` = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Session_expire` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Session_expire`(
	IN in_sessionid	CHAR(64)
)
BEGIN
	UPDATE BW_Session SET `Expire` = 1 WHERE SessionID = in_sessionid;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Session_get` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Session_get`(
	IN in_sessionid	CHAR(64)
)
BEGIN
	SELECT * FROM BW_Session WHERE SessionID = in_sessionid AND `Expire` = 0 LIMIT 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Session_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Session_new`()
BEGIN
	/*
	BW_Session table restriction: PK are SessionID and CreateTime. The table contains all active sessions and expired old sesions.
	Application logic restriction: Only one active session for each SessionID.
	*/
	
	DECLARE sid CHAR(64) DEFAULT TO_BASE64(RANDOM_BYTES(48));
	DECLARE jsk CHAR(64) DEFAULT TO_BASE64(RANDOM_BYTES(48));
	DECLARE slt CHAR(64) DEFAULT TO_BASE64(RANDOM_BYTES(48));
	
	DECLARE ok TINYINT;
	
	/* Avoid PK conflic: Which should not happen, just in case a record is insert just after COUNT and before INSERT */
	DECLARE CONTINUE HANDLER FOR 1062 BEGIN
		SET ok = 0;
		SET sid = TO_BASE64(RANDOM_BYTES(48));
	END;
	
	REPEAT
		SET ok = 1;
		
		/* Application logic restriction: Multiple active session with the same SID */
		IF ( SELECT COUNT(NULL) FROM BW_Session WHERE SessionID = sid AND `Expire` = 0) THEN
			SET ok = 0;
			SET sid = TO_BASE64(RANDOM_BYTES(48));
			
		ELSE
			INSERT INTO BW_Session (SessionID,JSKey,Salt) VALUES (sid,jsk,slt);
			
		END IF;
		
	UNTIL ok = 1 END REPEAT;
	
	SELECT * FROM BW_Session WHERE SessionID = sid AND `Expire` = 0 LIMIT 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Session_renew` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Session_renew`(
	IN in_sessionid	CHAR(64)
)
BEGIN
	UPDATE BW_Session SET LastUsed = CURRENT_TIMESTAMP WHERE SessionID = in_sessionid AND `Expire` = 0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sitemap_get` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Sitemap_get`(
	IN in_site		VARCHAR(45),
	IN in_url		VARCHAR(255),
	IN in_category	VARCHAR(255),
	IN in_status	VARCHAR(255)
)
BEGIN
	SELECT * FROM BW_Sitemap WHERE
		(in_site IS NULL OR Site = in_site OR Site = '@ALL') AND
        (in_url IS NULL OR URL LIKE in_url) AND
        (in_category IS NULL OR FIND_IN_SET( CONCAT(',',Category,',') , CONCAT(',',in_category,',') ) ) AND
        (in_status IS NULL OR FIND_IN_SET(Site,in_status) )
	;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sitemap_getRecentWebpageIndex` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Sitemap_getRecentWebpageIndex`(
	IN in_site		VARCHAR(45),
	IN in_category	VARCHAR(255),
	IN in_size		INT,
    In in_offset	INT
)
BEGIN
	SELECT
		Web.URL,
        Web.`Language`,
		Web.Title,
		Map.Category,
		Web.Keywords,
		Web.Description,
		Map.Author,
		Map.LastModify,
		Map.`Status`,
		Map.`Info`->>'$.poster' AS Poster
	/* Step 2 - Get page index from all language those are recent */
	FROM BW_Webpage Web RIGHT JOIN BW_Sitemap Map
    ON (Web.Site = Map.Site AND Web.URL = Map.URL)
    WHERE (Web.Site = in_site OR Web.Site = '@ALL') AND Web.URL IN (
		SELECT URL FROM (
			/* Step 1 - Get recent pages from BW_Sitemap */
			SELECT DISTINCT S.URL, S.LastModify
            FROM BW_Sitemap S RIGHT JOIN BW_Webpage W
            ON (S.Site = W.Site AND S.URL = W.URL)
            WHERE
				(S.Site = in_site OR S.Site = '@ALL') AND
				S.`Status` NOT IN ('A','P') AND
				FIND_IN_SET(S.Category,in_category)
			ORDER BY S.LastModify DESC
            LIMIT in_size OFFSET in_offset
		) AS Site
	);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Transaction_bindClientInfo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Transaction_bindClientInfo`(
	IN in_recordid		INT,
	IN in_sessionid		CHAR(64),
	IN in_username		VARCHAR(16),
	IN in_ip			VARCHAR(15)
)
BEGIN
	UPDATE BW_Transaction SET
		SessionID = in_sessionid,
        Username = in_username,
        IP = in_ip
	WHERE RecordID = in_recordid;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Transaction_bindPageInfo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Transaction_bindPageInfo`(
	IN in_recordid		INT,
	IN in_url			VARCHAR(255)
)
BEGIN
	UPDATE BW_Transaction SET URL = in_url WHERE RecordID = in_recordid;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Transaction_bindStatisticInfo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Transaction_bindStatisticInfo`(
	IN in_recordid		INT,
	IN in_executiontime	DECIMAL(10,2),
    IN in_status		CHAR(3)
)
BEGIN
	UPDATE BW_Transaction SET ExecutionTime = in_executiontime, `Status` = in_status WHERE RecordID = in_recordid;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Transaction_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Transaction_new`(
	IN in_transactionid		VARCHAR(255)
)
BEGIN
	INSERT INTO BW_Transaction (TransactionID) VALUES (in_transactionid);
	SELECT * FROM BW_Transaction WHERE RecordID = LAST_INSERT_ID();
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `User_get` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `User_get`(
	IN in_username	CHAR(16)
)
BEGIN
	SELECT * FROM BW_User WHERE Username = in_username;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `User_new` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `User_new`(
	IN in_username	CHAR(16),
	IN in_nickname	CHAR(16),
	IN in_password	CHAR(32),
	IN in_ip		CHAR(15)
)
BEGIN
	INSERT INTO BW_User (Username,Nickname,`Password`,RegisterIP) VALUES (in_username,in_nickname,in_password,in_ip);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Webpage_get` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Webpage_get`(
	IN in_site		VARCHAR(45),
    IN in_url		VARCHAR(255),
    IN in_language	VARCHAR(5)
)
BEGIN
	SELECT * FROM BW_Webpage WHERE (Site = in_site OR Site = '@ALL') AND URL = in_url AND `Language` = in_language;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Webpage_getLanguageIndex` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `Webpage_getLanguageIndex`(
	IN in_site	VARCHAR(45),
    IN in_url	VARCHAR(255)
)
BEGIN
	SELECT `Language` FROM BW_Webpage WHERE (Site = in_site OR Site = '@ALL') AND URL = in_url;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-11-26 10:13:00
