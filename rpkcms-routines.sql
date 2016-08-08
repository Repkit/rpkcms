-- MySQL dump 10.13  Distrib 5.5.46, for debian-linux-gnu (x86_64)
--
-- Host: 0.0.0.0    Database: rpkcms
-- ------------------------------------------------------
-- Server version	5.5.46-0ubuntu0.14.04.2

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
-- Table structure for table `page_categories`
--

DROP TABLE IF EXISTS `page_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`),
  KEY `parentId` (`parentId`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_categories`
--

LOCK TABLES `page_categories` WRITE;
/*!40000 ALTER TABLE `page_categories` DISABLE KEYS */;
INSERT INTO `page_categories` VALUES (1,0,'blog','blog','0000-00-00 00:00:00','2016-03-27 19:52:02',1,1),(2,1,'tutorials','tutorials','0000-00-00 00:00:00','2016-04-01 22:08:01',1,1),(3,2,'Programming','programming','0000-00-00 00:00:00','2016-04-04 20:45:45',1,1),(4,3,'Php','cocorico','0000-00-00 00:00:00','2016-04-07 08:11:29',1,1),(5,0,'Videos','video','0000-00-00 00:00:00','2016-04-04 21:17:45',1,1);
/*!40000 ALTER TABLE `page_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_categories_nested_model`
--

DROP TABLE IF EXISTS `page_categories_nested_model`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_categories_nested_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lft` int(11) DEFAULT NULL,
  `rgt` int(11) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`),
  KEY `lft` (`lft`,`rgt`),
  KEY `parentId` (`parentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_categories_nested_model`
--

LOCK TABLES `page_categories_nested_model` WRITE;
/*!40000 ALTER TABLE `page_categories_nested_model` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_categories_nested_model` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_meta`
--

DROP TABLE IF EXISTS `page_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `metaKey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `metaValue` text COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pageId` (`pageId`,`metaKey`),
  KEY `metaKey` (`metaKey`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_meta`
--

LOCK TABLES `page_meta` WRITE;
/*!40000 ALTER TABLE `page_meta` DISABLE KEYS */;
INSERT INTO `page_meta` VALUES (7,8,'preview','<h2>cocorico</h2>','2016-06-10 18:02:11','2016-06-10 18:02:11',1,1),(16,9,'thumbnail','<img src=\"/logo.png\" alt=\"by-mistake\">','2016-06-14 19:54:04','2016-06-14 19:54:04',1,1),(17,9,'short_info','<p>this is the page short info</p>','2016-06-14 19:54:04','2016-06-14 19:54:04',1,1);
/*!40000 ALTER TABLE `page_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_meta_tags`
--

DROP TABLE IF EXISTS `page_meta_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_meta_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_meta_tags`
--

LOCK TABLES `page_meta_tags` WRITE;
/*!40000 ALTER TABLE `page_meta_tags` DISABLE KEYS */;
INSERT INTO `page_meta_tags` VALUES (13,9,'name=\"keywords\" content=\"cms, mistakes, repkit\"','2016-06-14 19:54:04','2016-06-14 19:54:04',1,1),(14,9,'name=\"author\" content=\"by-mistake\"','2016-06-14 19:54:04','2016-06-14 19:54:04',1,1),(15,9,'charset=\"UTF-8\"','2016-06-14 19:54:04','2016-06-14 19:54:04',1,1);
/*!40000 ALTER TABLE `page_meta_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_schedules`
--

DROP TABLE IF EXISTS `page_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pageId` int(11) NOT NULL,
  `oldPageStausId` int(11) DEFAULT NULL,
  `newPageStatusId` int(11) NOT NULL,
  `scheduleDate` datetime NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`),
  KEY `scheduleDate` (`scheduleDate`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_schedules`
--

LOCK TABLES `page_schedules` WRITE;
/*!40000 ALTER TABLE `page_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_statuses`
--

DROP TABLE IF EXISTS `page_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_statuses`
--

LOCK TABLES `page_statuses` WRITE;
/*!40000 ALTER TABLE `page_statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_templates`
--

DROP TABLE IF EXISTS `page_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_path` (`name`,`path`(200)),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_templates`
--

LOCK TABLES `page_templates` WRITE;
/*!40000 ALTER TABLE `page_templates` DISABLE KEYS */;
INSERT INTO `page_templates` VALUES (1,'home','/','0000-00-00 00:00:00','2016-02-22 23:10:54',0,1),(2,'page','/theme/coco/','2016-02-23 21:10:16','2016-04-13 20:39:31',1,1),(4,'post','/theme/coco/','2016-02-23 21:57:27','2016-03-30 19:59:50',1,1),(5,'index','/theme/startbootstrap-clean-blog/','2016-02-29 22:36:51','2016-04-10 17:54:43',1,1),(7,'index','/theme/startbootstrap-business-casual/','0000-00-00 00:00:00','2016-03-16 21:02:35',1,1),(8,'about','/theme/startbootstrap-business-casual/','0000-00-00 00:00:00','2016-03-06 17:59:32',0,1),(9,'blog','/theme/startbootstrap-business-casual/','0000-00-00 00:00:00','2016-03-06 17:59:32',0,1),(10,'contact','/theme/startbootstrap-business-casual/','0000-00-00 00:00:00','2016-03-06 17:59:32',0,1),(11,'footer','/theme/startbootstrap-business-casual/chunks/','2016-03-16 21:48:45','2016-03-16 21:48:45',1,1);
/*!40000 ALTER TABLE `page_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `templateId` int(11) NOT NULL,
  `parentId` int(11) NOT NULL DEFAULT '0',
  `categoryId` int(11) NOT NULL DEFAULT '0',
  `roleId` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `priority` int(11) NOT NULL DEFAULT '0',
  `statusId` int(11) NOT NULL,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `templateId` (`templateId`),
  KEY `parentId` (`parentId`),
  KEY `categoryId` (`categoryId`),
  KEY `roleId` (`roleId`),
  KEY `priority` (`priority`),
  KEY `statusId` (`statusId`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,5,0,0,1,'Why to become a mistaker?','first rpkcms page','<font color=\"#00ffff\"><span style=\"background-color: rgb(255, 255, 0);\">hello</span> &nbsp;peeps&nbsp;</font>of <b><u><font face=\"Comic Sans MS\">mistakers</font></u></b>! :)<hr> <a href=\"http://www.by-mistake.com\" target=\"_blank\">by-mistake.com</a>','index','0000-00-00 00:00:00','2016-03-27 19:51:44',0,1,1,1),(2,7,0,1,1,'mistaker blog','a blog home page created through cms','<div class=\"row\">\r\n            <div class=\"box\">\r\n                <div class=\"col-lg-12 text-center\">\r\n                    <div id=\"carousel-example-generic\" class=\"carousel slide\">\r\n                        <!-- Indicators -->\r\n                        <ol class=\"carousel-indicators hidden-xs\">\r\n                            <li data-target=\"#carousel-example-generic\" data-slide-to=\"0\" class=\"active\"></li>\r\n                            <li data-target=\"#carousel-example-generic\" data-slide-to=\"1\"></li>\r\n                            <li data-target=\"#carousel-example-generic\" data-slide-to=\"2\"></li>\r\n                        </ol>\r\n\r\n                        <!-- Wrapper for slides -->\r\n                        <div class=\"carousel-inner\">\r\n                            <div class=\"item active\">\r\n                                <img class=\"img-responsive img-full\" src=\"img/slide-1.jpg\" alt=\"\">\r\n                            </div>\r\n                            <div class=\"item\">\r\n                                <img class=\"img-responsive img-full\" src=\"img/slide-2.jpg\" alt=\"\">\r\n                            </div>\r\n                            <div class=\"item\">\r\n                                <img class=\"img-responsive img-full\" src=\"img/slide-3.jpg\" alt=\"\">\r\n                            </div>\r\n                        </div>\r\n\r\n                        <!-- Controls -->\r\n                        <a class=\"left carousel-control\" href=\"#carousel-example-generic\" data-slide=\"prev\">\r\n                            <span class=\"icon-prev\"></span>\r\n                        </a>\r\n                        <a class=\"right carousel-control\" href=\"#carousel-example-generic\" data-slide=\"next\">\r\n                            <span class=\"icon-next\"></span>\r\n                        </a>\r\n                    </div>\r\n                    <h2 class=\"brand-before\">\r\n                        <small>Welcome 21</small></h2>\r\n                    <h1 class=\"brand-name\">RpkCms</h1>\r\n                    <hr class=\"tagline-divider\">\r\n                    <h2><font color=\"#777777\"><span style=\"font-size: 19.5px; line-height: 19.5px;\">by-mistake.com</span></font></h2>\r\n                </div>\r\n            </div>\r\n        </div>\r\n\r\n        <div class=\"row\">\r\n            <div class=\"box\">\r\n                <div class=\"col-lg-12\">\r\n                    <hr>\r\n                    <h2 class=\"intro-text text-center\">Build a website\r\n                        <strong>worth visiting</strong>\r\n                    </h2>\r\n                    <hr>\r\n                    <img class=\"img-responsive img-border img-left\" src=\"img/intro-pic.jpg\" alt=\"\">\r\n                    <hr class=\"visible-xs\">\r\n                    <p>The boxes used in this template are nested inbetween a normal Bootstrap row and the start of your column layout. The boxes will be full-width boxes, so if you want to make them smaller then you will need to customize.</p>\r\n                    <p>A huge thanks to <a href=\"http://join.deathtothestockphoto.com/\" target=\"_blank\">Death to the Stock Photo</a> for allowing us to use the beautiful photos that make this template really come to life. When using this template, make sure your photos are decent. Also make sure that the file size on your photos is kept to a minumum to keep load times to a minimum.</p>\r\n                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc placerat diam quis nisl vestibulum dignissim. In hac habitasse platea dictumst. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>\r\n                </div>\r\n            </div>\r\n        </div>\r\n\r\n        <div class=\"row\">\r\n            <div class=\"box\">\r\n                <div class=\"col-lg-12\">\r\n                    <hr>\r\n                    <h2 class=\"intro-text text-center\">Beautiful boxes\r\n                        <strong>to showcase your content</strong>\r\n                    </h2>\r\n                    <hr>\r\n                    <p>Use as many boxes as you like, and put anything you want in them! They are great for just about anything, the sky\'s the limit!</p>\r\n                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc placerat diam quis nisl vestibulum dignissim. In hac habitasse platea dictumst. Interdum et malesuada fames ac ante ipsum primis in faucibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>\r\n                </div>\r\n            </div>\r\n        </div>','home','0000-00-00 00:00:00','2016-06-07 18:54:32',0,1,1,1),(3,9,0,1,1,'rpkcms blog page','streaming tutorial about how to create a theme for the mighty cms rpkcms','my fist blog','blog','0000-00-00 00:00:00','2016-03-06 18:03:00',0,1,0,1),(4,8,0,1,1,'About','about me and you toghether','<p><br></p>','about','0000-00-00 00:00:00','2016-06-16 20:16:41',0,1,1,1),(5,2,0,1,1,'Contact page','contact page','<p><br></p>','contact','0000-00-00 00:00:00','2016-04-01 21:43:50',0,1,1,1),(6,2,0,4,2,'Proxy design pattern','A proxy, in its most general form, is a class functioning as an interface to something else.','<p style=\"margin-top: 0.5em; margin-bottom: 0.5em; line-height: 22.4px; color: rgb(37, 37, 37); font-family: sans-serif;\">In&nbsp;<a href=\"https://en.wikipedia.org/wiki/Computer_programming\" title=\"Computer programming\" style=\"color: rgb(11, 0, 128); background-image: none; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">computer programming</a>, the&nbsp;<b>proxy pattern</b>&nbsp;is a&nbsp;<a href=\"https://en.wikipedia.org/wiki/Software_design_pattern\" title=\"Software design pattern\" style=\"color: rgb(11, 0, 128); background-image: none; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">software design pattern</a>.</p><p style=\"margin-top: 0.5em; margin-bottom: 0.5em; line-height: 22.4px; color: rgb(37, 37, 37); font-family: sans-serif;\">A&nbsp;<i>proxy</i>, in its most general form, is a class functioning as an interface to something else. The proxy could interface to anything: a network connection, a large object in memory, a file, or some other resource that is expensive or impossible to duplicate. In short, a proxy is a wrapper or agent object that is being called by the client to access the real serving object behind the scenes. In the proxy extra functionality can be provided, for example caching when operations on the real object are resource intensive, or checking preconditions before operations on the real object are invoked. For the client, usage of a proxy object is similar to using the real object, because both implement the same interface.</p><p style=\"margin-top: 0.5em; margin-bottom: 0.5em; line-height: 22.4px; color: rgb(37, 37, 37); font-family: sans-serif;\"><b>Remote Proxy</b>&nbsp;â€“ In&nbsp;<a href=\"https://en.wikipedia.org/wiki/Distributed_object_communication\" title=\"Distributed object communication\" style=\"color: rgb(11, 0, 128); background-image: none; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">distributed object communication</a>, a local object represents a remote object (one that belongs to a different address space). The local object is a proxy for the remote object, and method invocation on the local object results in&nbsp;<a href=\"https://en.wikipedia.org/wiki/Remote_method_invocation\" title=\"Remote method invocation\" class=\"mw-redirect\" style=\"color: rgb(11, 0, 128); background-image: none; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">remote method invocation</a>&nbsp;on the remote object. Think of an&nbsp;<a href=\"https://en.wikipedia.org/wiki/Automated_teller_machine\" title=\"Automated teller machine\" class=\"mw-redirect\" style=\"color: rgb(11, 0, 128); background-image: none; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">ATM</a>&nbsp;implementation, it will hold proxy objects for bank information that exists in the remote server.</p><p style=\"margin-top: 0.5em; margin-bottom: 0.5em; line-height: 22.4px; color: rgb(37, 37, 37); font-family: sans-serif;\"><b>Virtual Proxy</b>&nbsp;â€“ In place of a complex or heavy object, use a skeleton representation. When an underlying image is huge in size, just represent it using a virtual proxy object and on demand load the real object. You know that the real object is expensive in terms of instantiation and so without the real need we are not going to use the real object. Until the need arises we will use the virtual proxy.</p><p style=\"margin-top: 0.5em; margin-bottom: 0.5em; line-height: 22.4px; color: rgb(37, 37, 37); font-family: sans-serif;\"><b>Protection Proxy</b>&nbsp;â€“ Are you working on an&nbsp;<a href=\"https://en.wikipedia.org/wiki/Mobile_Network_Code\" title=\"Mobile Network Code\" class=\"mw-redirect\" style=\"color: rgb(11, 0, 128); background-image: none; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;\">MNC</a>? If so, we might be well aware of the proxy server that provides us internet by restricting access to some sort of websites like public e-mail, social networking, data storage etc. The management feels that, it is better to block some content and provide only work related web pages. Proxy server does that job. This is a type of proxy design pattern.</p>','proxy-pattern','0000-00-00 00:00:00','2016-04-13 20:38:55',0,1,1,1),(7,4,0,0,2,'test','<div class=\"sticky-note-inner\">\r\n<div class=\"embed-responsive embed-responsive-16by9\">\r\n  <iframe class=\"embed-responsive-item\" src=\"https://www.youtube.com/embed/M482RhQ8i1Q\"></iframe>\r\n</div>\r\n        <p>To help yourself, you must be yourself. Be the best that you can be. When you make a <strong>mistake</strong>, learn from it, pick yourself up and move on.</p>\r\n        <span class=\"author\">Dave Pelzer</span>\r\n      </div>','<p>test page</p>','test','0000-00-00 00:00:00','2016-06-07 20:52:55',1,1,1,1),(8,4,0,0,2,'title','description','<p><br></p>','slug','0000-00-00 00:00:00','2016-06-10 17:38:34',0,1,1,1),(9,2,0,0,2,'page meta tags','implementation of page with extended and metadata','<p>this is a complete cms page</p>','meta-tags','0000-00-00 00:00:00','2016-06-13 21:57:54',0,1,1,1),(10,4,0,0,2,'Test meta validation','test validation on meta and meta-tags','<p>what the fuck content 21</p>','test-meta-validation','0000-00-00 00:00:00','2016-06-16 20:31:27',0,1,1,1);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_meta`
--

DROP TABLE IF EXISTS `user_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `metaKey` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `metaValue` text COLLATE utf8_unicode_ci NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`,`metaKey`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_meta`
--

LOCK TABLES `user_meta` WRITE;
/*!40000 ALTER TABLE `user_meta` DISABLE KEYS */;
INSERT INTO `user_meta` VALUES (1,1,'externalId','116544776315755836109','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(2,1,'first_name','repkit','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(3,1,'last_name','.','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(4,1,'image','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(5,1,'verified_email','1','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(6,1,'name','repkit','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(7,1,'given_name','repkit','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(8,1,'family_name','.','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(9,1,'link','https://plus.google.com/116544776315755836109','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(10,1,'picture','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(11,1,'locale','ro','2016-03-12 23:47:25','2016-03-12 23:47:25',0,1),(12,2,'externalId','116544776315755836109','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(13,2,'first_name','repkit','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(14,2,'last_name','.','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(15,2,'image','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(16,2,'verified_email','1','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(17,2,'name','repkit','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(18,2,'given_name','repkit','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(19,2,'family_name','.','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(20,2,'link','https://plus.google.com/116544776315755836109','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(21,2,'picture','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(22,2,'locale','ro','2016-03-15 22:33:44','2016-03-15 22:33:44',0,1),(23,3,'externalId','116544776315755836109','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(24,3,'first_name','repkit','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(25,3,'last_name','.','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(26,3,'image','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(27,3,'verified_email','1','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(28,3,'name','repkit','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(29,3,'given_name','repkit','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(30,3,'family_name','.','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(31,3,'link','https://plus.google.com/116544776315755836109','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(32,3,'picture','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(33,3,'locale','ro','2016-03-15 22:33:47','2016-03-15 22:33:47',0,1),(34,1,'externalId','116544776315755836109','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(35,1,'first_name','repkit','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(36,1,'last_name','.','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(37,1,'image','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(38,1,'verified_email','1','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(39,1,'name','repkit','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(40,1,'given_name','repkit','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(41,1,'family_name','.','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(42,1,'link','https://plus.google.com/116544776315755836109','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(43,1,'picture','https://lh6.googleusercontent.com/-PWuO1BdGgsE/AAAAAAAAAAI/AAAAAAAACDI/Ny9_eZLpRgo/photo.jpg','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1),(44,1,'locale','ro','2016-03-15 22:36:16','2016-03-15 22:36:16',0,1);
/*!40000 ALTER TABLE `user_meta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,'admin',0,'2016-02-14 00:00:00','2016-02-13 23:10:00',1,1),(2,'blogger',0,'2016-03-13 15:18:35','2016-03-13 15:49:19',1,1);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(4095) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `registrationDate` datetime NOT NULL,
  `activationKey` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `external` int(11) NOT NULL DEFAULT '0',
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `username` (`username`,`password`(255)),
  KEY `external` (`external`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`),
  KEY `email` (`email`,`password`(255))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'repkit','ya29.pgIw4rBUscVJFoDlVhClMIrhKpuUXHhBL0TiMqzdmdRqD6sV7nqMswxssX7fkbhQq_s','repkit@gmail.com','repkit','2016-03-15 22:36:16',NULL,'2016-03-15 22:36:16','2016-03-15 22:40:56',1,0,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `roleId` int(11) NOT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `authorId` int(11) NOT NULL,
  `state` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `roleId` (`roleId`),
  KEY `authorId` (`authorId`),
  KEY `state` (`state`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_roles`
--

LOCK TABLES `users_roles` WRITE;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` VALUES (1,1,1,'2016-02-14 00:00:00','2016-03-15 22:15:53',1,1);
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'rpkcms'
--
/*!50003 DROP FUNCTION IF EXISTS `categoryChildrenById` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`repkit`@`%` FUNCTION `categoryChildrenById`(`categoryid` INT) RETURNS text CHARSET utf8
    DETERMINISTIC
BEGIN

    DECLARE rv,q,queue,queue_children VARCHAR(1024);
    DECLARE queue_length,front_id,pos INT;

    SET rv = '';
    SET queue = categoryid;
    SET queue_length = 1;

    WHILE queue_length > 0 DO
        SET front_id = FORMAT(queue,0);
        IF queue_length = 1 THEN
            SET queue = '';
        ELSE
            SET pos = LOCATE(',',queue) + 1;
            SET q = SUBSTR(queue,pos);
            SET queue = q;
        END IF;
        SET queue_length = queue_length - 1;

        SELECT IFNULL(qc,'') INTO queue_children
        FROM (SELECT GROUP_CONCAT(id) qc
        FROM page_categories WHERE parentId = front_id) A;

        IF LENGTH(queue_children) = 0 THEN
            IF LENGTH(queue) = 0 THEN
                SET queue_length = 0;
            END IF;
        ELSE
            IF LENGTH(rv) = 0 THEN
                SET rv = queue_children;
            ELSE
                SET rv = CONCAT(rv,',',queue_children);
            END IF;
            IF LENGTH(queue) = 0 THEN
                SET queue = queue_children;
            ELSE
                SET queue = CONCAT(queue,',',queue_children);
            END IF;
            SET queue_length = LENGTH(queue) - LENGTH(REPLACE(queue,',','')) + 1;
        END IF;
    END WHILE;

    RETURN rv;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `pagePathByCategoryId` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`repkit`@`%` FUNCTION `pagePathByCategoryId`(`categoryid` INT) RETURNS text CHARSET utf8
    DETERMINISTIC
BEGIN
  
	DECLARE rv TEXT;
    DECLARE cm CHAR(1);
    DECLARE ch INT;
    DECLARE nm VARCHAR(255);

    SET rv = '';
    SET cm = '';
    SET ch = categoryid;
	SET nm = '';
    WHILE ch > 0 DO
        SELECT parentId, slug INTO ch, nm FROM
        (SELECT parentId, slug FROM page_categories WHERE id = ch) A;
        IF ch >= 0 THEN
            SET rv = CONCAT(nm,cm,rv);
            SET cm = '/';
        END IF;
    END WHILE;
    RETURN CONCAT(cm,rv);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `fetchAllPages` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`repkit`@`%` PROCEDURE `fetchAllPages`(IN filters TEXT, IN sorters TEXT, IN startfrom INT(4), IN endto INT(4))
BEGIN
SET @sql = NULL;
SELECT
  GROUP_CONCAT(DISTINCT
    CONCAT(
      'MAX(IF(page_meta.metaKey = ''',
      page_meta.metaKey,
      ''', page_meta.metaValue, NULL)) AS ',
      page_meta.metaKey
    )
  ) INTO @sql
FROM page_meta;

SET @sql = CONCAT(' SELECT pages.*, pagePathByCategoryId(pages.categoryid) AS path, ', 
                    @sql, 
                    ' FROM pages
                    LEFT JOIN page_meta
                    ON pages.id = page_meta.pageId ',
                    filters,
                    ' GROUP BY pages.id ORDER BY ', sorters,
                    ' LIMIT ',startfrom,' , ', endto);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
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

-- Dump completed on 2016-06-16 20:56:30
