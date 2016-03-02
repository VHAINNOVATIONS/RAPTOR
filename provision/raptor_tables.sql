-- MySQL dump 10.13  Distrib 5.6.28, for Linux (x86_64)
--
-- Host: localhost    Database: raptor500
-- ------------------------------------------------------
-- Server version	5.6.28

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
-- Table structure for table `ajax_example_node_form_alter`
--

DROP TABLE IF EXISTS `ajax_example_node_form_alter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ajax_example_node_form_alter` (
  `nid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The node.nid to store settings.',
  `example_1` int(11) NOT NULL DEFAULT '0' COMMENT 'Node Form Example 1 checkbox',
  `example_2` varchar(256) DEFAULT '' COMMENT 'Node Form Example 2 textfield',
  PRIMARY KEY (`nid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores example settings for nodes.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ajax_example_node_form_alter`
--

LOCK TABLES `ajax_example_node_form_alter` WRITE;
/*!40000 ALTER TABLE `ajax_example_node_form_alter` DISABLE KEYS */;
/*!40000 ALTER TABLE `ajax_example_node_form_alter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_token`
--

DROP TABLE IF EXISTS `cache_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_token` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table for token information.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_token`
--

LOCK TABLES `cache_token` WRITE;
/*!40000 ALTER TABLE `cache_token` DISABLE KEYS */;
INSERT INTO `cache_token` VALUES ('field:info','a:4:{s:12:\"comment_body\";a:5:{s:11:\"token types\";a:1:{i:0;s:7:\"comment\";}s:6:\"labels\";a:2:{i:0;s:7:\"Comment\";i:1;s:7:\"Comment\";}s:7:\"bundles\";a:1:{s:7:\"comment\";a:2:{s:17:\"comment_node_page\";s:18:\"Basic page comment\";s:20:\"comment_node_article\";s:15:\"Article comment\";}}s:5:\"label\";s:7:\"Comment\";s:11:\"description\";s:16:\"Long text field.\";}s:4:\"body\";a:5:{s:11:\"token types\";a:1:{i:0;s:4:\"node\";}s:6:\"labels\";a:2:{i:0;s:4:\"Body\";i:1;s:4:\"Body\";}s:7:\"bundles\";a:1:{s:4:\"node\";a:2:{s:4:\"page\";s:10:\"Basic page\";s:7:\"article\";s:7:\"Article\";}}s:5:\"label\";s:4:\"Body\";s:11:\"description\";s:28:\"Long text and summary field.\";}s:10:\"field_tags\";a:5:{s:11:\"token types\";a:1:{i:0;s:4:\"node\";}s:6:\"labels\";a:1:{i:0;s:4:\"Tags\";}s:7:\"bundles\";a:1:{s:4:\"node\";a:1:{s:7:\"article\";s:7:\"Article\";}}s:5:\"label\";s:4:\"Tags\";s:11:\"description\";s:21:\"Term reference field.\";}s:11:\"field_image\";a:5:{s:11:\"token types\";a:1:{i:0;s:4:\"node\";}s:6:\"labels\";a:1:{i:0;s:5:\"Image\";}s:7:\"bundles\";a:1:{s:4:\"node\";a:1:{s:7:\"article\";s:7:\"Article\";}}s:5:\"label\";s:5:\"Image\";s:11:\"description\";s:12:\"Image field.\";}}',0,1435417102,1);
/*!40000 ALTER TABLE `cache_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_views`
--

DROP TABLE IF EXISTS `cache_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_views` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '0' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Generic cache table for caching things not separated out...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_views`
--

LOCK TABLES `cache_views` WRITE;
/*!40000 ALTER TABLE `cache_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_views_data`
--

DROP TABLE IF EXISTS `cache_views_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_views_data` (
  `cid` varchar(255) NOT NULL DEFAULT '' COMMENT 'Primary Key: Unique cache ID.',
  `data` longblob COMMENT 'A collection of data to cache.',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry should expire, or 0 for never.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'A Unix timestamp indicating when the cache entry was created.',
  `serialized` smallint(6) NOT NULL DEFAULT '1' COMMENT 'A flag to indicate whether content is serialized (1) or not (0).',
  PRIMARY KEY (`cid`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Cache table for views to store pre-rendered queries,...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_views_data`
--

LOCK TABLES `cache_views_data` WRITE;
/*!40000 ALTER TABLE `cache_views_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_views_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ctools_css_cache`
--

DROP TABLE IF EXISTS `ctools_css_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ctools_css_cache` (
  `cid` varchar(128) NOT NULL COMMENT 'The CSS ID this cache object belongs to.',
  `filename` varchar(255) DEFAULT NULL COMMENT 'The filename this CSS is stored in.',
  `css` longtext COMMENT 'CSS being stored.',
  `filter` tinyint(4) DEFAULT NULL COMMENT 'Whether or not this CSS needs to be filtered.',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A special cache used to store CSS that must be non-volatile.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ctools_css_cache`
--

LOCK TABLES `ctools_css_cache` WRITE;
/*!40000 ALTER TABLE `ctools_css_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `ctools_css_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ctools_object_cache`
--

DROP TABLE IF EXISTS `ctools_object_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ctools_object_cache` (
  `sid` varchar(64) NOT NULL COMMENT 'The session ID this cache object belongs to.',
  `name` varchar(128) NOT NULL COMMENT 'The name of the object this cache is attached to.',
  `obj` varchar(32) NOT NULL COMMENT 'The type of the object this cache is attached to; this essentially represents the owner so that several sub-systems can use this cache.',
  `updated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The time this cache was created or updated.',
  `data` longblob COMMENT 'Serialized data being stored.',
  PRIMARY KEY (`sid`,`obj`,`name`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A special cache used to store objects that are being...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ctools_object_cache`
--

LOCK TABLES `ctools_object_cache` WRITE;
/*!40000 ALTER TABLE `ctools_object_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `ctools_object_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_about`
--

DROP TABLE IF EXISTS `raptor_about`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_about` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Information category',
  `info_tx` varchar(100) DEFAULT NULL COMMENT 'The information text to show',
  `major_ct` int(10) unsigned DEFAULT '1' COMMENT 'Major info number, sometimes useful for versioning',
  `minor_ct` int(10) unsigned DEFAULT '1' COMMENT 'Minor info number, sometimes useful for versioning',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Information about this RAPTOR installation';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_about`
--

LOCK TABLES `raptor_about` WRITE;
/*!40000 ALTER TABLE `raptor_about` DISABLE KEYS */;
INSERT INTO `raptor_about` VALUES ('DBSchemaVersion','Initial installation',2015,8,'2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_about` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_atrisk_allergy_contrast`
--

DROP TABLE IF EXISTS `raptor_atrisk_allergy_contrast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_atrisk_allergy_contrast` (
  `keyword` varchar(50) NOT NULL COMMENT 'Keyword for text matching',
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Keywords to match with known patient allergies considered...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_atrisk_allergy_contrast`
--

LOCK TABLES `raptor_atrisk_allergy_contrast` WRITE;
/*!40000 ALTER TABLE `raptor_atrisk_allergy_contrast` DISABLE KEYS */;
INSERT INTO `raptor_atrisk_allergy_contrast` VALUES ('Contrast'),('Gadolinium'),('interavascular'),('Iodinated'),('Iodine');
/*!40000 ALTER TABLE `raptor_atrisk_allergy_contrast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_atrisk_bloodthinner`
--

DROP TABLE IF EXISTS `raptor_atrisk_bloodthinner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_atrisk_bloodthinner` (
  `keyword` varchar(50) NOT NULL COMMENT 'Keyword for text matching',
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Keywords to match with known blood thinning medications';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_atrisk_bloodthinner`
--

LOCK TABLES `raptor_atrisk_bloodthinner` WRITE;
/*!40000 ALTER TABLE `raptor_atrisk_bloodthinner` DISABLE KEYS */;
INSERT INTO `raptor_atrisk_bloodthinner` VALUES ('Lovenox'),('Metaglys'),('Metformin'),('Plavix'),('Proleukin'),('Warfarin');
/*!40000 ALTER TABLE `raptor_atrisk_bloodthinner` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_atrisk_meds`
--

DROP TABLE IF EXISTS `raptor_atrisk_meds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_atrisk_meds` (
  `keyword` varchar(50) NOT NULL COMMENT 'Keyword for text matching',
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Keywords to match with medications considered to be at...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_atrisk_meds`
--

LOCK TABLES `raptor_atrisk_meds` WRITE;
/*!40000 ALTER TABLE `raptor_atrisk_meds` DISABLE KEYS */;
INSERT INTO `raptor_atrisk_meds` VALUES ('Aldesleukin'),('Aspirin'),('Avandamet'),('Clopidogrel'),('Coumadin'),('Dalteparin'),('Enoxaparin'),('Fragmin'),('Glucophage'),('Glucovance'),('Heparin'),('Lovenox'),('Metaglys'),('Metformin'),('Plavix'),('Proleukin'),('Sample'),('Warfarin');
/*!40000 ALTER TABLE `raptor_atrisk_meds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_atrisk_rare_contrast`
--

DROP TABLE IF EXISTS `raptor_atrisk_rare_contrast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_atrisk_rare_contrast` (
  `keyword` varchar(50) NOT NULL COMMENT 'Keyword for text matching',
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Keywords to match with known controlled or rare contrast';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_atrisk_rare_contrast`
--

LOCK TABLES `raptor_atrisk_rare_contrast` WRITE;
/*!40000 ALTER TABLE `raptor_atrisk_rare_contrast` DISABLE KEYS */;
INSERT INTO `raptor_atrisk_rare_contrast` VALUES ('Ablavar');
/*!40000 ALTER TABLE `raptor_atrisk_rare_contrast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_atrisk_rare_radioisotope`
--

DROP TABLE IF EXISTS `raptor_atrisk_rare_radioisotope`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_atrisk_rare_radioisotope` (
  `keyword` varchar(50) NOT NULL COMMENT 'Keyword for text matching',
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Keywords to match with known controlled or rare radioisotope';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_atrisk_rare_radioisotope`
--

LOCK TABLES `raptor_atrisk_rare_radioisotope` WRITE;
/*!40000 ALTER TABLE `raptor_atrisk_rare_radioisotope` DISABLE KEYS */;
INSERT INTO `raptor_atrisk_rare_radioisotope` VALUES ('MAG3');
/*!40000 ALTER TABLE `raptor_atrisk_rare_radioisotope` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_boilerplate_exam`
--

DROP TABLE IF EXISTS `raptor_boilerplate_exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_boilerplate_exam` (
  `category_tx` varchar(50) NOT NULL DEFAULT '' COMMENT 'Category of the text',
  `title_tx` varchar(40) NOT NULL DEFAULT '' COMMENT 'Short title for the text',
  `content_tx` varchar(250) NOT NULL DEFAULT '' COMMENT 'Description of the location',
  PRIMARY KEY (`category_tx`,`title_tx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Boilerplate text options for exam step';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_boilerplate_exam`
--

LOCK TABLES `raptor_boilerplate_exam` WRITE;
/*!40000 ALTER TABLE `raptor_boilerplate_exam` DISABLE KEYS */;
INSERT INTO `raptor_boilerplate_exam` VALUES ('General','Difficult Patient','Image quality is reduced by [<artifact>] because patient [<reason>].'),('General','Extravasation','Extravasation of IV contrast occurred. [<>] cc of [<type>] at [<anatomy>].');
/*!40000 ALTER TABLE `raptor_boilerplate_exam` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_boilerplate_protocol`
--

DROP TABLE IF EXISTS `raptor_boilerplate_protocol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_boilerplate_protocol` (
  `category_tx` varchar(50) NOT NULL DEFAULT '' COMMENT 'Category of the text',
  `title_tx` varchar(40) NOT NULL DEFAULT '' COMMENT 'Short title for the text',
  `content_tx` varchar(250) NOT NULL DEFAULT '' COMMENT 'Description of the location',
  PRIMARY KEY (`category_tx`,`title_tx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Boilerplate text options for protocol step';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_boilerplate_protocol`
--

LOCK TABLES `raptor_boilerplate_protocol` WRITE;
/*!40000 ALTER TABLE `raptor_boilerplate_protocol` DISABLE KEYS */;
INSERT INTO `raptor_boilerplate_protocol` VALUES ('General','Labs Needed','Scheduler, please assure patient gets renal function laboratory blood draw prior to exam.'),('General','Mass Markers','Technologist, please bracket mass with skin surface markers prior to scanning.'),('General','On Table Check','Technologist, please have Radiologist check images while patient is still on table.'),('General','Unauthorized Provider','Unauthorized ordering provider for this joint MRI examination. Please contact provider and recommend referral to Orthopedics, Rehabilitation Medicine, or Rheumatology.'),('Hydration','IV Inpatient','Normal saline 1-2 mL/kg/hour for 12 hours before and after scan'),('Hydration','IV Outpatient','Normal saline 1-2 mL/kg/hour for 3-6 hours before and after scan'),('Hydration','Oral','500 cc water during 2 hr before scan + 500 cc water during 2 hrs after scan'),('Premedication','Diphenhydramine ',' Diphenhydramine 25 mg PO 1 hr before scan for either protocol above'),('Premedication','Emergency',' Emergency protocol – Hydrocortisone 200 mg IV 6 hr before scan, 0 hr before scan, and 4-6 hr after scan + diphenhydramine 50 mg PO or IM or IV 1 hr before scan'),('Premedication','Methylprednisolone','Methylprednisolone 32 mg PO @ 12 hr and 2 hr before scan'),('Premedication','Prednisone','Prednisone 50 mg PO @ 13 hr, 7 hr and 1 hr before scan');
/*!40000 ALTER TABLE `raptor_boilerplate_protocol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_cache_data`
--

DROP TABLE IF EXISTS `raptor_cache_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_cache_data` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Who owns this data',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  `retry_delay` int(10) unsigned NOT NULL COMMENT 'Seconds to wait before retry a cache read',
  `max_age` int(10) unsigned NOT NULL COMMENT 'This data expires after this many seconds',
  `group_name` varchar(50) NOT NULL COMMENT 'Group to which this data belongs',
  `item_name` varchar(50) NOT NULL COMMENT 'Specific name of data in the group',
  `item_data` mediumblob NOT NULL COMMENT 'The cached data',
  PRIMARY KEY (`uid`,`group_name`,`item_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Runtime cached data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_cache_data`
--

LOCK TABLES `raptor_cache_data` WRITE;
/*!40000 ALTER TABLE `raptor_cache_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_cache_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_cache_flag`
--

DROP TABLE IF EXISTS `raptor_cache_flag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_cache_flag` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Who owns this data',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  `retry_delay` int(10) unsigned NOT NULL COMMENT 'Seconds to wait before retry a flag read',
  `max_age` int(10) unsigned NOT NULL COMMENT 'This flag expires after this many seconds',
  `group_name` varchar(50) NOT NULL COMMENT 'Group to which this flag belongs',
  `item_name` varchar(50) NOT NULL COMMENT 'Specific name of item in the group',
  `flag_name` varchar(20) NOT NULL COMMENT 'Specific name of flag in the group',
  `flag_value` int(10) unsigned NOT NULL COMMENT 'Value of the flag',
  PRIMARY KEY (`uid`,`group_name`,`item_name`,`flag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Runtime cache flags';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_cache_flag`
--

LOCK TABLES `raptor_cache_flag` WRITE;
/*!40000 ALTER TABLE `raptor_cache_flag` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_cache_flag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_checklist_question`
--

DROP TABLE IF EXISTS `raptor_checklist_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_checklist_question` (
  `type_cd` varchar(2) NOT NULL COMMENT 'Safety Checklist = SC',
  `relative_position` int(10) unsigned NOT NULL DEFAULT '500' COMMENT 'Lower numbers are asked before higher numbered questions',
  `modality_abbr` varchar(2) NOT NULL DEFAULT '' COMMENT 'Modality abbreviation or empty if question applies to all modalities',
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'A specific protocol for this question or empty if question applies to all protocols',
  `question_shortname` varchar(20) NOT NULL COMMENT 'Uniquely identify the current version of a question',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this question must be incremented if the text is ever updated',
  `question_tx` varchar(512) NOT NULL COMMENT 'Question to ask the user',
  `ask_yes_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User will be prompted to answer Yes',
  `ask_no_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User will be prompted to answer No',
  `ask_notsure_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User will be prompted to answer Not Sure',
  `ask_notapplicable_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User will be prompted to answer Not Applicable',
  `always_require_comment_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then comment is always required for this question.',
  `trigger_comment_on_yes_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then comment is requird when user answers Yes',
  `trigger_comment_on_no_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then comment is requird when user answers No',
  `trigger_comment_on_notsure_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then comment is requird when user answers Not Sure',
  `trigger_comment_on_notapplicable_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then comment is requird when user answers Not Applicable',
  `comment_prompt_tx` varchar(128) DEFAULT 'Explanation' COMMENT 'Prompt to show the user when asking for a comment',
  `failed_on_yes_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then question is considered failed when user answers Yes',
  `failed_on_no_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then question is considered failed when user answers No',
  `failed_on_notsure_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then question is considered failed when user answers Not Sure',
  `failed_on_notapplicable_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then question is considered failed when user answers Not Applicable',
  `the_okay_answer_tx` varchar(16) DEFAULT 'yes' COMMENT 'Identify the answer that indicates the good scenario.  Possible values are yes,no,notsure,notapplicable.  Leave as NULL if no answer is considered special.',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`question_shortname`),
  KEY `raptor_checklist_question_sorted_idx` (`type_cd`,`modality_abbr`,`protocol_shortname`,`relative_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Checklist questions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_checklist_question`
--

LOCK TABLES `raptor_checklist_question` WRITE;
/*!40000 ALTER TABLE `raptor_checklist_question` DISABLE KEYS */;
INSERT INTO `raptor_checklist_question` VALUES ('SC',16,'CT','','GOT_IMG_PROTOCOL',1,'Correct imaging protocol?',1,1,1,1,0,0,1,1,1,'Explain why the imaging protocol does not appear to be correct and what action you will take.',0,1,0,0,'yes','2015-10-02 14:52:16'),('SC',12,'','','GOT_IMG_SITE',1,'Confirmed imaging site?',1,1,1,0,0,0,1,1,1,'Explain why the imaging site cannot be confirmed.',0,1,0,0,'yes','2015-10-02 14:52:16'),('SC',10,'','','GOT_PATIENT',1,'Correct patient?',1,1,1,0,0,0,1,1,1,'Explain why the patient identity cannot be confirmed as appropriate for the procedure.',0,1,0,0,'yes','2015-10-02 14:52:16'),('SC',14,'','','SET_PAT_POSITION',1,'Correct patient positioning?',1,1,1,0,0,0,1,1,1,'Explain why the correct positioning cannot be answered as Yes.',0,1,0,0,'yes','2015-10-02 14:52:16'),('SC',18,'CT','','SET_SCNR_PARAMS',1,'Correct scanner parameters?',1,1,1,1,0,0,1,1,1,'Explain why you cannot confirm the scanner parameters are correct for this procedure.',0,1,0,0,'yes','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_checklist_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_checklist_question_replaced`
--

DROP TABLE IF EXISTS `raptor_checklist_question_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_checklist_question_replaced` (
  `type_cd` varchar(2) NOT NULL COMMENT 'Safety Checklist = SC',
  `relative_position` int(10) unsigned NOT NULL DEFAULT '500' COMMENT 'Lower numbers are asked before higher numbered questions',
  `modality_abbr` varchar(2) NOT NULL DEFAULT '' COMMENT 'Modality abbreviation or empty if question applies to all modalities',
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'A specific protocol for this question or empty if question applies to all protocols',
  `question_shortname` varchar(20) NOT NULL COMMENT 'Uniquely identify the current version of a question',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this question must be incremented if the text is ever updated',
  `question_tx` varchar(512) NOT NULL COMMENT 'Question to ask the user',
  `ask_yes_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User will be prompted to answer Yes',
  `ask_no_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User will be prompted to answer No',
  `ask_notsure_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'User will be prompted to answer Not Sure',
  `ask_notapplicable_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User will be prompted to answer Not Applicable',
  `always_require_comment_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then comment is always required for this question.',
  `trigger_comment_on_yes_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then comment is requird when user answers Yes',
  `trigger_comment_on_no_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then comment is requird when user answers No',
  `trigger_comment_on_notsure_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then comment is requird when user answers Not Sure',
  `trigger_comment_on_notapplicable_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then comment is requird when user answers Not Applicable',
  `comment_prompt_tx` varchar(128) DEFAULT 'Explanation' COMMENT 'Prompt to show the user when asking for a comment',
  `failed_on_yes_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then question is considered failed when user answers Yes',
  `failed_on_no_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 1 then question is considered failed when user answers No',
  `failed_on_notsure_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then question is considered failed when user answers Not Sure',
  `failed_on_notapplicable_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'If 1 then question is considered failed when user answers Not Applicable',
  `the_okay_answer_tx` varchar(16) DEFAULT 'yes' COMMENT 'Identify the answer that indicates the good scenario.  Possible values are yes,no,notsure,notapplicable.  Leave as NULL if no answer is considered special.',
  `created_dt` datetime NOT NULL COMMENT 'When this record was originally created',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was replaced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replaced checklist questions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_checklist_question_replaced`
--

LOCK TABLES `raptor_checklist_question_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_checklist_question_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_checklist_question_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_contraindication_measure`
--

DROP TABLE IF EXISTS `raptor_contraindication_measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_contraindication_measure` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping in a logical way',
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this measure',
  `return_type` varchar(20) NOT NULL COMMENT 'The returned data type',
  `purpose_tx` varchar(1024) NOT NULL COMMENT 'Static text describing purpose of this measure',
  `criteria_tx` varchar(4000) DEFAULT NULL COMMENT 'The measure formula or INPUT if no formula',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this measure record should not be edited',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0 then this measure is not available for use in new expressions',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was updated',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`measure_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A measure used by the simple rules engine';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_contraindication_measure`
--

LOCK TABLES `raptor_contraindication_measure` WRITE;
/*!40000 ALTER TABLE `raptor_contraindication_measure` DISABLE KEYS */;
INSERT INTO `raptor_contraindication_measure` VALUES ('Renal','HAS_ACUTE_LOW_EGFR',1,'boolean','Does the patient have sudden low EGFR?','(LATEST_EGFR < 60) and (MIN_EGFR_60DAYS > 89) ',0,1,'2015-08-20 15:00:00','2014-08-01 00:00:00'),('Contrast','HAS_ANY_CONTRAST',1,'boolean','Has any contrast been selected for this patient?','GIVE_CONTRAST_ENTERIC or GIVE_CONTRAST_IV',0,1,'2014-07-29 00:00:00','2014-07-29 00:00:00'),('Radioisotope','HAS_ANY_RADIOISOTOPE',1,'boolean','Will this patient require any radioisotopes?','GIVE_RADIOISOTOPE_ENTERIC or GIVE_RADIOISOTOPE_IV',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Sedation','HAS_ANY_SEDATION',1,'boolean','Will this patient receive any sedation?','GIVE_SEDATION_ORAL or GIVE_SEDATION_IV',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Renal','HAS_ATRISK_MEDS',1,'boolean','Is this patient taking any medications from at-risk list?','CURRENT_MEDS somematch KWL_ATRISK_MEDS',0,1,'2015-09-14 00:00:00','2015-09-14 00:00:00'),('Blood','HAS_BLOODTHINNER',1,'boolean','Is this patient taking any medications from the blood thinner list?','CURRENT_MEDS somematch KWL_BLOOD_THINNER',0,1,'2014-08-14 00:00:00','2014-08-14 00:00:00'),('Renal','HAS_CHRONIC_LOW_EGFR',1,'boolean','Does the patient have low EGFR and show a trend down?','(LATEST_EGFR < 60) and (MIN_EGFR_90DAYS >= MIN_EGFR_30DAYS) ',0,1,'2015-08-20 15:00:00','2014-08-01 00:00:00'),('Allergy','HAS_CONTRAST_ALLERGY',1,'boolean','Does the patient\'s allergy list match any contrast allergen keywords?','CURRENT_ALLERGIES somematch KWL_CONTRAST_ALLERGY_INDICATOR',0,1,'2014-08-02 00:00:00','2014-08-02 00:00:00'),('Renal','HAS_EGFR_OVER_89',1,'boolean','Does the patient have a good eGFR reading?','LATEST_EGFR > 90',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Renal','HAS_EGFR_UNDER_30',1,'boolean','Does the patient have a critically low eGFR reading?','LATEST_EGFR < 30',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Renal','HAS_EGFR_UNDER_60',1,'boolean','Does the patient have a borderline low eGFR reading?','LATEST_EGFR < 60',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Renal','HAS_KIDNEY_FAILURE',1,'boolean','Does the patient have kidney failure?','LATEST_EGFR < 15',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Availability','HAS_RARE_CONTRAST',1,'boolean','Does the patient\'s contrast dose list match any of the rare or specially controlled contrast keywords?','CURRENT_CONTRASTS somematch KWL_RARE_CONTRAST',0,1,'2015-08-20 18:00:00','2014-08-30 00:00:00'),('Availability','HAS_RARE_RADIOISOTOPE',1,'boolean','Does the patient\'s radioisotope dose list match any of the rare or specially controlled radioisotope keywords?','CURRENT_RADIOISOTOPES somematch KWL_RARE_RADIOISOTOPE',0,1,'2015-08-20 18:00:00','2014-08-30 00:00:00'),('Gender','IS_FEMALE',1,'boolean','Is this patient a woman?','GENDER = \"F\"',0,1,'2014-07-31 00:00:00','2014-07-31 00:00:00'),('Size','IS_HEAVY',1,'boolean','Is this a large patient that may need special considerations for equipment?','WEIGHT_KG > 140',0,1,'2014-08-01 00:00:00','2014-08-01 00:00:00'),('Gender','IS_MALE',1,'boolean','Is this patient a man?','GENDER = \"M\"',0,1,'2014-07-31 00:00:00','2014-07-31 00:00:00'),('Modality','IS_MODALITY_CT',1,'boolean','Is the selected modality Computed Tomography?','MODALITY = \"CT\"',0,1,'2014-10-01 00:00:00','2014-10-01 00:00:00'),('Modality','IS_MODALITY_FL',1,'boolean','Is the selected modality Fluoroscopy?','MODALITY = \"FL\"',0,1,'2014-10-01 00:00:00','2014-10-01 00:00:00'),('Modality','IS_MODALITY_IR',1,'boolean','Is the selected modality Interventional Radiology?','MODALITY = \"IR\"',0,1,'2014-10-01 00:00:00','2014-10-01 00:00:00'),('Modality','IS_MODALITY_MR',1,'boolean','Is the selected modality Magnetic Resonance Imaging ?','MODALITY = \"MR\"',0,1,'2014-10-01 00:00:00','2014-10-01 00:00:00'),('Modality','IS_MODALITY_NM',1,'boolean','Is the selected modality Nuclear Medicine?','MODALITY = \"NM\"',0,1,'2014-10-01 00:00:00','2014-10-01 00:00:00'),('Modality','IS_MODALITY_US',1,'boolean','Is the selected modality Ultrasound?','MODALITY = \"US\"',0,1,'2014-10-01 00:00:00','2014-10-01 00:00:00'),('Age','IS_OVER_AGE_60',1,'boolean','Is this patient over age 60?','AGE > 60',0,1,'2014-07-29 00:00:00','2014-07-29 00:00:00'),('Age','IS_UNDER_AGE_18',1,'boolean','Is this patient under age 18?','AGE < 18',0,1,'2015-08-20 15:00:00','2015-08-20 15:00:00'),('Age','IS_UNDER_AGE_50',1,'boolean','Is this patient under age 50?','AGE < 50',0,1,'2014-07-31 00:00:00','2014-07-31 00:00:00'),('Age','IS_UNDER_AGE_60',1,'boolean','Is this patient under age 60?','AGE < 60',0,1,'2015-08-20 15:00:00','2015-08-20 15:00:00'),('Medication','KWL_ATRISK_MEDS',1,'array of text','If patient has medication that matches any of these keywords then this indicates they are taking an at-risk medication','TEXT FROM MANAGED LIST',0,1,'2015-09-14 00:00:00','2015-09-14 00:00:00'),('Medication','KWL_BLOOD_THINNER',1,'array of text','If patient has medication that matches any of these keywords then this indicates they are on a blood thinner.','TEXT FROM MANAGED LIST',0,1,'2014-09-24 00:00:00','2014-09-24 00:00:00'),('Contrast','KWL_CONTRAST_ALLERGY_INDICATOR',1,'array of text','If patient has allergy that matches any of these keywords then there is a possible contrast allergy.','TEXT FROM MANAGED LIST',0,1,'2014-09-24 00:00:00','2014-09-24 00:00:00'),('Policy','KWL_RARE_CONTRAST',1,'array of text','If order has contrast that matches any of these keywords then this indicates that a rare or special procurement contrast has been selected.','TEXT FROM MANAGED LIST',0,1,'2014-09-24 00:00:00','2014-09-24 00:00:00'),('Policy','KWL_RARE_RADIOISOTOPE',1,'array of keywords','If order has radioisotope that matches any of these keywords then this indicates that a rare or special procurement radioisotope has been selected.','TEXT FROM MANAGED LIST',0,1,'2014-09-24 00:00:00','2014-09-24 00:00:00');
/*!40000 ALTER TABLE `raptor_contraindication_measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_contraindication_measure_replaced`
--

DROP TABLE IF EXISTS `raptor_contraindication_measure_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_contraindication_measure_replaced` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping in a logical way',
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this measure',
  `return_type` varchar(20) NOT NULL COMMENT 'The returned data type',
  `purpose_tx` varchar(1024) NOT NULL COMMENT 'Static text describing purpose of this measure',
  `criteria_tx` varchar(4000) DEFAULT NULL COMMENT 'The measure formula or INPUT if no formula',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this measure record should not be edited',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0 then this measure is not available for use in new expressions',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was updated',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`measure_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A measure used by the simple rules engine';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_contraindication_measure_replaced`
--

LOCK TABLES `raptor_contraindication_measure_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_contraindication_measure_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_contraindication_measure_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_contraindication_rule`
--

DROP TABLE IF EXISTS `raptor_contraindication_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_contraindication_rule` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping rules in a logical way',
  `rule_nm` varchar(40) NOT NULL COMMENT 'Must be unique',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Increases each time change is saved',
  `summary_msg_tx` varchar(80) NOT NULL COMMENT 'Static summary text to show the user when rule is triggered',
  `msg_tx` varchar(512) NOT NULL COMMENT 'Text to show the user when rule is triggered',
  `explanation` varchar(2048) NOT NULL COMMENT 'Explanation of the rule purpose',
  `req_ack_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 1 then an acknowledgement is required',
  `trigger_crit` varchar(4000) NOT NULL COMMENT 'The criteria that triggers the rule',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this rule record cannot be edited',
  `active_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 0 then this rule is not active',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `created_dt` datetime DEFAULT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`rule_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A simple rules engine rule';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_contraindication_rule`
--

LOCK TABLES `raptor_contraindication_rule` WRITE;
/*!40000 ALTER TABLE `raptor_contraindication_rule` DISABLE KEYS */;
INSERT INTO `raptor_contraindication_rule` VALUES ('Scheduling','ATRISK_OUTPATIENT_RENAL_LABS',1,'Renal risk','Laboratory renal function required within 14 days for at risk outpatients. Please assure blood draw is ordered','Laboratory renal function required within 14 days for at risk outpatients. Please assure blood draw is ordered',1,'',0,1,'2014-07-28 00:40:48',NULL),('Allergy','CONSENT_ALLERGY_CONTRAINDICATION',4,'Allergy to IV Contrast risk','Consent for IV contrast required, potential history of imaging contrast dye allergy','Consent for IV contrast required, potential history of imaging contrast dye allergy',1,'AnyFlagTrue(GIVE_CONTRAST_IV) and AllFlagsTrue(HAS_CONTRAST_ALLERGY)',0,1,'2015-06-15 19:45:00',NULL),('Allergy','CONTRAST_ALLERGY_CONTRAINDICATION',4,'Allergy risk','Contrast allergy risk','CONSENT FOR IV CONTRAST REQUIRED, POTENTIAL HISTORY OF IMAGING CONTRAST DYE ALLERGY',1,'AnyFlagTrue(GIVE_CONTRAST_IV , GIVE_CONTRAST_ENTERIC) and AllFlagsTrue(HAS_CONTRAST_ALLERGY)',0,1,'2014-09-24 12:29:00',NULL),('Renal','CONTRAST_RENAL_IMPAIRMENT_ACUTE_III',4,'Renal risk','At risk for contrast. Acute Stage 3 Renal Impairment.','At risk for contrast. Acute Stage 3 Renal Impairment.',1,'AllFlagsTrue(HAS_ACUTE_LOW_EGFR , HAS_EGFR_UNDER_60, GIVE_CONTRAST_IV) and AllFlagsFalse(HAS_EGFR_UNDER_30)',0,1,'2015-06-15 20:08:00',NULL),('Renal','CONTRAST_RENAL_IMPAIRMENT_CHRONIC_III',3,'Renal risk','At risk for contrast. Chronic Stage 3 Renal Impairment.','At risk for contrast. Chronic Stage 3 Renal Impairment.',1,'AllFlagsTrue(HAS_CHRONIC_LOW_EGFR, HAS_EGFR_UNDER_60, GIVE_CONTRAST_IV) and AllFlagsFalse(HAS_EGFR_UNDER_30)',0,1,'2015-06-15 20:06:00',NULL),('Renal','CONTRAST_RENAL_IMPAIRMENT_IV_V',5,'Renal risk','At risk for contrast. Stage IV or Stage V Renal Impairment.','At risk for contrast. Stage IV or Stage V Renal Impairment.',1,'AnyFlagTrue(HAS_KIDNEY_FAILURE, ) and AllFlagsTrue(HAS_EGFR_UNDER_30, GIVE_CONTRAST_IV)',0,1,'2015-06-15 20:15:00',NULL),('Age','CONTRAST_RISK_AGE',7,'Age risk','Patient age of 60 years or older carries increased risk for IV contrast complications. Consider noncontrast exam or alternate imaging modality.','Studies of serum creatinine suggest age, hypertension, and diabetes are important predictors of creatinine elevation. In addition, many VA centers use age (with variable thresholds) to determine the need for creatinine testing and this practice is also ingrained in the department culture at VA.',0,'AllFlagsTrue(IS_OVER_AGE_60, GIVE_CONTRAST_IV)',0,1,'2015-06-15 20:20:00',NULL),('Renal','DIAGNOSTIC_EXAM_RISK_MED',6,'Renal risk','Patient medication use increases risk for IV contrast complications.','Patient medication use increases risk for IV contrast complications.',1,'AllFlagsTrue(GIVE_CONTRAST_IV , IS_DIAGNOSTIC_EXAM)',0,1,'2015-06-26 14:54:00',NULL),('Bleeding','IMGGUIDE_RISK_MED',7,'Bleeding risk','At-Risk Medications for Image-Guided Procedures. Increased risk of bleeding complications.','At-Risk Medications for Image-Guided Procedures. Increased risk of bleeding complications.',1,'AllFlagsTrue(IS_IMG_GUIDED_EXAM, HAS_ATRISK_MEDS)',0,1,'2015-09-14 16:17:00',NULL),('Renal','IMPAIRED_RENAL_ALTERNATE_STUDY',3,'Renal risk','Impaired renal function, consider non-contrast study or alternative imaging modality','Impaired renal function, consider non-contrast study or alternative imaging modality',1,'AllFlagsTrue(HAS_EGFR_UNDER_60, GIVE_CONTRAST_IV)',0,1,'2015-06-15 21:01:00',NULL),('Renal','IMPAIRED_RENAL_PRE_POST_HYDRATION',2,'Renal risk','Impaired renal function, consider pre and post-exam hydration for renal protection if IV contrast will be administered.','Impaired renal function, consider pre and post-exam hydration for renal protection if IV contrast will be administered',1,'AllFlagsTrue(HAS_EGFR_UNDER_60, GIVE_CONTRAST_IV)',0,1,'2015-06-15 21:05:00',NULL),('Renal','IMPAIRED_RENAL_REDUCED_CONTRAST',2,'Renal risk','Impaired renal function, consider reduced dose of IV contrast if IV contrast will be administered','Impaired renal function, consider reduced dose of IV contrast if IV contrast will be administered',1,'AllFlagsTrue(HAS_EGFR_UNDER_60, GIVE_CONTRAST_IV)',0,1,'2015-06-15 21:06:00',NULL),('Scheduling','INPATIENT_RENAL_LABS',2,'Renal risk','Laboratory renal function required within 48 hours for inpatients. Please assure blood draw is ordered','Laboratory renal function required within 48 hours for inpatients. Please assure blood draw is ordered',1,'AllFlagsTrue(GIVE_CONTRAST_IV)',0,0,'2015-06-15 21:11:00',NULL),('Scheduling','NONRISK_OUTPATIENT_RENAL_LABS',2,'Renal risk','Laboratory renal function required within 30 days for routine outpatients. Please assure blood draw is ordered','Laboratory renal function required within 30 days for routine outpatients. Please assure blood draw is ordered',1,'AllFlagsTrue(GIVE_CONTRAST_IV)',0,0,'2015-06-15 21:13:00',NULL),('General','POTENTIAL_DUPLICATE_STUDY',4,'Duplicate order risk','Please review potential duplicate studies in the worklist.','Click the #P column in the worklist for this order to group together all other active orders for this patient.',1,'AnyFlagTrue(IS_POSSIBLE_DUP_PROC)',0,1,'2014-10-03 14:19:00',NULL),('Allergy','PROPHYLACTIC_ALLERGY_PREMEDICATION',3,'Allergy risk','Potential history of imaging contrast dye allergy, consider prophylactic premedication protocol','Potential history of imaging contrast dye allergy, consider prophylactic premedication protocol',0,'AllFlagsTrue( HAS_ANY_CONTRAST , HAS_CONTRAST_ALLERGY )',0,1,'2014-09-21 21:56:00',NULL),('Rare','RARE_DOSE',6,'Rare or special procurement dose','One or more rare or special process related doses have been selected.','This exam has rare or difficult to procure doses. Please use caution as to not waste valuable resources and allow enough time for procurement when scheduling the exam.',1,'AnyFlagTrue(HAS_RARE_CONTRAST , HAS_RARE_RADIOISOTOPE)',0,1,'2015-04-20 17:30:00',NULL),('Renal','RENAL_IMPAIRMENT',2,'Renal risk','This patient may have Renal Impairment and require consent prior to contrast enhanced advanced medical imaging.','This patient may have Renal Impairment and require consent prior to contrast enhanced advanced medical imaging.',1,'AllFlagsTrue(HAS_EGFR_UNDER_30)',0,1,'2014-08-02 03:43:00',NULL),('Age','RISK_AGE',5,'Age risk','Patient is over age 60','Routine creatinine testing prior to contrast administration is not necessary in all patients.  The major indications are age over 60, history of renal insufficiency, diabetes mellitus, or hypertension. ',1,'AllFlagsTrue(IS_OVER_AGE_60)',0,1,'2014-08-30 16:43:00',NULL),('Vascular','RISK_DEHYDRATION',1,'Vascular risk','Myeloma or Sickle Cell disease (risks for dehydration)','Myeloma or Sickle Cell disease (risks for dehydration)',1,'',0,1,'2014-07-28 00:40:48',NULL),('Renal','RISK_FAMILY_HX_KIDNEY_DISEASE',2,'Family history of kidney failure','Family history of kidney failure','Family history of kidney failure',1,'',0,1,'2014-07-30 20:35:00',NULL),('General','RISK_GOUT',2,'GOUT','GOUT','GOUT',1,'',0,1,'2014-07-31 17:23:00',NULL),('Renal','RISK_HX_KIDNEY_DISEASE',1,'Diabetes risk','A history of kidney disease (including kidney tumors, solitary kidney, renal transplantation, recurrent UTI, etc.)','A history of kidney disease (including kidney tumors, solitary kidney, renal transplantation, recurrent UTI, etc.)',1,'',0,1,'2014-07-28 00:40:48',NULL),('Vascular','RISK_HX_VASCULAR_SURGERY',1,'Vascular risk','A history of vascular surgery for atherosclerosis','A history of vascular surgery for atherosclerosis',1,'',0,1,'2014-07-28 00:40:48',NULL),('Diabetes','RISK_INSULIN_DEPENDENT_DIABETES',1,'Diabetes risk','Insulin-dependent diabetes >2 yrs','Insulin-dependent diabetes >2 yrs',1,'',0,1,'2014-07-28 00:40:48',NULL),('General','RISK_LIVER_WORKUP',3,'Liver txp work-up','Liver txp work-up','Liver txp work-up',1,'',0,1,'2014-07-30 20:45:00',NULL),('General','RISK_LUPUS',1,'General risk','Systemic Lupus Erythematosis','Systemic Lupus Erythematosis',1,'',0,1,'2014-07-28 00:40:48',NULL),('General','RISK_NEPHROTOXIC',1,'General risk','On nephrotoxic drugs','On nephrotoxic drugs',1,'',0,1,'2014-07-28 00:40:48',NULL),('Diabetes','RISK_NONINSULIN_DEPENDENT_DIABETES',1,'Diabetes risk','Non-insulin-dependent diabetes >5 yrs','Non-insulin-dependent diabetes >5 yrs',1,'',0,1,'2014-07-28 00:40:48',NULL);
/*!40000 ALTER TABLE `raptor_contraindication_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_contraindication_rule_replaced`
--

DROP TABLE IF EXISTS `raptor_contraindication_rule_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_contraindication_rule_replaced` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping rules in a logical way',
  `rule_nm` varchar(40) NOT NULL COMMENT 'Must be unique',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Increases each time change is saved',
  `summary_msg_tx` varchar(80) NOT NULL COMMENT 'Static summary text to show the user when rule is triggered',
  `msg_tx` varchar(512) NOT NULL COMMENT 'Text to show the user when rule is triggered',
  `explanation` varchar(2048) NOT NULL COMMENT 'Explanation of the rule purpose',
  `req_ack_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 1 then an acknowledgement is required',
  `trigger_crit` varchar(4000) NOT NULL COMMENT 'The criteria that triggers the rule',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this rule record cannot be edited',
  `active_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 0 then this rule is not active',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `created_dt` datetime DEFAULT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`rule_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A simple rules engine rule';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_contraindication_rule_replaced`
--

LOCK TABLES `raptor_contraindication_rule_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_contraindication_rule_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_contraindication_rule_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_cprs_codes`
--

DROP TABLE IF EXISTS `raptor_cprs_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_cprs_codes` (
  `cprs_cd` varchar(6) NOT NULL COMMENT 'The CPRS code',
  `exclude_from_worklist_yn` int(10) unsigned NOT NULL COMMENT 'If 1 then orders of this type are ignored by RAPTOR',
  `contrast_yn` int(10) unsigned DEFAULT NULL COMMENT 'If 1, then contrast, else no contrast.',
  `modality_abbr` varchar(2) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the modality associated with this order',
  `service_nm` varchar(10) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the service associated with this order',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relevant information about codes used in CPRS.  In...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_cprs_codes`
--

LOCK TABLES `raptor_cprs_codes` WRITE;
/*!40000 ALTER TABLE `raptor_cprs_codes` DISABLE KEYS */;
INSERT INTO `raptor_cprs_codes` VALUES ('74000',1,NULL,'','','2015-10-02 14:52:16'),('74010',1,NULL,'','','2015-10-02 14:52:16'),('73050',1,NULL,'','','2015-10-02 14:52:16'),('75710',0,NULL,'CT','','2015-10-02 14:52:16'),('73600',1,NULL,'','','2015-10-02 14:52:16'),('73610',1,NULL,'','','2015-10-02 14:52:16'),('73650',1,NULL,'','','2015-10-02 14:52:16'),('71020',1,NULL,'','','2015-10-02 14:52:16'),('71035',1,NULL,'','','2015-10-02 14:52:16'),('71022',1,NULL,'','','2015-10-02 14:52:16'),('75716',0,NULL,'CT','','2015-10-02 14:52:16'),('74300',0,NULL,'CT','','2015-10-02 14:52:16'),('74290',0,NULL,'CT','','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_cprs_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_cpt_codes`
--

DROP TABLE IF EXISTS `raptor_cpt_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_cpt_codes` (
  `cpt_cd` varchar(5) NOT NULL COMMENT 'The CPT code',
  `exclude_from_worklist_yn` int(10) unsigned NOT NULL COMMENT 'If 1 then orders of this type are ignored by RAPTOR',
  `contrast_yn` int(10) unsigned DEFAULT NULL COMMENT 'If 1, then contrast, else no contrast.',
  `modality_abbr` varchar(2) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the modality associated with this order',
  `service_nm` varchar(10) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the service associated with this order',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relevant information about CPT codes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_cpt_codes`
--

LOCK TABLES `raptor_cpt_codes` WRITE;
/*!40000 ALTER TABLE `raptor_cpt_codes` DISABLE KEYS */;
INSERT INTO `raptor_cpt_codes` VALUES ('74000',1,NULL,'','','2015-10-02 14:52:16'),('74010',1,NULL,'','','2015-10-02 14:52:16'),('73050',1,NULL,'','','2015-10-02 14:52:16'),('75710',0,NULL,'CT','','2015-10-02 14:52:16'),('73600',1,NULL,'','','2015-10-02 14:52:16'),('73610',1,NULL,'','','2015-10-02 14:52:16'),('73650',1,NULL,'','','2015-10-02 14:52:16'),('71020',1,NULL,'','','2015-10-02 14:52:16'),('71035',1,NULL,'','','2015-10-02 14:52:16'),('71022',1,NULL,'','','2015-10-02 14:52:16'),('75716',0,NULL,'CT','','2015-10-02 14:52:16'),('74300',0,NULL,'CT','','2015-10-02 14:52:16'),('74290',0,NULL,'CT','','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_cpt_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_group`
--

DROP TABLE IF EXISTS `raptor_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_group` (
  `name_tx` varchar(10) NOT NULL DEFAULT '' COMMENT 'Group name',
  `desc_tx` varchar(128) NOT NULL DEFAULT '' COMMENT 'Group name',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`name_tx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lookup table of groups to which users can belong';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_group`
--

LOCK TABLES `raptor_group` WRITE;
/*!40000 ALTER TABLE `raptor_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_group_anatomy_keyword`
--

DROP TABLE IF EXISTS `raptor_group_anatomy_keyword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_group_anatomy_keyword` (
  `name_tx` varchar(10) NOT NULL DEFAULT '' COMMENT 'Group name',
  `weightgroup` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Group 1 has the most weight, 2 less, 3 least',
  `keyword` varchar(32) NOT NULL DEFAULT '' COMMENT 'Anatomy keyword',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`name_tx`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Default anatomy keywords for a group';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_group_anatomy_keyword`
--

LOCK TABLES `raptor_group_anatomy_keyword` WRITE;
/*!40000 ALTER TABLE `raptor_group_anatomy_keyword` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_group_anatomy_keyword` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_group_modality`
--

DROP TABLE IF EXISTS `raptor_group_modality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_group_modality` (
  `group_nm` varchar(10) NOT NULL DEFAULT '' COMMENT 'Group name',
  `modality_abbr` varchar(2) NOT NULL COMMENT 'Modality abbreviation',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`group_nm`,`modality_abbr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Default modalities associated with a group';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_group_modality`
--

LOCK TABLES `raptor_group_modality` WRITE;
/*!40000 ALTER TABLE `raptor_group_modality` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_group_modality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_group_service`
--

DROP TABLE IF EXISTS `raptor_group_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_group_service` (
  `group_nm` varchar(10) NOT NULL DEFAULT '' COMMENT 'Group name',
  `service_nm` varchar(10) NOT NULL COMMENT 'Service name',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`group_nm`,`service_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Default services associated with a group';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_group_service`
--

LOCK TABLES `raptor_group_service` WRITE;
/*!40000 ALTER TABLE `raptor_group_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_group_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_icd_codes`
--

DROP TABLE IF EXISTS `raptor_icd_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_icd_codes` (
  `icd_cd` varchar(10) NOT NULL COMMENT 'The ICD code',
  `exclude_from_worklist_yn` int(10) unsigned NOT NULL COMMENT 'If 1 then orders of this type are ignored by RAPTOR',
  `contrast_yn` int(10) unsigned DEFAULT NULL COMMENT 'If 1, then contrast, else no contrast.',
  `modality_abbr` varchar(2) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the modality associated with this order',
  `service_nm` varchar(10) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the service associated with this order',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relevant information about ICD codes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_icd_codes`
--

LOCK TABLES `raptor_icd_codes` WRITE;
/*!40000 ALTER TABLE `raptor_icd_codes` DISABLE KEYS */;
INSERT INTO `raptor_icd_codes` VALUES ('74000',1,NULL,'','','2015-10-02 14:52:16'),('74010',1,NULL,'','','2015-10-02 14:52:16'),('73050',1,NULL,'','','2015-10-02 14:52:16'),('75710',0,NULL,'CT','','2015-10-02 14:52:16'),('73600',1,NULL,'','','2015-10-02 14:52:16'),('73610',1,NULL,'','','2015-10-02 14:52:16'),('73650',1,NULL,'','','2015-10-02 14:52:16'),('71020',1,NULL,'','','2015-10-02 14:52:16'),('71035',1,NULL,'','','2015-10-02 14:52:16'),('71022',1,NULL,'','','2015-10-02 14:52:16'),('75716',0,NULL,'CT','','2015-10-02 14:52:16'),('74300',0,NULL,'CT','','2015-10-02 14:52:16'),('74290',0,NULL,'CT','','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_icd_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_contrast`
--

DROP TABLE IF EXISTS `raptor_list_contrast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_contrast` (
  `type_nm` varchar(8) NOT NULL COMMENT 'Oral/IV/Enteric',
  `option_tx` varchar(100) NOT NULL COMMENT 'The text to show',
  `ct_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to CT?',
  `mr_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to MRI?',
  `nm_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Nuclear Medicine?',
  `fl_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Fluoroscopy?',
  `us_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Ultrasound?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Options for contrast panel';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_contrast`
--

LOCK TABLES `raptor_list_contrast` WRITE;
/*!40000 ALTER TABLE `raptor_list_contrast` DISABLE KEYS */;
INSERT INTO `raptor_list_contrast` VALUES ('ENTERIC','GastroView 450cc during 1-2 hrs before scan',1,0,1,1,1),('ENTERIC','H2O 450cc over 20min prescan + 150cc on table',1,0,1,1,1),('ENTERIC','Other (see protocol notes)',1,1,1,1,1),('ENTERIC','Rectal',1,0,1,1,1),('ENTERIC','RediCat 450cc during 1-2 hrs before scan',1,0,1,1,1),('ENTERIC','Volumen 1350cc protocol',1,0,1,1,1),('ENTERIC','Volumen 450cc protocol',1,0,1,1,1),('ENTERIC','Volumen 900cc protocol',1,0,1,1,1),('IV','Ablavar 0.03 mmol/kg',0,1,0,1,1),('IV','Eovist 0.025 mmol/kg',0,1,0,1,1),('IV','Isovue 370',1,0,1,1,1),('IV','MultiHance 0.05 mmol/kg (\"1/2 Dose\")',0,1,0,1,1),('IV','MultiHance 0.1 mmol/kg',0,1,0,1,1),('IV','Other (see protocol notes)',1,1,1,1,1),('IV','ProHance 0.1 mmol/kg',0,1,0,1,1),('IV','Ultravist 300',1,0,1,1,1),('IV','Visipaque 320',1,0,1,1,1);
/*!40000 ALTER TABLE `raptor_list_contrast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_hydration`
--

DROP TABLE IF EXISTS `raptor_list_hydration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_hydration` (
  `type_nm` varchar(8) NOT NULL COMMENT 'Oral/IV/Enteric',
  `option_tx` varchar(100) NOT NULL COMMENT 'The text to show',
  `ct_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to CT?',
  `mr_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to MRI?',
  `nm_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Nuclear Medicine?',
  `fl_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Fluoroscopy?',
  `us_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Ultrasound?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Options for hydration panel';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_hydration`
--

LOCK TABLES `raptor_list_hydration` WRITE;
/*!40000 ALTER TABLE `raptor_list_hydration` DISABLE KEYS */;
INSERT INTO `raptor_list_hydration` VALUES ('ORAL','500cc H2O over 2hr pre-scan + post-scan',1,1,1,1,1),('ORAL','Other (See protocol notes)',1,1,1,1,1),('IV','OutPt- NS 1-2mL/kg/hr 3-6hr pre & postscan',1,1,1,1,1),('IV','InPt- NS 1-2 mL/kg/hr 12 hr pre & postscan',1,1,1,1,1),('IV','Other (See protocol notes)',1,1,1,1,1);
/*!40000 ALTER TABLE `raptor_list_hydration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_kw_with_contrast`
--

DROP TABLE IF EXISTS `raptor_list_kw_with_contrast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_kw_with_contrast` (
  `phrase_tx` varchar(50) NOT NULL COMMENT 'The exact text to look for'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Phrases that indicate an order includes contrast';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_kw_with_contrast`
--

LOCK TABLES `raptor_list_kw_with_contrast` WRITE;
/*!40000 ALTER TABLE `raptor_list_kw_with_contrast` DISABLE KEYS */;
INSERT INTO `raptor_list_kw_with_contrast` VALUES ('W CONT'),('WITH CONT'),('W/IV CONT'),('INCLUDE CONT'),('INC CONT');
/*!40000 ALTER TABLE `raptor_list_kw_with_contrast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_kw_withandwithout_contrast`
--

DROP TABLE IF EXISTS `raptor_list_kw_withandwithout_contrast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_kw_withandwithout_contrast` (
  `phrase_tx` varchar(50) NOT NULL COMMENT 'The exact text to look for'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Phrases that indicate an order is with and without contrast';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_kw_withandwithout_contrast`
--

LOCK TABLES `raptor_list_kw_withandwithout_contrast` WRITE;
/*!40000 ALTER TABLE `raptor_list_kw_withandwithout_contrast` DISABLE KEYS */;
INSERT INTO `raptor_list_kw_withandwithout_contrast` VALUES ('W&WO CONT'),('W&W/O CONT'),('WITH AND WITHOUT CONT');
/*!40000 ALTER TABLE `raptor_list_kw_withandwithout_contrast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_kw_without_contrast`
--

DROP TABLE IF EXISTS `raptor_list_kw_without_contrast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_kw_without_contrast` (
  `phrase_tx` varchar(50) NOT NULL COMMENT 'The exact text to look for'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Phrases that indicate an order is without contrast';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_kw_without_contrast`
--

LOCK TABLES `raptor_list_kw_without_contrast` WRITE;
/*!40000 ALTER TABLE `raptor_list_kw_without_contrast` DISABLE KEYS */;
INSERT INTO `raptor_list_kw_without_contrast` VALUES ('WO CONT'),('W/O CONT'),('WN CONT'),('W/N CONT'),('NO CONT'),('WITHOUT CONT'),('NON-CONT');
/*!40000 ALTER TABLE `raptor_list_kw_without_contrast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_modality`
--

DROP TABLE IF EXISTS `raptor_list_modality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_modality` (
  `modality_abbr` varchar(2) NOT NULL COMMENT 'Modality abbreviation',
  `modality_desc` varchar(100) NOT NULL COMMENT 'Modality description',
  `prefixes` varchar(100) NOT NULL COMMENT 'Comma delimited prefixes that indicate study is of this type  (Look only at start of study text)',
  `keywords` varchar(100) NOT NULL COMMENT 'Comma delimited keywords that indicate study is of this type.  (Look anywhere in the text.)',
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this record was last updated',
  PRIMARY KEY (`modality_abbr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Modalities in which users specialize';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_modality`
--

LOCK TABLES `raptor_list_modality` WRITE;
/*!40000 ALTER TABLE `raptor_list_modality` DISABLE KEYS */;
INSERT INTO `raptor_list_modality` VALUES ('CT','Computed Tomography','CT,CAT','CT,CAT','2015-10-02 18:52:16'),('FL','Fluoroscopy','FL','Flouro,Fluoroscopy','2015-10-02 18:52:16'),('IR','Interventional Radiology','IR','Interventional Radiology','2015-10-02 18:52:16'),('MR','Magnetic Resonance Imaging','MR','MR,MRI,MAGNETIC','2015-10-02 18:52:16'),('NM','Nuclear Medicine','NM,NUC','NUC','2015-10-02 18:52:16'),('US','Ultrasound','US','Ultrasound','2015-10-02 18:52:16');
/*!40000 ALTER TABLE `raptor_list_modality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_radiation_dose_target`
--

DROP TABLE IF EXISTS `raptor_list_radiation_dose_target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_radiation_dose_target` (
  `id` int(10) unsigned NOT NULL COMMENT 'ID for the area',
  `area_nm` varchar(32) DEFAULT '' COMMENT 'Target area',
  `area_desc` varchar(1024) DEFAULT '' COMMENT 'Description of the target area',
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Radiation exposure area lookup';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_radiation_dose_target`
--

LOCK TABLES `raptor_list_radiation_dose_target` WRITE;
/*!40000 ALTER TABLE `raptor_list_radiation_dose_target` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_list_radiation_dose_target` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_radioisotope`
--

DROP TABLE IF EXISTS `raptor_list_radioisotope`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_radioisotope` (
  `type_nm` varchar(8) NOT NULL COMMENT 'Oral/IV/Enteric',
  `option_tx` varchar(100) NOT NULL COMMENT 'The text to show',
  `ct_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Applies to CT?',
  `mr_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Applies to MRI?',
  `nm_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Nuclear Medicine?',
  `fl_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Fluoroscopy?',
  `us_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Ultrasound?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Options for radiological pharma panel';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_radioisotope`
--

LOCK TABLES `raptor_list_radioisotope` WRITE;
/*!40000 ALTER TABLE `raptor_list_radioisotope` DISABLE KEYS */;
INSERT INTO `raptor_list_radioisotope` VALUES ('ENTERIC','barium',1,1,1,1,1),('ENTERIC','Fluorescein',1,1,1,1,1),('ENTERIC','indium-111 DTPA',1,1,1,1,1),('ENTERIC','Other',1,1,1,1,1),('IV','Other',1,1,1,1,1),('IV','Diatrizoate',1,1,1,1,1),('IV','Tc99m-DTPA',1,1,1,1,1),('IV','Tc99m-MAG3',1,1,1,1,1),('IV','Tc99m-sestamibi',1,1,1,1,1),('IV','Technetium-99m',1,1,1,1,1);
/*!40000 ALTER TABLE `raptor_list_radioisotope` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_sedation`
--

DROP TABLE IF EXISTS `raptor_list_sedation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_sedation` (
  `type_nm` varchar(8) NOT NULL COMMENT 'Oral/IV/Enteric',
  `option_tx` varchar(100) NOT NULL COMMENT 'The text to show',
  `ct_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to CT?',
  `mr_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to MRI?',
  `nm_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Nuclear Medicine?',
  `fl_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Fluoroscopy?',
  `us_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Applies to Ultrasound?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Options for sedation panel';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_sedation`
--

LOCK TABLES `raptor_list_sedation` WRITE;
/*!40000 ALTER TABLE `raptor_list_sedation` DISABLE KEYS */;
INSERT INTO `raptor_list_sedation` VALUES ('IV','Conscious sedation',1,1,1,1,1),('IV','Other (See protocol notes)',1,1,1,1,1),('ORAL','Adavant',1,1,1,1,1),('ORAL','Midazolam',1,1,1,1,1),('ORAL','Valium 10mg PO 20 min before scan',1,1,1,1,1),('ORAL','Other (See protocol notes)',1,1,1,1,1);
/*!40000 ALTER TABLE `raptor_list_sedation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_list_service`
--

DROP TABLE IF EXISTS `raptor_list_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_list_service` (
  `service_nm` varchar(10) NOT NULL COMMENT 'Service name',
  `service_desc` varchar(100) NOT NULL COMMENT 'Service description',
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this record was last updated',
  PRIMARY KEY (`service_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Services in which users specialize';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_list_service`
--

LOCK TABLES `raptor_list_service` WRITE;
/*!40000 ALTER TABLE `raptor_list_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_list_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_loinc_codes`
--

DROP TABLE IF EXISTS `raptor_loinc_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_loinc_codes` (
  `loinc_cd` varchar(10) NOT NULL COMMENT 'The LOINC code',
  `exclude_from_worklist_yn` int(10) unsigned NOT NULL COMMENT 'If 1 then orders of this type are ignored by RAPTOR',
  `contrast_yn` int(10) unsigned DEFAULT NULL COMMENT 'If 1, then contrast, else no contrast.',
  `modality_abbr` varchar(2) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the modality associated with this order',
  `service_nm` varchar(10) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the service associated with this order',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relevant information about LOINC codes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_loinc_codes`
--

LOCK TABLES `raptor_loinc_codes` WRITE;
/*!40000 ALTER TABLE `raptor_loinc_codes` DISABLE KEYS */;
INSERT INTO `raptor_loinc_codes` VALUES ('74000',1,NULL,'','','2015-10-02 14:52:16'),('74010',1,NULL,'','','2015-10-02 14:52:16'),('73050',1,NULL,'','','2015-10-02 14:52:16'),('75710',0,NULL,'CT','','2015-10-02 14:52:16'),('73600',1,NULL,'','','2015-10-02 14:52:16'),('73610',1,NULL,'','','2015-10-02 14:52:16'),('73650',1,NULL,'','','2015-10-02 14:52:16'),('71020',1,NULL,'','','2015-10-02 14:52:16'),('71035',1,NULL,'','','2015-10-02 14:52:16'),('71022',1,NULL,'','','2015-10-02 14:52:16'),('75716',0,NULL,'CT','','2015-10-02 14:52:16'),('74300',0,NULL,'CT','','2015-10-02 14:52:16'),('74290',0,NULL,'CT','','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_loinc_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_patient_radiation_dose`
--

DROP TABLE IF EXISTS `raptor_patient_radiation_dose`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_patient_radiation_dose` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `patientid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the patient',
  `dose` float unsigned NOT NULL COMMENT 'Amount of exposure',
  `uom` varchar(32) NOT NULL COMMENT 'Unit of measure for the exposure',
  `dose_type_cd` char(1) NOT NULL COMMENT 'E=Estimated,A=Actual,U=Unknown Quality',
  `dose_source_cd` char(1) NOT NULL COMMENT 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
  `dose_target_area_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Target area lookup of codes',
  `dose_dt` datetime NOT NULL COMMENT 'When this dose was received',
  `data_provider` varchar(32) DEFAULT '' COMMENT 'Data provider for this record',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that associated with entry of this value',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`patientid`,`dose_source_cd`,`dose_dt`,`dose_target_area_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Radiation exposure for a patient';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_patient_radiation_dose`
--

LOCK TABLES `raptor_patient_radiation_dose` WRITE;
/*!40000 ALTER TABLE `raptor_patient_radiation_dose` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_patient_radiation_dose` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_patient_radiation_dose_replaced`
--

DROP TABLE IF EXISTS `raptor_patient_radiation_dose_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_patient_radiation_dose_replaced` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `patientid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the patient',
  `dose` float unsigned NOT NULL COMMENT 'Amount of exposure',
  `uom` varchar(32) NOT NULL COMMENT 'Unit of measure for the exposure',
  `dose_type_cd` char(1) NOT NULL COMMENT 'E=Estimated,A=Actual,U=Unknown Quality',
  `dose_source_cd` char(1) NOT NULL COMMENT 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
  `dose_target_area_id` int(10) unsigned DEFAULT NULL COMMENT 'Target area lookup of codes',
  `dose_dt` datetime NOT NULL COMMENT 'When this dose was received',
  `data_provider` varchar(32) DEFAULT '' COMMENT 'Data provider for this record',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that associated with entry of this value',
  `original_created_dt` datetime NOT NULL COMMENT 'The creation date of the replaced record',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replaced radiation exposure for a patient';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_patient_radiation_dose_replaced`
--

LOCK TABLES `raptor_patient_radiation_dose_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_patient_radiation_dose_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_patient_radiation_dose_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_code_maps`
--

DROP TABLE IF EXISTS `raptor_protocol_code_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_code_maps` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Protocol short name key value',
  `cprs_cd` varchar(6) DEFAULT NULL COMMENT 'The CPRS code',
  `radlex_cd` varchar(12) DEFAULT NULL COMMENT 'The RADLEX code',
  `cpt_cd` varchar(5) DEFAULT NULL COMMENT 'The CPT code',
  `snomed_cd` varchar(40) DEFAULT NULL COMMENT 'The SNOMED code',
  `icd_cd` varchar(10) DEFAULT NULL COMMENT 'The ICD code',
  `loinc_cd` varchar(10) DEFAULT NULL COMMENT 'The LOINC code',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Map codes to the protocols in the library';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_code_maps`
--

LOCK TABLES `raptor_protocol_code_maps` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_code_maps` DISABLE KEYS */;
INSERT INTO `raptor_protocol_code_maps` VALUES ('RPID144',NULL,'RPID144',NULL,NULL,NULL,NULL,'2015-04-05 13:00:00'),('RPID145',NULL,'RPID145',NULL,NULL,NULL,NULL,'2015-07-27 16:02:00'),('RPID159',NULL,'RPID159',NULL,NULL,NULL,NULL,'2015-07-27 16:06:00'),('RPID16',NULL,'RPID16',NULL,NULL,NULL,NULL,'2014-11-08 17:35:00'),('RPID160',NULL,'RPID160',NULL,NULL,NULL,NULL,'2014-11-08 17:36:00'),('RPID18',NULL,'RPID18',NULL,NULL,NULL,NULL,'2014-11-08 18:16:00'),('RPID21',NULL,'RPID21',NULL,NULL,NULL,NULL,'2014-11-08 18:17:00'),('RPID22',NULL,'RPID22',NULL,NULL,NULL,NULL,'2014-11-08 18:18:00'),('RPID24',NULL,'RPID24',NULL,NULL,NULL,NULL,'2015-04-06 03:15:00'),('RPID24',NULL,NULL,'70460',NULL,NULL,NULL,'2015-04-06 03:15:00'),('RPID24',NULL,NULL,'70470',NULL,NULL,NULL,'2015-04-06 03:15:00'),('RPID31',NULL,'RPID31',NULL,NULL,NULL,NULL,'2015-07-27 16:05:00'),('RPID33',NULL,'RPID33',NULL,NULL,NULL,NULL,'2014-11-08 18:26:00'),('RPID66',NULL,NULL,NULL,NULL,'A',NULL,'2014-11-01 23:01:00'),('RPID66',NULL,NULL,NULL,NULL,'C',NULL,'2014-11-01 23:01:00'),('RPID66',NULL,NULL,NULL,NULL,'B',NULL,'2014-11-01 23:01:00'),('RPID66',NULL,'RPID123',NULL,NULL,NULL,NULL,'2014-11-01 23:01:00'),('RPID66',NULL,NULL,'55555',NULL,NULL,NULL,'2014-11-01 23:01:00');
/*!40000 ALTER TABLE `raptor_protocol_code_maps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_keywords`
--

DROP TABLE IF EXISTS `raptor_protocol_keywords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_keywords` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Must match the protocol shortname in protocol_lib table',
  `weightgroup` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Group 1 has the most weight',
  `keyword` varchar(32) NOT NULL DEFAULT '' COMMENT 'Specialization keyword',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`protocol_shortname`,`weightgroup`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anatomy specializations of a protocol';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_keywords`
--

LOCK TABLES `raptor_protocol_keywords` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_keywords` DISABLE KEYS */;
INSERT INTO `raptor_protocol_keywords` VALUES ('FL-EXAMPLE',1,'BODY','2015-07-27 16:16:00'),('FL-EXAMPLE',2,'','2015-07-27 16:16:00'),('FL-EXAMPLE',3,'','2015-07-27 16:16:00'),('NM-EXAMPLE',1,' THORAX','2015-03-09 07:33:00'),('NM-EXAMPLE',1,'CHEST','2015-03-09 07:33:00'),('NM-EXAMPLE',2,'','2015-03-09 07:33:00'),('NM-EXAMPLE',3,'','2015-03-09 07:33:00'),('RPID144',1,'ABDOMEN','2015-04-05 13:00:00'),('RPID144',1,'PELVIS','2015-04-05 13:00:00'),('RPID144',2,'','2015-04-05 13:00:00'),('RPID144',3,'','2015-04-05 13:00:00'),('RPID145',1,'ABDOMEN','2015-07-27 16:02:00'),('RPID145',1,'PELVIS','2015-07-27 16:02:00'),('RPID145',2,'','2015-07-27 16:02:00'),('RPID145',3,'','2015-07-27 16:02:00'),('RPID159',1,'HEAD','2015-07-27 16:06:00'),('RPID159',1,'NEURAL','2015-07-27 16:06:00'),('RPID159',2,'','2015-07-27 16:06:00'),('RPID159',3,'','2015-07-27 16:06:00'),('RPID16',1,' THORAX','2014-11-08 17:35:00'),('RPID16',1,'CHEST','2014-11-08 17:35:00'),('RPID16',2,'','2014-11-08 17:35:00'),('RPID16',3,'','2014-11-08 17:35:00'),('RPID160',1,'HEAD','2014-11-08 17:36:00'),('RPID160',1,'NEURAL','2014-11-08 17:36:00'),('RPID160',2,'','2014-11-08 17:36:00'),('RPID160',3,'','2014-11-08 17:36:00'),('RPID18',1,' CHEST','2014-11-08 18:16:00'),('RPID18',1,'THORAX','2014-11-08 18:16:00'),('RPID18',2,'','2014-11-08 18:16:00'),('RPID18',3,'','2014-11-08 18:16:00'),('RPID21',1,'CERVICAL','2014-11-08 18:17:00'),('RPID21',1,'SPINAL','2014-11-08 18:17:00'),('RPID21',2,'','2014-11-08 18:17:00'),('RPID21',3,'','2014-11-08 18:17:00'),('RPID22',1,'HEAD','2014-11-08 18:18:00'),('RPID22',1,'NEURAL','2014-11-08 18:18:00'),('RPID22',2,'','2014-11-08 18:18:00'),('RPID22',3,'','2014-11-08 18:18:00'),('RPID23',1,'HEAD','2015-07-27 16:04:00'),('RPID23',1,'NEURAL','2015-07-27 16:04:00'),('RPID23',2,'','2015-07-27 16:04:00'),('RPID23',3,'','2015-07-27 16:04:00'),('RPID24',1,'HEAD','2015-04-06 03:15:00'),('RPID24',1,'NEURAL','2015-04-06 03:15:00'),('RPID24',2,'','2015-04-06 03:15:00'),('RPID24',3,'','2015-04-06 03:15:00'),('RPID249',1,' PELVIS','2014-11-01 22:41:00'),('RPID249',1,'ABDOMEN ','2014-11-01 22:41:00'),('RPID249',2,'','2014-11-01 22:41:00'),('RPID249',3,'','2014-11-01 22:41:00'),('RPID31',1,' SPINE','2015-07-27 16:05:00'),('RPID31',1,'LUMBAR','2015-07-27 16:05:00'),('RPID31',2,'','2015-07-27 16:05:00'),('RPID31',3,'','2015-07-27 16:05:00'),('RPID33',1,' LUMBAR','2014-11-08 18:26:00'),('RPID33',1,'NEURAL','2014-11-08 18:26:00'),('RPID33',1,'SPINAL','2014-11-08 18:26:00'),('RPID33',2,'','2014-11-08 18:26:00'),('RPID33',3,'','2014-11-08 18:26:00'),('RPID39',1,'NECK','2015-05-22 10:17:00'),('RPID39',2,'','2015-05-22 10:17:00'),('RPID39',3,'','2015-05-22 10:17:00'),('RPID42',1,'HEAD','2015-04-07 19:57:00'),('RPID42',1,'ORBITAL','2015-04-07 19:57:00'),('RPID42',2,'','2015-04-07 19:57:00'),('RPID42',3,'','2015-04-07 19:57:00'),('RPID66',1,'NECK','2014-11-01 23:01:00'),('RPID66',2,'','2014-11-01 23:01:00'),('RPID66',3,'','2014-11-01 23:01:00'),('RPID96',1,'HEAD','2014-11-01 22:08:00'),('RPID96',1,'NEURAL','2014-11-01 22:08:00'),('RPID96',2,'','2014-11-01 22:08:00'),('RPID96',3,'','2014-11-01 22:08:00'),('US-EXAMPLE',1,'BODY','2015-06-08 18:22:00'),('US-EXAMPLE',2,'','2015-06-08 18:22:00'),('US-EXAMPLE',3,'','2015-06-08 18:22:00'),('WAV004',1,' THORAX','2014-07-13 15:19:00'),('WAV004',1,'CHEST','2014-07-13 15:19:00'),('WAV004',2,'','2014-07-13 15:19:00'),('WAV004',3,'','2014-07-13 15:19:00'),('WAV007',1,'ABDOMEN','2014-08-30 16:50:00'),('WAV007',1,'CHEST','2014-08-30 16:50:00'),('WAV007',1,'PELVIS','2014-08-30 16:50:00'),('WAV007',2,'','2014-08-30 16:50:00'),('WAV007',3,'','2014-08-30 16:50:00'),('WAV008',1,' PELVIS','2014-07-13 15:20:00'),('WAV008',1,'ABDOMEN ','2014-07-13 15:20:00'),('WAV008',2,'','2014-07-13 15:20:00'),('WAV008',3,'','2014-07-13 15:20:00'),('WAV010',1,'BODY','2014-08-30 16:58:00'),('WAV010',1,'LIVER','2014-08-30 16:58:00'),('WAV010',2,'','2014-08-30 16:58:00'),('WAV010',3,'','2014-08-30 16:58:00'),('WAV011',1,'BODY','2014-07-13 15:19:00'),('WAV011',1,'LIVER','2014-07-13 15:19:00'),('WAV012',1,'BODY','2014-07-13 15:19:00'),('WAV012',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV013',1,'BODY','2015-07-27 16:04:00'),('WAV013',1,'GENITOURINARY','2015-07-27 16:04:00'),('WAV013',2,'','2015-07-27 16:04:00'),('WAV013',3,'','2015-07-27 16:04:00'),('WAV014',1,'BODY','2014-07-13 15:19:00'),('WAV014',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV015',1,'BODY','2015-07-27 16:03:00'),('WAV015',1,'GENITOURINARY','2015-07-27 16:03:00'),('WAV015',2,'','2015-07-27 16:03:00'),('WAV015',3,'','2015-07-27 16:03:00'),('WAV016',1,'BODY','2014-07-13 15:19:00'),('WAV016',1,'PANCREAS','2014-07-13 15:19:00'),('WAV017',1,'CHEST','2014-07-13 15:19:00'),('WAV017',1,'THORACIC','2014-07-13 15:19:00'),('WAV017',1,'VASCULAR','2014-07-13 15:19:00'),('WAV018',1,'CHEST','2014-07-13 15:19:00'),('WAV018',1,'THORACIC','2014-07-13 15:19:00'),('WAV018',1,'VASCULAR','2014-07-13 15:19:00'),('WAV019',1,'BODY','2014-07-13 15:19:00'),('WAV019',1,'VASCULAR','2014-07-13 15:19:00'),('WAV020',1,'BODY','2014-07-13 15:19:00'),('WAV020',1,'VASCULAR','2014-07-13 15:19:00'),('WAV021',1,'BODY','2014-07-13 15:19:00'),('WAV021',1,'VASCULAR','2014-07-13 15:19:00'),('WAV022',1,'CERVICAL','2014-08-30 16:54:00'),('WAV022',1,'SPINAL','2014-08-30 16:54:00'),('WAV022',2,'','2014-08-30 16:54:00'),('WAV022',3,'','2014-08-30 16:54:00'),('WAV023',1,'CERVICAL','2014-08-30 16:54:00'),('WAV023',1,'SPINAL','2014-08-30 16:54:00'),('WAV023',2,'','2014-08-30 16:54:00'),('WAV023',3,'','2014-08-30 16:54:00'),('WAV025',1,'CHEST','2014-07-13 15:19:00'),('WAV025',1,'LUNG','2014-07-13 15:19:00'),('WAV025',1,'PULMONARY','2014-07-13 15:19:00'),('WAV026',1,'FACE','2014-08-30 16:55:00'),('WAV026',1,'HEAD','2014-08-30 16:55:00'),('WAV026',2,'','2014-08-30 16:55:00'),('WAV026',3,'','2014-08-30 16:55:00'),('WAV033',1,'HEAD','2015-06-08 15:51:00'),('WAV033',1,'NEURAL','2015-06-08 15:51:00'),('WAV033',2,'','2015-06-08 15:51:00'),('WAV033',3,'','2015-06-08 15:51:00'),('WAV034',1,'HEAD','2014-07-13 15:19:00'),('WAV034',1,'NEURAL','2014-07-13 15:19:00'),('WAV035',1,'HEAD','2014-07-13 15:19:00'),('WAV036',1,'HEAD','2014-08-30 16:55:00'),('WAV036',2,'','2014-08-30 16:55:00'),('WAV036',3,'','2014-08-30 16:55:00'),('WAV037',1,'LARYNX','2014-07-13 15:19:00'),('WAV037',1,'NECK','2014-07-13 15:19:00'),('WAV037',1,'THROAT','2014-07-13 15:19:00'),('WAV038',1,'NEURAL','2014-08-30 16:55:00'),('WAV038',1,'SPINAL','2014-08-30 16:55:00'),('WAV038',2,'','2014-08-30 16:55:00'),('WAV038',3,'','2014-08-30 16:55:00'),('WAV042',1,'NECK','2014-11-08 18:25:00'),('WAV042',2,'HEAD','2014-11-08 18:25:00'),('WAV042',3,'','2014-11-08 18:25:00'),('WAV044',1,'CAVITY','2014-07-13 15:19:00'),('WAV044',1,'LARYNX','2014-07-13 15:19:00'),('WAV044',1,'NECK','2014-07-13 15:19:00'),('WAV044',1,'ORAL','2014-07-13 15:19:00'),('WAV044',1,'THROAT','2014-07-13 15:19:00'),('WAV044',1,'THYROID','2014-07-13 15:19:00'),('WAV045',1,'CERVICAL','2014-08-30 16:55:00'),('WAV045',1,'VERTIBRAE','2014-08-30 16:55:00'),('WAV045',2,'','2014-08-30 16:55:00'),('WAV045',3,'','2014-08-30 16:55:00'),('WAV046',1,'HEAD','2014-07-13 15:19:00'),('WAV046',1,'ORBITAL','2014-07-13 15:19:00'),('WAV047',1,'HEAD','2014-07-13 15:19:00'),('WAV047',1,'ORBITAL','2014-07-13 15:19:00'),('WAV049',1,'HEAD','2014-07-13 15:19:00'),('WAV049',1,'NEURAL','2014-07-13 15:19:00'),('WAV049',1,'PITUITARY','2014-07-13 15:19:00'),('WAV050',1,'FACE','2014-07-13 15:19:00'),('WAV050',1,'HEAD','2014-07-13 15:19:00'),('WAV050',1,'SINUS','2014-07-13 15:19:00'),('WAV051',1,'FACE','2014-07-13 15:19:00'),('WAV051',1,'HEAD','2014-07-13 15:19:00'),('WAV051',1,'SINUS','2014-07-13 15:19:00'),('WAV052',1,'FACE','2014-07-13 15:19:00'),('WAV052',1,'HEAD','2014-07-13 15:19:00'),('WAV052',1,'SINUS','2014-07-13 15:19:00'),('WAV053',1,'HEAD','2014-08-30 16:56:00'),('WAV053',1,'NEURAL','2014-08-30 16:56:00'),('WAV053',1,'TEMPORAL','2014-08-30 16:56:00'),('WAV053',2,'','2014-08-30 16:56:00'),('WAV053',3,'','2014-08-30 16:56:00'),('WAV054',1,'HEAD','2014-08-30 16:56:00'),('WAV054',1,'NEURAL','2014-08-30 16:56:00'),('WAV054',1,'TEMPORAL','2014-08-30 16:56:00'),('WAV054',2,'','2014-08-30 16:56:00'),('WAV054',3,'','2014-08-30 16:56:00'),('WAV055',1,'KNEE','2014-07-13 15:22:00'),('WAV055',1,'MUSCULOSKELETAL','2014-07-13 15:22:00'),('WAV055',2,'BODY','2014-07-13 15:22:00'),('WAV055',3,'','2014-07-13 15:22:00'),('WAV056',1,'BODY','2014-07-13 15:19:00'),('WAV056',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV056',1,'SHOULDER','2014-07-13 15:19:00'),('WAV057',1,'BODY','2014-07-13 15:19:00'),('WAV057',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV057',1,'SHOULDER','2014-07-13 15:19:00'),('WAV058',1,'ANKLE','2014-07-13 15:19:00'),('WAV058',1,'BODY','2014-07-13 15:19:00'),('WAV058',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV059',1,'BODY','2014-07-13 15:19:00'),('WAV059',1,'HIP','2014-07-13 15:19:00'),('WAV059',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV060',1,'BODY','2014-07-13 15:19:00'),('WAV060',1,'HIP','2014-07-13 15:19:00'),('WAV060',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV061',1,'BODY','2015-07-27 16:04:00'),('WAV061',1,'MUSCULOSKELETAL','2015-07-27 16:04:00'),('WAV061',1,'WRIST','2015-07-27 16:04:00'),('WAV061',2,'','2015-07-27 16:04:00'),('WAV061',3,'','2015-07-27 16:04:00'),('WAV062',1,'BODY','2014-07-13 15:19:00'),('WAV062',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV062',1,'WRIST','2014-07-13 15:19:00'),('WAV063',1,'BODY','2014-07-13 15:19:00'),('WAV063',1,'ELBOW','2014-07-13 15:19:00'),('WAV063',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV064',1,'BODY','2014-07-13 15:19:00'),('WAV064',1,'VASCULAR','2014-07-13 15:19:00'),('WAV065',1,'BODY','2014-07-13 15:19:00'),('WAV065',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV065',1,'VASCULAR','2014-07-13 15:19:00'),('WAV066',1,'AORTOGRAM','2014-07-13 15:19:00'),('WAV066',1,'CHEST','2014-07-13 15:19:00'),('WAV066',1,'THORACIC','2014-07-13 15:19:00'),('WAV067',1,'BODY','2014-07-13 15:19:00'),('WAV067',1,'LIVER','2014-07-13 15:19:00'),('WAV068',1,'BODY','2014-07-13 15:19:00'),('WAV068',1,'LIVER','2014-07-13 15:19:00'),('WAV069',1,'BODY','2014-07-13 15:19:00'),('WAV069',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV069',1,'RENAL','2014-07-13 15:19:00'),('WAV070',1,'ADRENAL','2014-07-13 15:19:00'),('WAV070',1,'BODY','2014-07-13 15:19:00'),('WAV070',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV071',1,'BODY','2014-07-13 15:19:00'),('WAV071',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV071',1,'UROGRAM','2014-07-13 15:19:00'),('WAV072',1,'BODY','2014-07-13 15:19:00'),('WAV072',1,'GENITOURINARY','2014-07-13 15:19:00'),('WAV072',1,'GYNECOLOGIC','2014-07-13 15:19:00'),('WAV073',1,'BODY','2014-07-13 15:19:00'),('WAV073',1,'PELVIC','2014-07-13 15:19:00'),('WAV073',1,'VASCULAR','2014-07-13 15:19:00'),('WAV074',1,'ANKLE','2014-07-13 15:19:00'),('WAV074',1,'BODY','2014-07-13 15:19:00'),('WAV074',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV075',1,'ANKLE','2014-07-13 15:19:00'),('WAV075',1,'BODY','2014-07-13 15:19:00'),('WAV075',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV076',1,'BODY','2014-07-13 15:19:00'),('WAV076',1,'HIP','2014-07-13 15:19:00'),('WAV076',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV077',1,'BODY','2014-07-13 15:19:00'),('WAV077',1,'ELBOW','2014-07-13 15:19:00'),('WAV077',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV078',1,'BODY','2014-07-13 15:19:00'),('WAV078',1,'ELBOW','2014-07-13 15:19:00'),('WAV078',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV079',1,'BODY','2014-07-13 15:19:00'),('WAV079',1,'FOOT','2014-07-13 15:19:00'),('WAV079',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV080',1,'BODY','2014-07-13 15:19:00'),('WAV080',1,'FOOT','2014-07-13 15:19:00'),('WAV080',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV081',1,'BODY','2014-07-13 15:19:00'),('WAV081',1,'FOOT','2014-07-13 15:19:00'),('WAV081',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV082',1,'BODY','2014-07-13 15:19:00'),('WAV082',1,'FOOT','2014-07-13 15:19:00'),('WAV082',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV083',1,'BODY','2014-07-13 15:19:00'),('WAV083',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV083',1,'PELVIS','2014-07-13 15:19:00'),('WAV084',1,'BODY','2014-07-13 15:19:00'),('WAV084',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV084',1,'THIGHS','2014-07-13 15:19:00'),('WAV085',1,'BODY','2014-07-13 15:19:00'),('WAV085',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV086',1,'BODY','2014-07-13 15:19:00'),('WAV086',1,'MUSCULOSKELETAL','2014-07-13 15:19:00'),('WAV087',1,'HEAD','2014-07-13 15:19:00'),('WAV088',1,'CERVICAL','2014-07-13 15:19:00'),('WAV088',1,'NEURAL','2014-07-13 15:19:00'),('WAV089',1,'BODY','2014-07-13 15:19:00'),('WAV089',1,'ELBOW','2014-07-13 15:19:00'),('WAV090',1,'HEAD','2014-07-13 15:19:00'),('WAV091',1,'HEAD','2014-07-13 15:19:00'),('WAV092',1,'HEAD','2014-07-13 15:19:00'),('WAV092',1,'NECK','2014-07-13 15:19:00'),('WAV093',1,'HEAD','2014-07-13 15:19:00'),('WAV094',1,'BODY','2014-07-13 15:19:00'),('WAV094',1,'KNEE','2014-07-13 15:19:00'),('WAV095',1,'HEAD','2014-07-13 15:19:00'),('WAV095',1,'PITUITARY','2014-07-13 15:19:00'),('WAV096',1,'NEURAL','2014-07-13 15:19:00'),('WAV096',1,'SPINAL','2014-07-13 15:19:00'),('WAV097',1,'NEURAL','2014-07-13 15:19:00'),('WAV097',1,'SPINAL','2014-07-13 15:19:00'),('WAV098',1,'NEOPLASM','2014-07-13 15:19:00'),('WAV098',1,'SINUS','2014-07-13 15:19:00'),('WAV099',1,'GYNECOLOGIC','2014-07-13 15:19:00'),('WAV099',1,'REPRODUCTIVE','2014-07-13 15:19:00'),('WAV100',1,'HEAD','2014-07-13 15:19:00'),('WAV100',1,'PITUITARY','2014-07-13 15:19:00'),('WAV101',1,'HEAD','2014-07-13 15:19:00'),('WAV101',1,'PITUITARY','2014-07-13 15:19:00'),('WAV102',1,'NEURAL','2014-07-13 15:19:00'),('WAV102',1,'SPINAL','2014-07-13 15:19:00'),('WAV103',1,'NEURAL','2014-07-13 15:19:00'),('WAV103',1,'SPINAL','2014-07-13 15:19:00'),('WAV104',1,'NEURAL','2014-07-13 15:19:00'),('WAV104',1,'SPINAL','2014-07-13 15:19:00'),('WAV105',1,'NEURAL','2014-07-13 15:19:00'),('WAV105',1,'SPINAL','2014-07-13 15:19:00'),('WAV106',1,'NEURAL','2014-07-13 15:19:00'),('WAV106',1,'SPINAL','2014-07-13 15:19:00'),('WAV107',1,'NEURAL','2014-07-13 15:19:00'),('WAV108',1,'NEURAL','2014-07-13 15:19:00'),('WAV108',1,'WRIST','2014-07-13 15:19:00'),('WAV109',1,'NEURAL','2014-07-13 15:19:00'),('WAV109',1,'SPINAL','2014-07-13 15:19:00'),('WAV109',1,'THORAX','2014-07-13 15:19:00'),('WAV110',1,'HEAD','2014-07-13 15:19:00'),('WAV110',1,'NEURAL','2014-07-13 15:19:00'),('WAV111',1,'BODY','2014-07-25 14:41:00'),('WAV111',1,'MUSCULOSKELETAL','2014-07-25 14:41:00'),('WAV111',2,'','2014-07-25 14:41:00'),('WAV111',3,'','2014-07-25 14:41:00'),('WAV112',1,'BODY','2014-07-25 14:42:00'),('WAV112',1,'MUSCULOSKELETAL','2014-07-25 14:42:00'),('WAV112',2,'','2014-07-25 14:42:00'),('WAV112',3,'','2014-07-25 14:42:00'),('WAV113',1,'BODY','2014-07-25 14:42:00'),('WAV113',1,'HEAD','2014-07-25 14:42:00'),('WAV113',1,'NEURAL','2014-07-25 14:42:00'),('WAV113',2,'','2014-07-25 14:42:00'),('WAV113',3,'','2014-07-25 14:42:00'),('WAV114',1,'BODY','2014-08-30 16:57:00'),('WAV114',1,'MUSCULOSKELETAL','2014-08-30 16:57:00'),('WAV114',2,'','2014-08-30 16:57:00'),('WAV114',3,'','2014-08-30 16:57:00'),('WAV115',1,'BODY','2014-08-30 16:58:00'),('WAV115',1,'NEURAL','2014-08-30 16:58:00'),('WAV115',1,'SPINAL','2014-08-30 16:58:00'),('WAV115',2,'','2014-08-30 16:58:00'),('WAV115',3,'','2014-08-30 16:58:00'),('WAV116',1,'BODY','2014-07-13 15:19:00'),('WAV116',1,'LACRIMAL','2014-07-13 15:19:00'),('WAV116',1,'ORBITAL','2014-07-13 15:19:00'),('WAV117',1,'BODY','2014-07-13 15:19:00'),('WAV117',1,'NEOPLASM','2014-07-13 15:19:00'),('WAV118',1,'GASTROINTESTINAL','2014-07-13 15:19:00'),('WAV119',1,'BODY','2014-07-13 15:19:00'),('WAV119',1,'GASTROINTESTINAL','2014-07-13 15:19:00'),('WAV119',1,'HEMATOLOGICAL','2014-07-13 15:19:00'),('WAV120',1,'BILIARY','2014-07-13 15:19:00'),('WAV120',1,'BODY','2014-07-13 15:19:00'),('WAV120',1,'GALLBLADDER','2014-07-13 15:19:00'),('WAV120',1,'LIVER','2014-07-13 15:19:00'),('WAV121',1,'BODY','2014-07-13 15:19:00'),('WAV121',1,'HEMATOLOGICAL','2014-07-13 15:19:00'),('WAV121',1,'LIVER','2014-07-13 15:19:00'),('WAV122',1,'BODY','2014-07-13 15:19:00'),('WAV122',1,'LIVER','2014-07-13 15:19:00'),('WAV122',1,'SPLEEN','2014-07-13 15:19:00'),('WAV123',1,'BODY','2014-07-13 15:19:00'),('WAV123',1,'LUNG','2014-07-13 15:19:00'),('WAV124',1,'BODY','2014-07-13 15:19:00'),('WAV124',1,'LUNG','2014-07-13 15:19:00'),('WAV125',1,'BODY','2014-07-13 15:19:00'),('WAV125',1,'LYMPHATIC','2014-07-13 15:19:00'),('WAV126',1,'BODY','2014-07-13 15:19:00'),('WAV127',1,'BODY','2014-07-13 15:19:00'),('WAV127',1,'NEURAL','2014-07-13 15:19:00'),('WAV127',1,'SPINAL','2014-07-13 15:19:00'),('WAV128',1,'BODY','2014-07-13 15:19:00'),('WAV128',1,'CARDIAC','2014-07-13 15:19:00'),('WAV129',1,'BODY','2014-07-13 15:19:00'),('WAV129',1,'CARDIAC','2014-07-13 15:19:00'),('WAV130',1,'BODY','2014-07-13 15:19:00'),('WAV130',1,'CARDIAC','2014-07-13 15:19:00'),('WAV131',1,'BODY','2014-07-13 15:19:00'),('WAV131',1,'CARDIAC','2014-07-13 15:19:00'),('WAV132',1,'BODY','2014-07-13 15:19:00'),('WAV132',1,'CARDIAC','2014-07-13 15:19:00'),('WAV133',1,'BODY','2014-07-13 15:19:00'),('WAV133',1,'CARDIAC','2014-07-13 15:19:00'),('WAV134',1,'BODY','2014-07-13 15:19:00'),('WAV134',1,'CARDIAC','2014-07-13 15:19:00'),('WAV135',1,'BODY','2014-07-13 15:19:00'),('WAV135',1,'CARDIAC','2014-07-13 15:19:00'),('WAV136',1,'BODY','2014-07-13 15:19:00'),('WAV136',1,'CARDIAC','2014-07-13 15:19:00'),('WAV137',1,'BODY','2014-07-13 15:19:00'),('WAV137',1,'NEOPLASM','2014-07-13 15:19:00'),('WAV138',1,'BODY','2014-07-13 15:19:00'),('WAV138',1,'PARATHYROID','2014-07-13 15:19:00'),('WAV139',1,'BODY','2014-07-13 15:19:00'),('WAV139',1,'HEMATOLOGY','2014-07-13 15:19:00'),('WAV139',1,'PLATELET','2014-07-13 15:19:00'),('WAV140',1,'BODY','2014-07-13 15:19:00'),('WAV140',1,'RENAL','2014-07-13 15:19:00'),('WAV141',1,'BODY','2014-07-13 15:19:00'),('WAV141',1,'RENAL','2014-07-13 15:19:00'),('WAV144',1,'BODY','2014-07-13 15:19:00'),('WAV144',1,'ENDOCRINE','2014-07-13 15:19:00'),('WAV144',1,'THYROID','2014-07-13 15:19:00'),('WAV146',1,'BODY','2014-07-13 15:19:00'),('WAV146',1,'ENDOCRINE','2014-07-13 15:19:00'),('WAV146',1,'THYROID','2014-07-13 15:19:00'),('WAV147',1,'BODY','2014-07-13 15:19:00'),('WAV147',1,'ENDOCRINE','2014-07-13 15:19:00'),('WAV147',1,'THYROID','2014-07-13 15:19:00'),('WAV148',1,'BODY','2014-07-13 15:19:00'),('WAV148',1,'ENDOCRINE','2014-07-13 15:19:00'),('WAV148',1,'THYROID','2014-07-13 15:19:00'),('WAV149',1,'BODY','2014-07-13 15:19:00'),('WAV149',1,'HEMATOLOGY','2014-07-13 15:19:00'),('WAV150',1,'BODY','2014-07-13 15:19:00'),('WAV150',1,'HEMATOLOGY','2014-07-13 15:19:00');
/*!40000 ALTER TABLE `raptor_protocol_keywords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_keywords_replaced`
--

DROP TABLE IF EXISTS `raptor_protocol_keywords_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_keywords_replaced` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Must match the protocol shortname in protocol_lib table',
  `weightgroup` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Group 1 has the most weight',
  `keyword` varchar(32) NOT NULL DEFAULT '' COMMENT 'Specialization keyword',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was replaced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anatomy specializations of a protocol replaced records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_keywords_replaced`
--

LOCK TABLES `raptor_protocol_keywords_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_keywords_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_protocol_keywords_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_lib`
--

DROP TABLE IF EXISTS `raptor_protocol_lib`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_lib` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Protocol short name which must be unique',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT 'Protocol name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this protocol if there have been replacements',
  `modality_abbr` varchar(2) NOT NULL DEFAULT '' COMMENT 'Modality abbreviation',
  `service_nm` varchar(10) NOT NULL DEFAULT '' COMMENT 'Service name',
  `lowerbound_weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Lower bound weight for this protocol',
  `upperbound_weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Upper bound weight for this protocol',
  `image_guided_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is an image guided protocol',
  `contrast_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is a contrast protocol',
  `radioisotope_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is a radioisotope protocol',
  `sedation_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is a sedation protocol',
  `multievent_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this protocol can be associated with multiple schedule events',
  `filename` varchar(128) NOT NULL DEFAULT '' COMMENT 'Filename in RAPTOR of the scanned protocol',
  `original_filename` varchar(128) DEFAULT '' COMMENT 'Original filename of uploaded protocol',
  `original_file_upload_dt` datetime DEFAULT NULL COMMENT 'When this file was last uploaded',
  `original_file_upload_by_uid` int(10) unsigned DEFAULT NULL COMMENT 'Who uploaded the file',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this protocol is still active for use',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`protocol_shortname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Protocol Library item key attributes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_lib`
--

LOCK TABLES `raptor_protocol_lib` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_lib` DISABLE KEYS */;
INSERT INTO `raptor_protocol_lib` VALUES ('FL-EXAMPLE','FL Example Placeholder Fluoroscopy Protocol',2,'FL','',0,0,0,0,0,0,0,'no-filename','',NULL,NULL,0,'2015-07-27 16:16:00'),('NM-EXAMPLE','NM Example Placeholder NM Protocol',4,'NM','',0,0,0,0,0,0,0,'no-filename','',NULL,NULL,1,'2015-03-09 07:33:00'),('RPID144','CT Abdomen and pelvis with no oral or IV contrast',2,'CT','',0,0,0,0,0,0,0,'RPID144-v2.pdf','Abdomen Pelvis without contrast.pdf','2015-04-05 13:00:00',5,1,'2015-04-05 13:00:00'),('RPID145','CT Abdomen and pelvis with IV and oral contrast',3,'CT','',0,0,0,1,0,0,0,'RPID145-v7.pdf','Abdomen Pelvis with contrast Routine.pdf','2015-04-05 12:58:00',5,1,'2015-07-27 16:02:00'),('RPID159','CT HEAD- POSTERIOR FOSSA (3mm) with Contrast (axial) A',5,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-07-27 16:06:00'),('RPID16','CT Chest without IV contrast',4,'CT','',0,0,0,0,0,1,0,'','',NULL,NULL,1,'2014-11-08 17:35:00'),('RPID160','CT HEAD- POSTERIOR FOSSA (3mm) Non-Contrast (axial) B',4,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-11-08 17:36:00'),('RPID18','CT Chest with IV contrast',4,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-11-08 18:16:00'),('RPID21','CT CERVICAL SPINE without CONTRAST',2,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-11-08 18:17:00'),('RPID22','CT HEAD Non-Contrast (axial)',3,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-11-08 18:18:00'),('RPID23','CT HEAD without and with Contrast (axial)',3,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-07-27 16:04:00'),('RPID24','CT HEAD with Contrast (axial)',4,'CT','',0,0,0,1,0,0,0,'RPID24-v4.pdf','RAPTOR Active CT Protocols HEAD DEMO.pdf','2015-04-06 03:15:00',5,1,'2015-04-06 03:15:00'),('RPID249','CT Chest, abdomen and pelvis with IV and oral contrast',5,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-11-01 22:41:00'),('RPID31','CT LUMBAR SPINE without CONTRAST',5,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2015-07-27 16:05:00'),('RPID33','CT LUMBAR SPINE with Contrast (helical)',4,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-11-08 18:26:00'),('RPID39','CT NECK with Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'RPID39-v4.pdf','neck_soft_tissue.pdf','2015-04-05 14:56:00',5,1,'2015-05-22 10:17:00'),('RPID42','CT ORBIT without & with CONTRAST',3,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-04-07 19:57:00'),('RPID66','CT NECK ANGIOGRAPHY (CTA) (helical) only',3,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-11-01 23:01:00'),('RPID96','CT HEAD PERFUSION with Contrast (axial)',3,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-11-01 22:08:00'),('US-EXAMPLE','US Example Placeholder Ultrasound Protocol',3,'US','',0,0,0,0,0,0,0,'US-EXAMPLE-v3.jpg','Penguins.jpg','2015-06-08 18:22:00',30,1,'2015-06-08 18:22:00'),('WAV004','CT High Resolution Spiral Chest (supine or prone)',2,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-13 15:19:00'),('WAV007','CT Chest, Abdomen and pelvis with oral contrast only',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:50:00'),('WAV008','CT Abdomen and pelvis with oral contrast only',2,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-13 15:20:00'),('WAV010','CT Four-phase liver',2,'CT','',0,0,0,1,0,1,0,'','',NULL,NULL,1,'2014-08-30 16:58:00'),('WAV011','CT Three-phase liver',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV012','CT KUB (normal, low dose)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV013','CT IVP',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-07-27 16:04:00'),('WAV014','CT renal mass protocol',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV015','CT adrenal mass protocol',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-07-27 16:03:00'),('WAV016','CT pancreas mass protocol',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV017','CT aortic dissection protocol',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV018','CT thoracic aortic aneurysm protocol',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV019','CT pre-stent evaluation (or R/O AAA leak)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV020','CT Three-phase post-stent evaluation',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV021','CT Two-phase post-stent evaluation',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV022','CT CERVICAL SPINE TRAUMA DETAILED Non-Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:54:00'),('WAV023','CT CERVICAL SPINE TRAUMA SCREEN Non-Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:54:00'),('WAV025','CT Chest Pulmonary Angiogram',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV026','CT FACIAL TRAUMA - CORONAL REFORMAT Non-Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:55:00'),('WAV033','CT HEAD ANGIOGRAPHY (CTA) Ð ANEURYSM with and without Contrast (axial & helical)',3,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-06-08 15:51:00'),('WAV034','CT HEAD-RADIATION TREATMENT PLANNING with Contrast (helicial)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV035','CT HEAD-STEALTH STEREOTACTIC with Contrast (helicial)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV036','CT HEAD-STEREOTACTIC THALAMOTOMY Non-Contrast (helicial)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:55:00'),('WAV037','CT LARYNX TUMOR with Contrast (helical and angled axial)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV038','CT LUMBAR SPINE DEGENERATIVE Non-Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:55:00'),('WAV042','CT NECK ANGIOGRAPHY (CTA) (helical), plus HEAD with and without Contrast (axial)',2,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-11-08 18:25:00'),('WAV044','CT NECK, ORAL, CAVITY, LARYNX, THYROID, without & with CONTRAST',1,'CT','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV045','CT ODONTOID TRAUMA Non-Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:55:00'),('WAV046','CT ORBIT SCREEN PRE-MRI (helical)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV047','CT ORBIT with Contrast (axial) with CORONAL SAGITTAL REFORMATS',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV049','CT PITUITARY (helical) and HEAD CT with Contrast (axial)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV050','CT SINUSITIS - CORONAL REFORMAT PRE-OP (helical)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV051','CT SINUSITIS AXIAL SCREEN (In-Patient/Elderly) (axial)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV052','CT SINUSITIS DIRECT CORONAL DETAILED PRE-OP (axial)',1,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV053','CT TEMPORAL BONE (High Res) (axial) & SPIRAL Non-Contrast (helical)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:56:00'),('WAV054','CT TEMPORAL BONE (High Res) (axial) & CORONAL Non-Contrast (axial)',2,'CT','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-08-30 16:56:00'),('WAV055','MR knee (left, right)',1,'MR','',0,0,0,0,0,0,0,'no-filename','',NULL,NULL,0,'2014-07-13 15:22:00'),('WAV056','MR shoulder (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV057','MR shoulder arthrogram (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV058','MR ankle (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV059','MR hip (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV060','MR hip arthrogram (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV061','MR wrist (left, right)',2,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2015-07-27 16:04:00'),('WAV062','MR wrist arthrogram (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV063','MR elbow (left, right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV064','MR run-off',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV065','MR Renal',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV066','MR thoracic aortogram',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV067','MR liver',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV068','MR CP',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV069','MR renal mass protocol',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV070','MR adrenal mass protocol',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV071','MR urogram',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV072','MR gynecologic study',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV073','MR pelvic venogram',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV074','MR ankle arthrogram (right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV075','MR ankle arthrogram (left)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV076','MR hip AVN/Fracture screen',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV077','MR elbow arthrogram (right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV078','MR elbow arthrogram (left)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV079','MR foot (right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV080','MR foot (left)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV081','MR foot OSTEO/Mass with Contrast (right)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV082','MR foot OSTEO/Mass with Contrast (left)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV083','MR Pelvis OSTEO with Contrast',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV084','MR Thighs Myosittis without Contrast',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV085','MR Subcutaneous Lipoma',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV086','MR Soft Tissue Mass with Contrast',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV087','MR HEAD VENOGRAM (MRV) without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV088','MR CERVICAL SPINE- (MULTIPLE SCLEROSIS) with CONTRAST ONLY',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV089','MR ELBOW NEUROGRAM (MRN) without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV090','MR HEAD & COW MRA without CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV091','MR HEAD- (MULTIPLE SCLEROSIS) without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV092','MR HEAD, NECK, & ARCH MRA without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV093','MR IAC & HEAD without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV094','MR KNEE NEUROGRAM (MRN) without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV095','MR PITUITARY & HEAD without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV096','MR POST-OP LUMBAR SPINE without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV097','MR SACRAL PLEXUS without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV098','MR SINUS TUMOR without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV099','MR gynecologic study',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV100','MR PITUITARY (helical) and HEAD CT with Contrast (axial)',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV101','MR PITUITARY & HEAD without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV102','MR SKULL BASE & PAROTID without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV103','MR THORACIC SPINE- (MULTIPLE SCLEROSIS) with CONTRAST ONLY',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV104','MR THORACIC SPINE without CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV105','MR TOTAL CORD SCREEN (C & T-Sp) for MULTIPLE SCLEROSIS without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV106','MR TOTAL SPINE SCREEN without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV107','MR TRIGEMINAL NEURALGIA (TIC DOLOREAUX) without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV108','MR WRIST NEUROGRAM (MRN) without & with CONRAST',1,'MR','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV109','MR BRACHIAL PLEXUS without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV110','MR HEAD- (SEIZURE) without & with CONTRAST',1,'MR','',0,0,0,0,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV111','NM Bone Marrow',2,'NM','',0,0,0,1,1,0,0,'','',NULL,NULL,1,'2014-07-25 14:41:00'),('WAV112','NM Bone Scan',2,'NM','',0,0,0,1,1,0,0,'','',NULL,NULL,1,'2014-07-25 14:42:00'),('WAV113','NM Brain Imaging',2,'NM','',0,0,1,1,1,0,0,'','',NULL,NULL,1,'2014-07-25 14:42:00'),('WAV114','NM Cisternogram',2,'NM','',0,0,0,0,1,0,0,'','',NULL,NULL,1,'2014-08-30 16:57:00'),('WAV115','NM CSF Shunt Eval',2,'NM','',0,0,0,1,1,0,0,'','',NULL,NULL,1,'2014-08-30 16:58:00'),('WAV116','NM Dacrocystogram',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV117','NM Gallium Scan',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV118','NM Gastric Emptying',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV119','NM GI Bleed Loc.',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV120','NM Hepatobillary',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV121','NM Liver Blood Pool',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV122','NM Liver/ Spleen',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV123','NM Lung Perfusion',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV124','NM Lung Ventilation',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV125','NM Lymph Node Map',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV126','NM Meckles',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV127','NM MIBG',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV128','NM Myocardial Perfusion Resting Dual or Stress with proto',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV129','NM Myocardial Perfusion Resting high/high (two day) with proto 240# - 280#',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV130','NM Myocardial Perfusion Resting high/high (two day) with proto over 280#',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV131','NM Myocardial Perfusion Resting high/high (two day) with proto up to 240#',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV132','NM Myocardial Perfusion Resting low/high (one day) with proto',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV133','NM Myocardial Perfusion Stress one or two day proto 240# - 280#',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV134','NM Myocardial Perfusion Stress one or two day proto over 280#',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV135','NM Myocardial Perfusion Stress one or two day proto up to 240#',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV136','NM Myocardial Perfusion Viability',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV137','NM Octreotide Scan',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV138','NM Parathyroid',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV139','NM Platelet',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV140','NM Renal Scan with GFR',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV141','NM Renal Scan',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV144','NM Thyroid Scan',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV146','NM Thyroid Uptake and Scan',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV147','NM Thyroid Whole Body Scan by rTSH stimulation',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV148','NM Thyroid Whole Body Scan by withdrawal',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV149','NM White Blood Cell Scan	with IN-111 Oxine',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV150','NM White Blood Cell Scan	with 99mTc-HMPAO',1,'NM','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16'),('WAV999','Other (See notes)',1,'','',0,0,0,1,0,0,0,'','',NULL,NULL,1,'2014-07-11 20:32:16');
/*!40000 ALTER TABLE `raptor_protocol_lib` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_lib_replaced`
--

DROP TABLE IF EXISTS `raptor_protocol_lib_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_lib_replaced` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Protocol short name which must be unique',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT 'Protocol name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this protocol if there have been replacements',
  `modality_abbr` varchar(2) NOT NULL DEFAULT '' COMMENT 'Modality abbreviation',
  `service_nm` varchar(10) NOT NULL DEFAULT '' COMMENT 'Service name',
  `lowerbound_weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Lower bound weight for this protocol',
  `upperbound_weight` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Upper bound weight for this protocol',
  `image_guided_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is an image guided protocol',
  `contrast_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is a contrast protocol',
  `radioisotope_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is a radioisotope protocol',
  `sedation_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this is a sedation protocol',
  `multievent_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this protocol can be associated with multiple schedule events',
  `filename` varchar(128) NOT NULL DEFAULT '' COMMENT 'Filename in RAPTOR of the scanned protocol',
  `original_filename` varchar(128) DEFAULT '' COMMENT 'Original filename of uploaded protocol',
  `original_file_upload_dt` datetime DEFAULT NULL COMMENT 'When this file was last uploaded',
  `original_file_upload_by_uid` int(10) unsigned DEFAULT NULL COMMENT 'Who uploaded the file',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this protocol is still active for use',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was replaced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Protocol Library Replaced Records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_lib_replaced`
--

LOCK TABLES `raptor_protocol_lib_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_lib_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_protocol_lib_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_lib_uploads`
--

DROP TABLE IF EXISTS `raptor_protocol_lib_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_lib_uploads` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Protocol short name which must be unique',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this protocol to match the parent record',
  `filename` varchar(128) NOT NULL DEFAULT '' COMMENT 'Filename in RAPTOR of the scanned protocol',
  `original_filename` varchar(128) DEFAULT NULL COMMENT 'Original filename of uploaded protocol',
  `filetype` varchar(8) NOT NULL COMMENT 'The uppercase file extension of the file',
  `file_blob` mediumblob NOT NULL COMMENT 'The uploaded image as a blob',
  `filesize` int(10) unsigned NOT NULL COMMENT 'Size in bytes',
  `uploaded_by_uid` int(10) unsigned DEFAULT NULL COMMENT 'Who uploaded the file',
  `comment_tx` varchar(1024) DEFAULT '' COMMENT 'Comment from uploader',
  `uploaded_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`protocol_shortname`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Protocol library upload tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_lib_uploads`
--

LOCK TABLES `raptor_protocol_lib_uploads` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_lib_uploads` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_protocol_lib_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_radiation_dose_tracking`
--

DROP TABLE IF EXISTS `raptor_protocol_radiation_dose_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_radiation_dose_tracking` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `protocol_shortname` varchar(20) NOT NULL COMMENT 'Protocol short name key value',
  `dose_source_cd` char(1) NOT NULL COMMENT 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
  `uom` varchar(32) NOT NULL COMMENT 'Unit of measure for the exposure',
  `dose_type_cd` char(1) NOT NULL COMMENT 'E=Estimated,A=Actual,U=Unknown Quality',
  `dose_avg` float unsigned NOT NULL COMMENT 'Average amount of exposure',
  `sample_ct` int(10) unsigned NOT NULL COMMENT 'Number of samples averaged into this record',
  `baseline_dose_avg` float unsigned NOT NULL DEFAULT '0' COMMENT 'Initial baseline average amount of exposure',
  `baseline_sample_ct` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Number of samples averaged into baseline value of this record',
  `lowest_dose_value` float unsigned DEFAULT NULL COMMENT 'Lowest dose instance',
  `highest_dose_value` float unsigned DEFAULT NULL COMMENT 'Highest dose instance',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `created_dt` datetime NOT NULL COMMENT 'When this record was originally created',
  PRIMARY KEY (`siteid`,`protocol_shortname`,`dose_source_cd`,`uom`,`dose_type_cd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Simple average radiation exposure for a protocol at a site.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_radiation_dose_tracking`
--

LOCK TABLES `raptor_protocol_radiation_dose_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_radiation_dose_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_protocol_radiation_dose_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_template`
--

DROP TABLE IF EXISTS `raptor_protocol_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_template` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Must match the protocol shortname in protocol_lib table',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this protocol is still active for use',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `consent_req_kw` varchar(8) DEFAULT '' COMMENT 'consent required values are yes, no, or unknown.',
  `protocolnotes_tx` varchar(1024) DEFAULT '' COMMENT 'protocol notes text',
  `examnotes_tx` varchar(1024) DEFAULT '' COMMENT 'exam notes text',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`protocol_shortname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Template values';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_template`
--

LOCK TABLES `raptor_protocol_template` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_template` DISABLE KEYS */;
INSERT INTO `raptor_protocol_template` VALUES ('FL-EXAMPLE',0,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'unknown','','','2015-07-27 16:16:00'),('NM-EXAMPLE',1,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'unknown','','','2015-03-09 07:33:00'),('RPID144',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','Image Order: Dome of the diaphragm through the pubic symphysis','Reconstruction: Coronal  2x2mm  Sagittal   2x2mm','2015-04-05 13:00:00'),('RPID145',1,1,NULL,NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','Application/Chief Complaint: R/O abscess, FUO, Abdominal Pain, Diverticulitis, Cancer staging. Image Order: Dome of the diaphragm through the pubic symphysis IV: Scan delay 65 sec.','Scan delay 65 sec. Reconstruction: Coronal  2x2mm   Sagittal   2x2mm','2015-07-27 16:02:00'),('RPID159',1,1,NULL,NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2015-07-27 16:06:00'),('RPID16',1,0,'200cc H2O over 1hr pre-scan + post-scan',NULL,0,'Valium 10mg PO 20 min before scan',NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso facto','ipso facto','2014-11-08 17:35:00'),('RPID160',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-11-08 17:36:00'),('RPID18',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2014-11-08 18:16:00'),('RPID21',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-11-08 18:17:00'),('RPID22',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-11-08 18:18:00'),('RPID23',1,1,NULL,NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2015-07-27 16:04:00'),('RPID24',1,1,NULL,NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2015-04-06 03:15:00'),('RPID31',1,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'unknown','','','2015-07-27 16:05:00'),('RPID33',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'Visipaque 320',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2014-11-08 18:26:00'),('RPID39',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','Application:	Mass, Swelling, Lump etc. [NL]KV / Effective mAs / Rotation time (sec)	120 / 300 / 0.5','Axial Slice Thickness, Interval  (mm)	3x3 [NL]Image Order	cr-ca in inspiration, skull base thru thoracic inlet','2015-05-22 10:17:00'),('RPID42',1,1,NULL,NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','','','2015-04-07 19:57:00'),('RPID66',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'Isovue 370',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2014-11-01 23:01:00'),('RPID96',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','ipso default protocol notes facto','ipso default exam notes facto','2014-11-01 22:08:00'),('US-EXAMPLE',1,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'unknown','','','2015-06-08 18:22:00'),('WAV004',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','','','2014-07-13 15:19:00'),('WAV007',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,'GastroView 450cc during 1-2 hrs before scan',NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:50:00'),('WAV008',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,NULL,'','','2014-07-13 15:20:00'),('WAV010',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,0,'Valium 10mg PO 20 min before scan',NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:58:00'),('WAV011',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV012',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV013',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2015-07-27 16:04:00'),('WAV014',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV015',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2015-07-27 16:03:00'),('WAV016',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV017',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV018',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV019',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV020',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV021',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV022',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:54:00'),('WAV023',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:54:00'),('WAV025',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV026',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:55:00'),('WAV033',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'Ultravist 300',1,NULL,NULL,'unknown','','','2015-06-08 15:51:00'),('WAV034',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV035',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV036',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:55:00'),('WAV037',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV038',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:55:00'),('WAV042',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-11-08 18:25:00'),('WAV044',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV045',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:55:00'),('WAV046',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV047',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV049',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV050',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV051',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV052',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV053',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:56:00'),('WAV054',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:56:00'),('WAV055',1,0,NULL,'InPt- NS 1-2 mL/kg/hr 12 hr pre & postscan',1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'no','','','2014-07-13 15:22:00'),('WAV056',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV057',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV058',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV059',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV060',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV061',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2015-07-27 16:04:00'),('WAV062',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV063',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV064',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV065',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV066',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV067',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV068',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV069',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV070',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV071',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV072',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV073',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV074',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV075',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV076',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV077',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV078',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV079',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV080',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV081',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV082',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV083',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV084',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV085',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV086',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV087',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV088',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV089',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV090',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV091',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV092',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV093',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV094',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV095',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV096',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV097',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV098',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV099',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV100',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV101',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV102',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV103',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV104',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV105',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV106',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV107',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV108',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV109',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV110',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV111',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',0,'barium',NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-25 14:41:00'),('WAV112',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',0,'barium','Other','yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-25 14:42:00'),('WAV113',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',0,NULL,'Technetium-99m','yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-25 14:42:00'),('WAV114',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,1,NULL,NULL,0,NULL,'Technetium-99m','yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:57:00'),('WAV115',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',0,'barium',NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-08-30 16:58:00'),('WAV116',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV117',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV118',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV119',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV120',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV121',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV122',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV123',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV124',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV125',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV126',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV127',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV128',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV129',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV130',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV131',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV132',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV133',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV134',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV135',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV136',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV137',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV138',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV139',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV140',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV141',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV144',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV146',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV147',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV148',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV149',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV150',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16'),('WAV999',1,0,'500cc H2O over 2hr pre-scan + post-scan',NULL,1,NULL,NULL,0,NULL,'ProHance',1,NULL,NULL,'yes','ipso default protocol notes facto','ipso default exam notes facto','2014-07-11 20:32:16');
/*!40000 ALTER TABLE `raptor_protocol_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_protocol_template_replaced`
--

DROP TABLE IF EXISTS `raptor_protocol_template_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_protocol_template_replaced` (
  `protocol_shortname` varchar(20) NOT NULL DEFAULT '' COMMENT 'Must match the protocol shortname in protocol_lib table',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if this protocol is still active for use',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if default value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `consent_req_kw` varchar(8) DEFAULT '' COMMENT 'consent required values are yes, no, or unknown.',
  `protocolnotes_tx` varchar(1024) DEFAULT '' COMMENT 'protocol notes text',
  `examnotes_tx` varchar(1024) DEFAULT '' COMMENT 'exam notes text',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was replaced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Template values of replaced records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_protocol_template_replaced`
--

LOCK TABLES `raptor_protocol_template_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_protocol_template_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_protocol_template_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_qa_criteria`
--

DROP TABLE IF EXISTS `raptor_qa_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_qa_criteria` (
  `context_cd` char(1) NOT NULL DEFAULT 'T' COMMENT 'T=Ticket',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this criteria if there have been replacements',
  `position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Relative order for default presentation of the criteria',
  `shortname` varchar(32) NOT NULL DEFAULT '' COMMENT 'A unique ID for this criteria',
  `question` varchar(256) NOT NULL DEFAULT '' COMMENT 'The criteria question',
  `explanation` varchar(2048) NOT NULL DEFAULT '' COMMENT 'The criteria explained',
  `updated_dt` datetime NOT NULL COMMENT 'When this criteria was last updated',
  PRIMARY KEY (`context_cd`,`shortname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quality assurance evaluation criteria';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_qa_criteria`
--

LOCK TABLES `raptor_qa_criteria` WRITE;
/*!40000 ALTER TABLE `raptor_qa_criteria` DISABLE KEYS */;
INSERT INTO `raptor_qa_criteria` VALUES ('T',1,1,'SAMPLE_QA_QUESTION1','QA Evaluation Criteria Item1','This is a sample evaluation criteria','2015-10-02 14:52:16'),('T',1,2,'SAMPLE_QA_QUESTION2','QA Evaluation Criteria Item2','This is a sample evaluation criteria','2015-10-02 14:52:16'),('T',1,3,'SAMPLE_QA_QUESTION3','QA Evaluation Criteria Item3','This is a sample evaluation criteria','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_qa_criteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_qa_criteria_replaced`
--

DROP TABLE IF EXISTS `raptor_qa_criteria_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_qa_criteria_replaced` (
  `context_cd` char(1) NOT NULL DEFAULT 'T' COMMENT 'T=Ticket',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of the replaced criteria',
  `position` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Relative order for default presentation of the criteria',
  `shortname` varchar(32) NOT NULL DEFAULT '' COMMENT 'A unique ID for this criteria',
  `question` varchar(256) NOT NULL DEFAULT '' COMMENT 'The criteria question',
  `explanation` varchar(2048) NOT NULL DEFAULT '' COMMENT 'The criteria explained',
  `updated_dt` datetime NOT NULL COMMENT 'When this criteria was last updated',
  `replacement_dt` datetime NOT NULL COMMENT 'When this criteria was replaced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A replaced quality assurance evaluation criteria';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_qa_criteria_replaced`
--

LOCK TABLES `raptor_qa_criteria_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_qa_criteria_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_qa_criteria_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_radlex_codes`
--

DROP TABLE IF EXISTS `raptor_radlex_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_radlex_codes` (
  `radlex_cd` varchar(12) NOT NULL COMMENT 'The RADLEX code',
  `exclude_from_worklist_yn` int(10) unsigned NOT NULL COMMENT 'If 1 then orders of this type are ignored by RAPTOR',
  `contrast_yn` int(10) unsigned DEFAULT NULL COMMENT 'If 1, then contrast, else no contrast.',
  `modality_abbr` varchar(2) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the modality associated with this order',
  `service_nm` varchar(10) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the service associated with this order',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relevant information about RADLEX codes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_radlex_codes`
--

LOCK TABLES `raptor_radlex_codes` WRITE;
/*!40000 ALTER TABLE `raptor_radlex_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_radlex_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_rep_example_table`
--

DROP TABLE IF EXISTS `raptor_rep_example_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_rep_example_table` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `some_dt` datetime DEFAULT NULL COMMENT 'Event start date and time',
  `notes_tx` varchar(1024) DEFAULT '' COMMENT 'Notes associated with this item'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Example table just to illustrate installer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_rep_example_table`
--

LOCK TABLES `raptor_rep_example_table` WRITE;
/*!40000 ALTER TABLE `raptor_rep_example_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_rep_example_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_role`
--

DROP TABLE IF EXISTS `raptor_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_role` (
  `roleid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Unique for each row of table',
  `enabled_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Role is available for users only if enabled',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT 'Role name',
  `CEUA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can create/edit other accounts',
  `lockCEUA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then CEUA1 value can be changed, else locked.',
  `LACE1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can create/edit resident account',
  `lockLACE1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then LACE1 value can be changed, else locked.',
  `SWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can select worklist items for view',
  `lockSWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then SWI1 value can be changed, else locked.',
  `PWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can protocol worklist items',
  `lockPWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then PWI1 value can be changed, else locked.',
  `APWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can approve protocoled worklist items',
  `lockAPWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then APWI1 value can be changed, else locked.',
  `SUWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can cancel a worklist item',
  `lockSUWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then SUWI1 value can be changed, else locked.',
  `CE1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can complete an examination',
  `lockCE1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then CE1 value can be changed, else locked.',
  `QA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can complete a QA examination',
  `lockQA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then QA1 value can be changed, else locked.',
  `SP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user edit pass box',
  `lockSP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then SP1 value can be changed, else locked.',
  `VREP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can view department activity report',
  `lockVREP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then VREP1 value can be changed, else locked.',
  `VREP2` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can view department activity report',
  `lockVREP2` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then VREP2 value can be changed, else locked.',
  `EBO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit protocol/exam note boilerplate options',
  `lockEBO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then EBO1 value can be changed, else locked.',
  `UNP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can upload new protocols',
  `lockUNP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then UNP1 value can be changed, else locked.',
  `REP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can retire existing protocols',
  `lockREP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then REP1 value can be changed, else locked.',
  `DRA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit default and required attribs of roles',
  `lockDRA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then DRA1 value can be changed, else locked.',
  `ELCO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit contrast options',
  `lockELCO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then ELCO1 value can be changed, else locked.',
  `ELHO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit hydration options',
  `lockELHO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then ELHO1 value can be changed, else locked.',
  `ELSO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit sedation options',
  `lockELSO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then ELSO1 value can be changed, else locked.',
  `ELRO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit radioisotope options',
  `lockELRO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then ELRO1 value can be changed, else locked.',
  `ELSVO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit service options',
  `lockELSVO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then ELSVO1 value can be changed, else locked.',
  `EECC1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit CPRS code metadata',
  `lockEECC1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then EECC1 value can be changed, else locked.',
  `ECIR1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit contraindication rules',
  `lockECIR1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then ECIR1 value can be changed, else locked.',
  `EERL1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can alter list of examination rooms',
  `lockEERL1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then EERL1 value can be changed, else locked.',
  `EARM1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit at risk medications keywords',
  `lockEARM1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then EARM1 value can be changed, else locked.',
  `CUT1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can create/edit umbrella terms and associate keywords with them',
  `lockCUT1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then CUT1 value can be changed, else locked.',
  `QA2` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can see all QA results',
  `lockQA2` int(11) NOT NULL DEFAULT '0' COMMENT 'If 0 then QA2 value can be changed, else locked.',
  `QA3` int(11) DEFAULT '0' COMMENT 'If 1 then user can edit all QA criteria',
  `lockQA3` int(11) DEFAULT '0' COMMENT 'If 0 then QA3 value can be changed, else locked.',
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When this record was last updated',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Raptor Roles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_role`
--

LOCK TABLES `raptor_role` WRITE;
/*!40000 ALTER TABLE `raptor_role` DISABLE KEYS */;
INSERT INTO `raptor_role` VALUES (10,1,'Radiologist',0,1,1,0,1,1,1,1,1,0,0,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,1,0,1,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'2015-10-02 18:52:16'),(20,1,'Resident',0,1,0,1,1,1,1,1,0,0,0,1,0,0,0,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'2015-10-02 18:52:16'),(40,1,'Scheduler',0,1,0,1,1,0,0,1,0,1,0,1,0,0,0,1,1,0,1,0,1,0,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1,0,0,0,0,1,1,0,0,0,0,0,0,0,0,0,'2015-10-02 18:52:16'),(1,1,'Site Administrator',1,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1,1,0,1,0,1,0,1,0,1,0,1,1,1,0,1,0,1,0,1,0,1,0,0,0,1,0,1,0,1,0,1,0,0,0,0,0,'2015-10-02 18:52:16'),(30,1,'Technologist',0,1,0,1,1,1,0,1,1,0,0,0,1,1,1,0,1,0,1,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'2015-10-02 18:52:16');
/*!40000 ALTER TABLE `raptor_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_schedule_duration`
--

DROP TABLE IF EXISTS `raptor_schedule_duration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_schedule_duration` (
  `minutes` int(10) unsigned NOT NULL COMMENT 'Exam duration minutes',
  PRIMARY KEY (`minutes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Exam duration shortcut values available for scheduling';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_schedule_duration`
--

LOCK TABLES `raptor_schedule_duration` WRITE;
/*!40000 ALTER TABLE `raptor_schedule_duration` DISABLE KEYS */;
INSERT INTO `raptor_schedule_duration` VALUES (10),(15),(20),(30),(45),(60);
/*!40000 ALTER TABLE `raptor_schedule_duration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_schedule_location`
--

DROP TABLE IF EXISTS `raptor_schedule_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_schedule_location` (
  `location_tx` varchar(20) NOT NULL DEFAULT '' COMMENT 'Location identifier',
  `description_tx` varchar(512) DEFAULT '' COMMENT 'Description of the location',
  PRIMARY KEY (`location_tx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Locations available for scheduling';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_schedule_location`
--

LOCK TABLES `raptor_schedule_location` WRITE;
/*!40000 ALTER TABLE `raptor_schedule_location` DISABLE KEYS */;
INSERT INTO `raptor_schedule_location` VALUES ('RM 120','Ultrasound Room 1'),('RM 121','Ultrasound Room 2'),('RM 130','XRAY Room'),('RM 155','CT Room 1'),('RM 158','CT Room 2'),('RM 250','FL Room'),('RM 255','NM Room'),('RM 390','MRI Room');
/*!40000 ALTER TABLE `raptor_schedule_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_schedule_track`
--

DROP TABLE IF EXISTS `raptor_schedule_track`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_schedule_track` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `scheduled_dt` datetime DEFAULT NULL COMMENT 'Event start date and time',
  `duration_am` int(10) unsigned DEFAULT NULL COMMENT 'Duration amount in minutes',
  `notes_tx` varchar(1024) DEFAULT '' COMMENT 'Notes associated with this item',
  `notes_critical_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 is critcal note, 0 is not critical note',
  `location_tx` varchar(20) DEFAULT '' COMMENT 'Location identifier',
  `confirmed_by_patient_dt` datetime DEFAULT NULL COMMENT 'If not null, then confirmed by patient on this date',
  `canceled_reason_tx` varchar(20) DEFAULT '' COMMENT 'If not null, then canceled for this reason',
  `canceled_dt` datetime DEFAULT NULL COMMENT 'If not null, then canceled on this date',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track scheduling of an exam';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_schedule_track`
--

LOCK TABLES `raptor_schedule_track` WRITE;
/*!40000 ALTER TABLE `raptor_schedule_track` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_schedule_track` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_schedule_track_replaced`
--

DROP TABLE IF EXISTS `raptor_schedule_track_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_schedule_track_replaced` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `scheduled_dt` datetime DEFAULT NULL COMMENT 'Event start date and time',
  `duration_am` int(10) unsigned DEFAULT NULL COMMENT 'Duration amount in minutes',
  `notes_tx` varchar(1024) DEFAULT '' COMMENT 'Notes associated with this item',
  `notes_critical_yn` int(10) unsigned NOT NULL COMMENT '1 is critcal note, 0 is not critical note',
  `location_tx` varchar(20) DEFAULT '' COMMENT 'Location identifier',
  `confirmed_by_patient_dt` datetime DEFAULT NULL COMMENT 'If not null, then confirmed by patient on this date',
  `canceled_reason_tx` varchar(20) DEFAULT '' COMMENT 'If not null, then canceled for this reason',
  `canceled_dt` datetime DEFAULT NULL COMMENT 'If not null, then canceled on this date',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `original_created_dt` datetime NOT NULL COMMENT 'When this record was created',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track replaced scheduling of an exam';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_schedule_track_replaced`
--

LOCK TABLES `raptor_schedule_track_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_schedule_track_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_schedule_track_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_snomed_codes`
--

DROP TABLE IF EXISTS `raptor_snomed_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_snomed_codes` (
  `snomed_cd` varchar(40) NOT NULL COMMENT 'The SNOMED code',
  `exclude_from_worklist_yn` int(10) unsigned NOT NULL COMMENT 'If 1 then orders of this type are ignored by RAPTOR',
  `contrast_yn` int(10) unsigned DEFAULT NULL COMMENT 'If 1, then contrast, else no contrast.',
  `modality_abbr` varchar(2) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the modality associated with this order',
  `service_nm` varchar(10) DEFAULT NULL COMMENT 'If not excluded, then this field tells us the service associated with this order',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relevant information about SNOMED codes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_snomed_codes`
--

LOCK TABLES `raptor_snomed_codes` WRITE;
/*!40000 ALTER TABLE `raptor_snomed_codes` DISABLE KEYS */;
INSERT INTO `raptor_snomed_codes` VALUES ('74000',1,NULL,'','','2015-10-02 14:52:16'),('74010',1,NULL,'','','2015-10-02 14:52:16'),('73050',1,NULL,'','','2015-10-02 14:52:16'),('75710',0,NULL,'CT','','2015-10-02 14:52:16'),('73600',1,NULL,'','','2015-10-02 14:52:16'),('73610',1,NULL,'','','2015-10-02 14:52:16'),('73650',1,NULL,'','','2015-10-02 14:52:16'),('71020',1,NULL,'','','2015-10-02 14:52:16'),('71035',1,NULL,'','','2015-10-02 14:52:16'),('71022',1,NULL,'','','2015-10-02 14:52:16'),('75716',0,NULL,'CT','','2015-10-02 14:52:16'),('74300',0,NULL,'CT','','2015-10-02 14:52:16'),('74290',0,NULL,'CT','','2015-10-02 14:52:16');
/*!40000 ALTER TABLE `raptor_snomed_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_checklist`
--

DROP TABLE IF EXISTS `raptor_ticket_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_checklist` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `question_shortname` varchar(20) NOT NULL COMMENT 'The unique shortname of the question in the system',
  `question_tx` varchar(512) NOT NULL COMMENT 'The actual question text the user was asked',
  `answer_tx` varchar(16) DEFAULT NULL COMMENT 'The response provided by the user',
  `comment_prompt_tx` varchar(128) DEFAULT NULL COMMENT 'Prompt shown the user when asking for a comment',
  `comment_tx` varchar(512) DEFAULT NULL COMMENT 'Comment provided by the user',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Checklist question responses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_checklist`
--

LOCK TABLES `raptor_ticket_checklist` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_checklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_checklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_checklist_replaced`
--

DROP TABLE IF EXISTS `raptor_ticket_checklist_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_checklist_replaced` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `question_shortname` varchar(20) NOT NULL COMMENT 'The unique shortname of the question in the system',
  `question_tx` varchar(512) NOT NULL COMMENT 'The actual question text the user was asked',
  `answer_tx` varchar(16) DEFAULT NULL COMMENT 'The response provided by the user',
  `comment_prompt_tx` varchar(128) DEFAULT NULL COMMENT 'Prompt shown the user when asking for a comment',
  `comment_tx` varchar(512) DEFAULT NULL COMMENT 'Comment provided by the user',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'The creation date of the replaced record',
  `replaced_dt` datetime NOT NULL COMMENT 'When the record was replaced'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Checklist question responses replaced records';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_checklist_replaced`
--

LOCK TABLES `raptor_ticket_checklist_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_checklist_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_checklist_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_collaboration`
--

DROP TABLE IF EXISTS `raptor_ticket_collaboration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_collaboration` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `requester_uid` int(10) unsigned NOT NULL COMMENT 'Who requested the collaboration',
  `requested_dt` datetime NOT NULL COMMENT 'When this collaboration was requested',
  `requester_notes_tx` varchar(1024) DEFAULT '' COMMENT 'notes text',
  `collaborator_uid` int(10) unsigned NOT NULL COMMENT 'Who requested for collaboration',
  `viewed_dt` datetime DEFAULT NULL COMMENT 'When the ticket was viewed by the collaborator',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'This value is 1 if request is still active',
  `workflow_state` varchar(2) DEFAULT NULL COMMENT 'Ticket workflow state at time of request',
  KEY `main_idx` (`siteid`,`IEN`,`collaborator_uid`,`active_yn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Collaboration on tickets';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_collaboration`
--

LOCK TABLES `raptor_ticket_collaboration` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_collaboration` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_collaboration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_commit_tracking`
--

DROP TABLE IF EXISTS `raptor_ticket_commit_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_commit_tracking` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `workflow_state` varchar(2) NOT NULL DEFAULT '' COMMENT 'Workflow state of committed data',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that committed this data',
  `commit_dt` datetime NOT NULL COMMENT 'When this data was committed',
  PRIMARY KEY (`siteid`,`IEN`,`commit_dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track commit of ticket data into VISTA';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_commit_tracking`
--

LOCK TABLES `raptor_ticket_commit_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_commit_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_commit_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_contraindication`
--

DROP TABLE IF EXISTS `raptor_ticket_contraindication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_contraindication` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `rule_nm` varchar(40) NOT NULL COMMENT 'Must be unique',
  `acknowledged_yn` int(10) unsigned NOT NULL COMMENT 'If acknowledged then value is 1, else value is 0.',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contraindication acknowledgements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_contraindication`
--

LOCK TABLES `raptor_ticket_contraindication` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_contraindication` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_contraindication` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_notes`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_notes` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket exam notes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_notes`
--

LOCK TABLES `raptor_ticket_exam_notes` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_notes_sofar`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_notes_sofar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_notes_sofar` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket exam notes so far.  This is a backup record that...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_notes_sofar`
--

LOCK TABLES `raptor_ticket_exam_notes_sofar` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_notes_sofar` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_notes_sofar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_radiation_dose`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_radiation_dose`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_radiation_dose` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL COMMENT 'Ticket identifier within the site',
  `patientid` int(10) unsigned NOT NULL COMMENT 'ID for the patient',
  `sequence_position` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Order of exposures for an exam',
  `dose` float unsigned NOT NULL COMMENT 'Amount of exposure',
  `uom` varchar(32) NOT NULL COMMENT 'Unit of measure for the exposure',
  `dose_type_cd` char(1) NOT NULL COMMENT 'E=Estimated,A=Actual,U=Unknown Quality',
  `dose_source_cd` char(1) NOT NULL COMMENT 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
  `dose_target_area_id` int(10) unsigned DEFAULT NULL COMMENT 'Target area lookup of codes',
  `dose_dt` datetime NOT NULL COMMENT 'When this dose was received',
  `data_provider` varchar(32) DEFAULT '' COMMENT 'Data provider for this record',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that associated with entry of this value',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`siteid`,`IEN`,`dose_source_cd`,`sequence_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Radiation exposure associated with an exam';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_radiation_dose`
--

LOCK TABLES `raptor_ticket_exam_radiation_dose` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_radiation_dose` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_radiation_dose` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_radiation_dose_replaced`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_radiation_dose_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_radiation_dose_replaced` (
  `siteid` int(10) unsigned NOT NULL COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL COMMENT 'Ticket identifier within the site',
  `patientid` int(10) unsigned NOT NULL COMMENT 'ID for the patient',
  `sequence_position` int(10) unsigned NOT NULL COMMENT 'Order of exposures for an exam',
  `dose` float unsigned NOT NULL COMMENT 'Amount of exposure',
  `uom` varchar(32) NOT NULL COMMENT 'Unit of measure for the exposure',
  `dose_type_cd` char(1) NOT NULL COMMENT 'E=Estimated, A=Actual, U=Unknown Quality',
  `dose_source_cd` char(1) NOT NULL COMMENT 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
  `dose_target_area_id` int(10) unsigned DEFAULT NULL COMMENT 'Target area lookup of codes',
  `dose_dt` datetime NOT NULL COMMENT 'When this dose was received',
  `data_provider` varchar(32) DEFAULT NULL COMMENT 'Data provider for this record',
  `author_uid` int(10) unsigned NOT NULL COMMENT 'The user that associated with entry of this value',
  `original_created_dt` datetime NOT NULL COMMENT 'The creation date of the replaced record',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Radiation exposure associated with an exam';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_radiation_dose_replaced`
--

LOCK TABLES `raptor_ticket_exam_radiation_dose_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_radiation_dose_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_radiation_dose_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_radiation_dose_sofar`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_radiation_dose_sofar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_radiation_dose_sofar` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL COMMENT 'Ticket identifier within the site',
  `patientid` int(10) unsigned NOT NULL COMMENT 'ID for the patient',
  `sequence_position` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Order of exposures for an exam',
  `dose` float unsigned NOT NULL COMMENT 'Amount of exposure',
  `uom` varchar(32) NOT NULL COMMENT 'Unit of measure for the exposure',
  `dose_type_cd` char(1) NOT NULL COMMENT 'E=Estimated,A=Actual,U=Unknown Quality',
  `dose_source_cd` char(1) NOT NULL COMMENT 'R=Radioisotope, E=Equipment Other, C=CTDIvol, D=DLP, Fluoro=[Q,S,T,H]',
  `dose_target_area_id` int(10) unsigned DEFAULT NULL COMMENT 'Target area lookup of codes',
  `dose_dt` datetime NOT NULL COMMENT 'When this dose was received',
  `data_provider` varchar(32) DEFAULT '' COMMENT 'Data provider for this record',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that associated with entry of this value',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Radiation exposure values so far associated with an exam....';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_radiation_dose_sofar`
--

LOCK TABLES `raptor_ticket_exam_radiation_dose_sofar` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_radiation_dose_sofar` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_radiation_dose_sofar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_settings`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_settings` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `current_workflow_state_cd` varchar(2) NOT NULL COMMENT 'Current workflow state code',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `consent_received_kw` varchar(8) DEFAULT '' COMMENT 'Consent received values are yes, no, or leave null (unknown).',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket exam values as provided by exam completer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_settings`
--

LOCK TABLES `raptor_ticket_exam_settings` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_settings_replaced`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_settings_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_settings_replaced` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `current_workflow_state_cd` varchar(2) NOT NULL COMMENT 'Current workflow state code',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `consent_received_kw` varchar(8) DEFAULT '' COMMENT 'Consent received values are yes, no, or leave null (unknown).',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `original_created_dt` datetime NOT NULL COMMENT 'The creation date of the replaced record',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Replaced ticket exam values as provided by exam completer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_settings_replaced`
--

LOCK TABLES `raptor_ticket_exam_settings_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_settings_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_settings_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_exam_settings_sofar`
--

DROP TABLE IF EXISTS `raptor_ticket_exam_settings_sofar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_exam_settings_sofar` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `current_workflow_state_cd` varchar(2) NOT NULL COMMENT 'Current workflow state code',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `consent_received_kw` varchar(8) DEFAULT '' COMMENT 'Consent received values are yes, no, or leave null (unknown).',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket exam values as provided so far by an examiner. ...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_exam_settings_sofar`
--

LOCK TABLES `raptor_ticket_exam_settings_sofar` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_exam_settings_sofar` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_exam_settings_sofar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_id_change_tracking`
--

DROP TABLE IF EXISTS `raptor_ticket_id_change_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_id_change_tracking` (
  `original_siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site for original ticket',
  `original_IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Original ticket identifier within the site',
  `new_siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site for new ticket',
  `new_IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'New ticket identifier within the site',
  `workflow_state_at_change` varchar(2) NOT NULL DEFAULT '' COMMENT 'Workflow state at time of the change',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that initiated this change',
  `created_dt` datetime NOT NULL COMMENT 'When this change happened',
  PRIMARY KEY (`original_siteid`,`original_IEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track change of ticket id when a VISTA order is replaced.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_id_change_tracking`
--

LOCK TABLES `raptor_ticket_id_change_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_id_change_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_id_change_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_interpret_notes`
--

DROP TABLE IF EXISTS `raptor_ticket_interpret_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_interpret_notes` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket interpretation notes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_interpret_notes`
--

LOCK TABLES `raptor_ticket_interpret_notes` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_interpret_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_interpret_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_lock_tracking`
--

DROP TABLE IF EXISTS `raptor_ticket_lock_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_lock_tracking` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `locked_by_uid` int(10) unsigned NOT NULL COMMENT 'Who locked the ticket',
  `locked_type_cd` varchar(1) NOT NULL DEFAULT 'E' COMMENT 'Type of lock',
  `lock_started_dt` datetime DEFAULT NULL COMMENT 'When the lock began',
  `lock_refreshed_dt` datetime DEFAULT NULL COMMENT 'Most recent time the lock has been refreshed',
  PRIMARY KEY (`siteid`,`IEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket Lock Tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_lock_tracking`
--

LOCK TABLES `raptor_ticket_lock_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_lock_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_lock_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_protocol_notes`
--

DROP TABLE IF EXISTS `raptor_ticket_protocol_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_protocol_notes` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket protocol notes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_protocol_notes`
--

LOCK TABLES `raptor_ticket_protocol_notes` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_protocol_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_protocol_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_protocol_settings`
--

DROP TABLE IF EXISTS `raptor_ticket_protocol_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_protocol_settings` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `primary_protocol_shortname` varchar(20) NOT NULL COMMENT 'Primary protocol short name',
  `secondary_protocol_shortname` varchar(20) DEFAULT '' COMMENT 'Secondary protocol short name',
  `current_workflow_state_cd` varchar(2) NOT NULL COMMENT 'Current workflow state code',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `allergy_kw` varchar(8) DEFAULT '' COMMENT 'allergy values are yes, no, or leave null (unknown).',
  `claustrophobic_kw` varchar(8) DEFAULT '' COMMENT 'claustrophobic values are yes, no, or leave null (unknown).',
  `consent_req_kw` varchar(8) DEFAULT '' COMMENT 'Consent required values are yes, no, or leave null (unknown).',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`siteid`,`IEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket protocol values as provided by protocoler';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_protocol_settings`
--

LOCK TABLES `raptor_ticket_protocol_settings` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_protocol_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_protocol_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_protocol_settings_replaced`
--

DROP TABLE IF EXISTS `raptor_ticket_protocol_settings_replaced`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_protocol_settings_replaced` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `primary_protocol_shortname` varchar(20) NOT NULL COMMENT 'Primary protocol short name',
  `secondary_protocol_shortname` varchar(20) DEFAULT '' COMMENT 'Secondary protocol short name',
  `current_workflow_state_cd` varchar(2) NOT NULL COMMENT 'Current workflow state code',
  `hydration_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `hydration_oral_tx` varchar(100) DEFAULT NULL COMMENT 'hydration oral default value',
  `hydration_iv_tx` varchar(100) DEFAULT NULL COMMENT 'hydration iv default value',
  `sedation_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `sedation_oral_tx` varchar(100) DEFAULT NULL COMMENT 'sedation oral default value',
  `sedation_iv_tx` varchar(100) DEFAULT NULL COMMENT 'sedation iv default value',
  `contrast_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `contrast_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'contrast enteric default value',
  `contrast_iv_tx` varchar(100) DEFAULT NULL COMMENT 'contrast iv default value',
  `radioisotope_none_yn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'True if value is NONE',
  `radioisotope_enteric_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope enteric default value',
  `radioisotope_iv_tx` varchar(100) DEFAULT NULL COMMENT 'radioisotope iv default value',
  `allergy_kw` varchar(8) DEFAULT '' COMMENT 'allergy values are yes, no, or unknown.',
  `claustrophobic_kw` varchar(8) DEFAULT '' COMMENT 'claustrophobic values are yes, no, or unknown.',
  `consent_req_kw` varchar(8) DEFAULT '' COMMENT 'Consent required values are yes, no, or unknown.',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that selected these values',
  `original_created_dt` datetime NOT NULL COMMENT 'The creation date of the replaced record',
  `replaced_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket protocol values as provided by protocoler which...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_protocol_settings_replaced`
--

LOCK TABLES `raptor_ticket_protocol_settings_replaced` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_protocol_settings_replaced` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_protocol_settings_replaced` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_qa_evaluation`
--

DROP TABLE IF EXISTS `raptor_ticket_qa_evaluation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_qa_evaluation` (
  `siteid` int(10) unsigned NOT NULL COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL COMMENT 'Ticket identifier within the site',
  `workflow_state` varchar(2) NOT NULL COMMENT 'Workflow state of evaluated ticket',
  `criteria_shortname` varchar(32) NOT NULL COMMENT 'The criteria evaluated',
  `criteria_version` int(10) unsigned NOT NULL COMMENT 'The version number of the criteria',
  `criteria_score` int(10) unsigned NOT NULL COMMENT 'The score assigned on the criteria',
  `comment` varchar(2048) DEFAULT '' COMMENT 'The comment associated with this evaluation',
  `author_uid` int(10) unsigned NOT NULL COMMENT 'The user that evaluated the ticket',
  `evaluation_dt` datetime NOT NULL COMMENT 'When this ticket was evaluated',
  PRIMARY KEY (`siteid`,`IEN`,`author_uid`,`criteria_shortname`,`evaluation_dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Quality assurance evaluation of a ticket';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_qa_evaluation`
--

LOCK TABLES `raptor_ticket_qa_evaluation` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_qa_evaluation` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_qa_evaluation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_qa_notes`
--

DROP TABLE IF EXISTS `raptor_ticket_qa_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_qa_notes` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket quality assurance notes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_qa_notes`
--

LOCK TABLES `raptor_ticket_qa_notes` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_qa_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_qa_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_suspend_notes`
--

DROP TABLE IF EXISTS `raptor_ticket_suspend_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_suspend_notes` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket suspension notes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_suspend_notes`
--

LOCK TABLES `raptor_ticket_suspend_notes` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_suspend_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_suspend_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_tracking`
--

DROP TABLE IF EXISTS `raptor_ticket_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_tracking` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `workflow_state` varchar(2) NOT NULL DEFAULT 'AC' COMMENT 'Current workflow state (assume AC if null)',
  `suspended_dt` datetime DEFAULT NULL COMMENT 'Null if this ticket has not been suspended',
  `approved_dt` datetime DEFAULT NULL COMMENT 'When the protocol was approved',
  `acknowledged_dt` datetime DEFAULT NULL COMMENT 'When the approved protocol was acknowledged',
  `exam_completed_dt` datetime DEFAULT NULL COMMENT 'When this exam was completed',
  `interpret_completed_dt` datetime DEFAULT NULL COMMENT 'When the interpretation was completed',
  `qa_completed_dt` datetime DEFAULT NULL COMMENT 'When the QA was completed',
  `exam_details_committed_dt` datetime DEFAULT NULL COMMENT 'When the exam details were committed to VistA',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`siteid`,`IEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket Tracking';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_tracking`
--

LOCK TABLES `raptor_ticket_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_unsuspend_notes`
--

DROP TABLE IF EXISTS `raptor_ticket_unsuspend_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_unsuspend_notes` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `notes_tx` varchar(1024) NOT NULL DEFAULT '' COMMENT 'notes text',
  `author_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user that created these notes',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket unsuspension notes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_unsuspend_notes`
--

LOCK TABLES `raptor_ticket_unsuspend_notes` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_unsuspend_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_unsuspend_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_ticket_workflow_history`
--

DROP TABLE IF EXISTS `raptor_ticket_workflow_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_ticket_workflow_history` (
  `siteid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ID for the site',
  `IEN` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Ticket identifier within the site',
  `initiating_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The user causing the state change',
  `old_workflow_state` varchar(2) NOT NULL COMMENT 'The ticket workflow state before change',
  `new_workflow_state` varchar(2) NOT NULL COMMENT 'The ticket workflow state after change',
  `collaborate_uid` int(10) unsigned DEFAULT NULL COMMENT 'The user which is collaborating on this ticket',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Ticket workflow history';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_ticket_workflow_history`
--

LOCK TABLES `raptor_ticket_workflow_history` WRITE;
/*!40000 ALTER TABLE `raptor_ticket_workflow_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_ticket_workflow_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_activity_tracking`
--

DROP TABLE IF EXISTS `raptor_user_activity_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_activity_tracking` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Must match the uid in drupal',
  `action_cd` int(10) unsigned NOT NULL COMMENT 'Action code (1=login, 2=logout, 3=interaction)',
  `ipaddress` varchar(40) DEFAULT NULL COMMENT 'The user ip address',
  `sessionid` varchar(200) DEFAULT NULL COMMENT 'The user session id',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  KEY `main` (`updated_dt`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track some basic user actions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_activity_tracking`
--

LOCK TABLES `raptor_user_activity_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_user_activity_tracking` DISABLE KEYS */;
INSERT INTO `raptor_user_activity_tracking` VALUES (1,3,'::1','DQSGwXCdhr-FW7f9baRyGQMuJNUBF870C2Z_g0roX9U','2015-10-02 14:52:22'),(1,3,'::1','DQSGwXCdhr-FW7f9baRyGQMuJNUBF870C2Z_g0roX9U','2015-10-02 14:52:25'),(1,1,'192.168.33.1','xxgxyEMQiiff39SS_oo0b7QzT2nSJPTD83uhsMa2tUQ','2016-02-01 10:41:53'),(1,3,'192.168.33.1','zfQiRVynonLSgWXJBYYKhun_3Z3KxizGFptLgetpLJ4','2016-02-01 10:41:54'),(1,3,'192.168.33.1','zfQiRVynonLSgWXJBYYKhun_3Z3KxizGFptLgetpLJ4','2016-02-01 10:41:54'),(1,3,'192.168.33.1','zfQiRVynonLSgWXJBYYKhun_3Z3KxizGFptLgetpLJ4','2016-02-01 10:41:54'),(1,3,'192.168.33.1','zfQiRVynonLSgWXJBYYKhun_3Z3KxizGFptLgetpLJ4','2016-02-01 10:41:55');
/*!40000 ALTER TABLE `raptor_user_activity_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_anatomy`
--

DROP TABLE IF EXISTS `raptor_user_anatomy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_anatomy` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Must match the uid in drupal',
  `weightgroup` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Group 1 has the most weight',
  `keyword` varchar(32) NOT NULL DEFAULT '' COMMENT 'Specialization keyword',
  `specialist_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user is a specialist in this area',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`weightgroup`,`keyword`,`specialist_yn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Anatomy specializations of users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_anatomy`
--

LOCK TABLES `raptor_user_anatomy` WRITE;
/*!40000 ALTER TABLE `raptor_user_anatomy` DISABLE KEYS */;
INSERT INTO `raptor_user_anatomy` VALUES (39,1,'HEAD',0,'2015-03-15 23:55:25'),(39,1,'HEAD',1,'2015-03-15 23:55:25'),(39,2,'BODY',0,'2015-03-15 23:55:25'),(39,2,'BODY',1,'2015-03-15 23:55:25'),(39,3,'NM',1,'2015-03-15 23:55:25'),(40,1,'CHEST',1,'2014-07-09 14:10:58'),(40,1,'MR',1,'2014-07-09 14:10:58'),(41,1,'CHEST',0,'2014-07-14 18:15:05'),(41,1,'CT',1,'2014-07-14 18:15:05'),(41,1,'HEAD',0,'2014-07-14 18:15:05'),(41,1,'THORAX',0,'2014-07-14 18:15:05'),(41,1,'THORAX',1,'2014-07-14 18:15:05'),(41,2,'CT',0,'2014-07-14 18:15:05'),(41,2,'MR',0,'2014-07-14 18:15:05'),(42,1,'HEAD',0,'2015-07-07 21:57:16'),(42,1,'HEAD',1,'2015-07-07 21:57:16'),(42,1,'NECK',0,'2015-07-07 21:57:16'),(42,1,'THORAX',0,'2015-07-07 21:57:16'),(42,1,'THORAX',1,'2015-07-07 21:57:16'),(42,2,'SPINE',0,'2015-07-07 21:57:16'),(42,3,'LEG',1,'2015-07-07 21:57:16'),(43,1,'HEAD',0,'2015-07-07 21:57:03'),(43,1,'NECK',0,'2015-07-07 21:57:03'),(44,1,'HEAD',0,'2014-07-14 16:18:18'),(44,1,'HEAD',1,'2014-07-14 16:18:18'),(45,1,'HEAD',0,'2014-07-13 20:17:00'),(45,2,'NECK',0,'2014-07-13 20:17:00'),(45,3,'NM',0,'2014-07-13 20:17:00'),(46,1,'HEAD',0,'2014-07-13 20:17:29'),(46,1,'NM',0,'2014-07-13 20:17:29'),(46,2,'MR',0,'2014-07-13 20:17:29'),(46,3,'FL',0,'2014-07-13 20:17:29'),(47,1,'CHEST',0,'2014-06-30 18:48:54'),(47,1,'HEAD',0,'2014-06-30 18:48:54'),(47,1,'NECK',0,'2014-06-30 18:48:54'),(47,1,'SPINE',0,'2014-06-30 18:48:54'),(47,2,'FOOT',0,'2014-06-30 18:48:54'),(48,1,'HEAD',0,'2014-07-13 11:54:46'),(48,1,'NM',0,'2014-07-13 11:54:46'),(48,2,'SPINE',0,'2014-07-13 11:54:46'),(48,3,'US',0,'2014-07-13 11:54:46'),(49,1,'CT',0,'2014-08-18 15:18:05'),(49,1,'HEAD',0,'2014-08-18 15:18:05'),(49,2,'MR',0,'2014-08-18 15:18:05'),(49,3,'NM',0,'2014-08-18 15:18:05'),(50,1,'HEAD',0,'2014-07-14 16:27:54'),(51,1,'CT',0,'2014-07-13 19:55:49'),(51,1,'HEAD',0,'2014-07-13 19:55:49'),(51,1,'HEAD',1,'2014-07-13 19:55:49'),(51,1,'THORAX',0,'2014-07-13 19:55:49'),(51,2,'NM',0,'2014-07-13 19:55:49'),(51,3,'LIVER',0,'2014-07-13 19:55:49'),(52,1,'HEAD',1,'2014-08-31 19:54:28'),(55,1,'KNEE',1,'2014-07-14 20:14:23'),(61,1,'CHEST',0,'2014-07-13 20:19:40'),(61,1,'HEAD',0,'2014-07-13 20:19:40'),(61,1,'NECK',0,'2014-07-13 20:19:40'),(61,1,'NECK',1,'2014-07-13 20:19:40'),(61,1,'SPINE',0,'2014-07-13 20:19:40'),(61,2,'FEMUR',0,'2014-07-13 20:19:40'),(62,1,'CHEST',0,'2014-07-13 20:15:16'),(62,1,'HEAD',0,'2014-07-13 20:15:16'),(62,1,'HEAD',1,'2014-07-13 20:15:16'),(62,2,'CHEST',1,'2014-07-13 20:15:16'),(62,2,'CT',0,'2014-07-13 20:15:16'),(62,3,'LIVER',1,'2014-07-13 20:15:16'),(62,3,'NM',0,'2014-07-13 20:15:16'),(63,1,'THORAX',0,'2014-07-14 20:15:21');
/*!40000 ALTER TABLE `raptor_user_anatomy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_anatomy_override`
--

DROP TABLE IF EXISTS `raptor_user_anatomy_override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_anatomy_override` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Must match the uid in drupal',
  `weightgroup` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Group 1 has the most weight',
  `keyword` varchar(32) NOT NULL DEFAULT '' COMMENT 'Specialization keyword',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`weightgroup`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Override anatomy specializations of users for ranking...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_anatomy_override`
--

LOCK TABLES `raptor_user_anatomy_override` WRITE;
/*!40000 ALTER TABLE `raptor_user_anatomy_override` DISABLE KEYS */;
INSERT INTO `raptor_user_anatomy_override` VALUES (40,1,'ANKLE','2014-08-19 18:08:06'),(40,1,'ELBOW','2014-08-19 18:08:06'),(40,1,'KNEE','2014-08-19 18:08:06'),(40,1,'SHOULDER','2014-08-19 18:08:06'),(40,1,'WRIST','2014-08-19 18:08:06'),(40,2,'ABDOMEN','2014-08-19 18:08:06'),(40,3,'HEAD','2014-08-19 18:08:06'),(40,3,'SPINE','2014-08-19 18:08:06'),(43,1,'HEAD','2014-07-02 19:27:28'),(43,1,'NECK','2014-07-02 19:27:28'),(47,1,'CHEST','2014-06-27 14:42:45'),(47,1,'HEAD','2014-06-27 14:42:45'),(47,1,'NECK','2014-06-27 14:42:45'),(47,1,'SPINE','2014-06-27 14:42:45'),(47,2,'KNEE','2014-06-27 14:42:45'),(47,3,'FOOT','2014-06-27 14:42:45');
/*!40000 ALTER TABLE `raptor_user_anatomy_override` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_group_membership`
--

DROP TABLE IF EXISTS `raptor_user_group_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_group_membership` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Must match the uid in drupal',
  `group_nm` varchar(10) NOT NULL DEFAULT '' COMMENT 'Group in which user is a member',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`group_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Groups to which a user belongs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_group_membership`
--

LOCK TABLES `raptor_user_group_membership` WRITE;
/*!40000 ALTER TABLE `raptor_user_group_membership` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_user_group_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_modality`
--

DROP TABLE IF EXISTS `raptor_user_modality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_modality` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Must match the uid in drupal',
  `modality_abbr` varchar(2) NOT NULL COMMENT 'Modality abbreviation',
  `specialist_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user is a specialist in this modality',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`modality_abbr`,`specialist_yn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Modalities in which users specialize';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_modality`
--

LOCK TABLES `raptor_user_modality` WRITE;
/*!40000 ALTER TABLE `raptor_user_modality` DISABLE KEYS */;
INSERT INTO `raptor_user_modality` VALUES (39,'CT',1,'2015-03-15 23:55:25'),(39,'MR',1,'2015-03-15 23:55:25'),(40,'NM',0,'2014-07-09 14:10:58'),(41,'CT',1,'2014-07-14 18:15:05'),(41,'FL',0,'2014-07-14 18:15:05'),(41,'IR',1,'2014-07-14 18:15:05'),(41,'MR',1,'2014-07-14 18:15:05'),(41,'NM',0,'2014-07-14 18:15:05'),(41,'US',1,'2014-07-14 18:15:05'),(42,'CT',1,'2015-07-07 21:57:16'),(42,'FL',0,'2015-07-07 21:57:16'),(42,'IR',0,'2015-07-07 21:57:16'),(42,'MR',1,'2015-07-07 21:57:16'),(42,'NM',0,'2015-07-07 21:57:16'),(42,'US',1,'2015-07-07 21:57:16'),(43,'FL',0,'2015-07-07 21:57:03'),(44,'CT',1,'2014-07-14 16:18:18'),(44,'MR',0,'2014-07-14 16:18:18'),(45,'CT',0,'2014-07-13 20:17:00'),(45,'FL',0,'2014-07-13 20:17:00'),(45,'IR',0,'2014-07-13 20:17:00'),(45,'MR',0,'2014-07-13 20:17:00'),(45,'US',0,'2014-07-13 20:17:00'),(46,'CT',0,'2014-07-13 20:17:29'),(46,'MR',0,'2014-07-13 20:17:29'),(46,'NM',0,'2014-07-13 20:17:29'),(46,'US',0,'2014-07-13 20:17:29'),(47,'CT',0,'2014-06-30 18:48:54'),(47,'MR',0,'2014-06-30 18:48:54'),(47,'NM',0,'2014-06-30 18:48:54'),(47,'US',0,'2014-06-30 18:48:54'),(48,'CT',0,'2014-07-13 11:54:46'),(48,'FL',0,'2014-07-13 11:54:46'),(48,'IR',0,'2014-07-13 11:54:46'),(48,'NM',0,'2014-07-13 11:54:46'),(48,'US',0,'2014-07-13 11:54:46'),(49,'CT',0,'2014-08-18 15:18:05'),(49,'FL',0,'2014-08-18 15:18:05'),(49,'IR',0,'2014-08-18 15:18:05'),(49,'MR',0,'2014-08-18 15:18:05'),(49,'NM',0,'2014-08-18 15:18:05'),(49,'US',0,'2014-08-18 15:18:05'),(50,'CT',0,'2014-07-14 16:27:54'),(51,'CT',1,'2014-07-13 19:55:49'),(51,'FL',0,'2014-07-13 19:55:49'),(51,'IR',1,'2014-07-13 19:55:49'),(51,'MR',0,'2014-07-13 19:55:49'),(51,'NM',1,'2014-07-13 19:55:49'),(51,'US',0,'2014-07-13 19:55:49'),(52,'FL',1,'2014-08-31 19:54:28'),(52,'IR',1,'2014-08-31 19:54:28'),(55,'IR',1,'2014-07-14 20:14:23'),(60,'CT',0,'2014-07-13 20:22:46'),(60,'FL',0,'2014-07-13 20:22:46'),(60,'MR',0,'2014-07-13 20:22:46'),(60,'NM',0,'2014-07-13 20:22:46'),(61,'CT',1,'2014-07-13 20:19:40'),(61,'FL',0,'2014-07-13 20:19:40'),(61,'MR',0,'2014-07-13 20:19:40'),(61,'US',0,'2014-07-13 20:19:40'),(62,'CT',1,'2014-07-13 20:15:16'),(62,'IR',0,'2014-07-13 20:15:16'),(62,'MR',1,'2014-07-13 20:15:16'),(62,'US',1,'2014-07-13 20:15:16'),(63,'CT',0,'2014-07-14 20:15:21'),(63,'FL',0,'2014-07-14 20:15:21'),(63,'IR',0,'2014-07-14 20:15:21'),(63,'MR',0,'2014-07-14 20:15:21'),(63,'NM',0,'2014-07-14 20:15:21'),(63,'US',1,'2014-07-14 20:15:21'),(64,'CT',0,'2014-07-14 19:25:36'),(64,'MR',0,'2014-07-14 19:25:36'),(65,'CT',0,'2014-06-30 18:41:29');
/*!40000 ALTER TABLE `raptor_user_modality` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_modality_override`
--

DROP TABLE IF EXISTS `raptor_user_modality_override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_modality_override` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Must match the uid in drupal',
  `modality_abbr` varchar(2) NOT NULL COMMENT 'Modality abbreviation',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`modality_abbr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Override modalities in which users specialize for ranking...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_modality_override`
--

LOCK TABLES `raptor_user_modality_override` WRITE;
/*!40000 ALTER TABLE `raptor_user_modality_override` DISABLE KEYS */;
INSERT INTO `raptor_user_modality_override` VALUES (40,'CT','2014-08-19 18:08:06'),(40,'MR','2014-08-19 18:08:06'),(43,'FL','2014-07-02 19:27:28'),(47,'CT','2014-06-27 14:42:45'),(47,'US','2014-06-27 14:42:45');
/*!40000 ALTER TABLE `raptor_user_modality_override` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_profile`
--

DROP TABLE IF EXISTS `raptor_user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_profile` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Must match the uid in Drupal',
  `username` varchar(128) NOT NULL DEFAULT '' COMMENT 'Must match the username in VA system',
  `role_nm` varchar(32) NOT NULL DEFAULT 'RADIOLOGIST' COMMENT 'The role of this user in RAPTOR',
  `worklist_cols` varchar(2048) DEFAULT NULL COMMENT 'Encoded collection of worklist columns to HIDE (blank means sow all)',
  `usernametitle` varchar(16) DEFAULT '' COMMENT 'Title such as Dr, etc',
  `firstname` varchar(50) DEFAULT '' COMMENT 'First name',
  `lastname` varchar(50) DEFAULT '' COMMENT 'Last name',
  `suffix` varchar(20) DEFAULT '' COMMENT 'Suffix such as PhD, etc',
  `prefemail` varchar(128) DEFAULT '' COMMENT 'Preferred email address',
  `prefphone` varchar(128) DEFAULT '' COMMENT 'Preferred phone number',
  `accountactive_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 0 then account is NOT active',
  `CEUA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can create/edit other accounts',
  `LACE1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can create/edit resident account',
  `SWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can select worklist items for view',
  `PWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can protocol worklist items',
  `APWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can approve protocoled worklist items',
  `SUWI1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can cancel a worklist item',
  `CE1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can complete an examination',
  `QA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can complete a QA examination',
  `SP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit pass box',
  `VREP1` int(11) NOT NULL DEFAULT '1' COMMENT 'If 1 then user can view the department activity report',
  `VREP2` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can view the user activity report',
  `EBO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit protocol/exam note boilerplate options',
  `UNP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can upload new protocols',
  `REP1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can retire existing protocols',
  `DRA1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit default and required attribs of roles',
  `ELCO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit contrast options',
  `ELHO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit hydration options',
  `ELRO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit radioisotope options',
  `ELSO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit sedation options',
  `ELSVO1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit service options',
  `EECC1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit CPRS code metadata',
  `ECIR1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can edit contraindication rules',
  `EERL1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can alter list of examination rooms',
  `EARM1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can alter keywords of at risk medication list',
  `CUT1` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user can create/edit umbrella terms and associate keywords with them',
  `QA2` int(11) DEFAULT '0' COMMENT 'If 1 then user can see all QA results',
  `QA3` int(11) DEFAULT '0' COMMENT 'If 1 then user can edit QA criteria',
  `special_privs` varchar(100) DEFAULT '' COMMENT 'Custom strings for special user priviliges, use semicolon as delimiter',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Main profile record for the user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_profile`
--

LOCK TABLES `raptor_user_profile` WRITE;
/*!40000 ALTER TABLE `raptor_user_profile` DISABLE KEYS */;
INSERT INTO `raptor_user_profile` VALUES (1,'admin','Site Administrator',NULL,'','Site','Admin','','raptordefaultadmin@talkecho.com','',1,1,1,0,0,0,1,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,'','2015-10-02 14:52:16'),(39,'11radiologist','Radiologist',NULL,'Dr.','Fresno','Radiologist','PhD','','',1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,0,1,1,0,0,0,0,0,0,0,'','2015-03-15 23:55:25'),(40,'5radiologist','Radiologist',NULL,'Dr','Portland','Radiologist','','','',1,1,1,1,1,1,0,0,1,1,1,0,0,0,0,0,0,1,0,1,1,0,0,0,0,0,0,0,'','2014-07-09 14:10:58'),(41,'6radiologist','Radiologist','a:1:{i:0;s:9:\"Transport\";}','Dr','Tucson','Radiologist','PhD','','',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,0,0,'','2014-07-14 18:15:05'),(42,'7radiologist','Radiologist','a:1:{i:0;s:9:\"Transport\";}','Dr','Seattle','Radiologist','','radiologist@va.gov','202-123-4567',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,'','2015-07-07 21:57:16'),(43,'01vehu','Radiologist','a:1:{i:0;s:9:\"Transport\";}','','Demo','Vehu','','','',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,0,0,1,'RUN_PERFORMANCE_CHECK=1','2015-07-07 21:57:03'),(44,'15radiologist','Radiologist',NULL,'Dr.','Innovations','Radiologist','','','',1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,1,0,1,1,0,0,0,0,0,0,0,'','2014-07-14 16:18:18'),(45,'1imaging','Resident',NULL,'','Demo','Resident','','','',1,1,1,1,1,0,1,0,0,1,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,'','2014-07-13 20:17:00'),(46,'1provider','Resident',NULL,'','Demo','Resident','','','',1,1,1,1,1,0,1,0,0,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,'','2014-07-13 20:17:29'),(47,'1radiologist','Resident','a:2:{i:0;s:7:\"Urgency\";i:1;s:9:\"Transport\";}','Dr','Seattle','Resident','','','',1,1,1,1,1,0,1,0,0,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,'','2014-06-30 18:48:54'),(48,'25radiologist','Resident','a:1:{i:0;s:9:\"Transport\";}','Dr','Fresno','Resident','','','',1,1,1,1,1,0,1,0,1,1,1,1,0,0,0,0,1,0,0,0,1,0,0,0,0,0,0,0,'','2014-07-13 11:54:46'),(49,'40radiologist','Resident','a:1:{i:0;s:9:\"Transport\";}','Dr.','Tucson','Resident','','','',1,1,1,1,1,1,1,1,1,1,1,1,0,0,1,1,1,0,0,1,1,0,1,0,1,0,0,0,'','2014-08-18 15:18:05'),(50,'14radiologist','Resident','a:1:{i:0;s:9:\"Transport\";}','Dr.','Innovations','Resident','','','',1,1,1,1,1,0,1,0,0,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,'','2014-07-14 16:27:54'),(51,'39radiologist','Resident','a:1:{i:0;s:9:\"Transport\";}','Dr','Portland','Resident','PhD','','',1,1,1,1,1,0,1,0,1,1,1,1,0,0,0,0,1,1,0,0,1,0,0,0,1,0,0,0,'','2014-07-13 19:55:49'),(52,'10radiologist','Scheduler','a:1:{i:0;s:9:\"Transport\";}','Ms.','Fresno','Scheduler','','portland@va.gov','202-123-4567',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,'','2014-08-31 19:54:28'),(53,'1masclerk','Scheduler',NULL,'','Demo','Scheduler','','','',1,1,1,1,1,1,1,0,1,1,1,0,1,1,1,1,1,1,1,1,1,0,1,1,0,0,0,0,'','2014-07-13 20:16:20'),(54,'1programmer','Scheduler',NULL,'Ms','Demo','Scheduler','','','',1,1,1,1,1,1,1,0,1,1,1,0,1,1,1,1,1,1,1,1,1,0,1,1,1,0,0,0,'','2014-06-30 18:42:31'),(55,'4radiologist','Scheduler','a:1:{i:0;s:9:\"Transport\";}','Ms','Seattle','Scheduler','','','',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,'','2014-07-14 20:14:23'),(56,'8radiologist','Scheduler',NULL,'Ms','Portland','Scheduler','','','',1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,0,0,0,0,'','2014-07-12 20:19:10'),(57,'9radiologist','Scheduler','a:1:{i:0;s:9:\"Transport\";}','Ms.','Tucson','Scheduler','','','',1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,0,0,0,0,'','2014-07-12 20:32:10'),(58,'13radiologist','Scheduler',NULL,'Ms.','Innovations','Scheduler','','','',1,1,1,1,1,1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,1,0,1,1,0,0,0,0,'','2014-07-14 16:43:04'),(59,'limitedadmin','Site Administrator',NULL,'','Limited','Admin','','','',1,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,'','2014-06-23 15:55:06'),(60,'12radiologist','Technologist',NULL,'Ms.','Portland','Technologist','M.S.','','',1,1,1,1,1,1,0,1,1,1,1,1,1,1,1,0,0,0,0,0,1,0,0,0,0,0,0,0,'','2014-07-13 20:22:46'),(61,'2radiologist','Technologist',NULL,'Ms','Fresno','Technologist','M.S.','','',1,1,1,1,1,1,0,1,1,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,'','2014-07-13 20:19:40'),(62,'38radiologist','Technologist','a:1:{i:0;s:9:\"Transport\";}','Ms.','Tucson','Technologist','','','',1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,'','2014-07-13 20:15:16'),(63,'3radiologist','Technologist','a:1:{i:0;s:0:\"\";}','','Seattle','Technologist','','','',1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,1,1,0,1,1,1,1,0,0,'','2014-07-14 20:15:21'),(64,'16radiologist','Technologist',NULL,'Ms.','Innovations','Technologist','M.S.','','',1,1,1,1,1,1,0,1,1,1,1,0,0,0,0,0,0,0,0,0,1,0,0,0,1,0,0,0,'','2014-07-14 19:25:36'),(65,'1labtech','Technologist',NULL,'Mr','Demo','Tech','','','',1,1,1,1,1,1,0,1,1,1,0,0,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,'','2014-06-30 18:41:29'),(82,'2masclerk','Technologist',NULL,'Mr','MASCLERK','ONE(2really)','','','',1,0,0,1,0,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,'','2015-03-08 11:56:00');
/*!40000 ALTER TABLE `raptor_user_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_recent_activity_tracking`
--

DROP TABLE IF EXISTS `raptor_user_recent_activity_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_recent_activity_tracking` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Must match the uid in drupal',
  `ipaddress` varchar(40) DEFAULT NULL COMMENT 'The user ip address',
  `sessionid` varchar(200) DEFAULT NULL COMMENT 'The user session id',
  `most_recent_login_dt` datetime DEFAULT NULL COMMENT 'When this record was last updated',
  `most_recent_logout_dt` datetime DEFAULT NULL COMMENT 'When this record was last updated',
  `most_recent_action_dt` datetime DEFAULT NULL COMMENT 'When this record was last updated',
  `most_recent_action_cd` int(10) unsigned DEFAULT NULL COMMENT 'Action code (1=login, 2=logout, 3=interaction)',
  `most_recent_error_dt` datetime DEFAULT NULL COMMENT 'When this record was last updated',
  `most_recent_error_cd` int(10) unsigned DEFAULT NULL COMMENT 'The most recent error code',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Track some basic recent user actions for accounts that...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_recent_activity_tracking`
--

LOCK TABLES `raptor_user_recent_activity_tracking` WRITE;
/*!40000 ALTER TABLE `raptor_user_recent_activity_tracking` DISABLE KEYS */;
INSERT INTO `raptor_user_recent_activity_tracking` VALUES (0,NULL,NULL,NULL,NULL,NULL,NULL,'2016-02-01 10:25:39',5),(1,'192.168.33.1','zfQiRVynonLSgWXJBYYKhun_3Z3KxizGFptLgetpLJ4','2016-02-01 10:41:53',NULL,'2016-02-01 10:41:55',3,'2015-10-02 14:52:25',5);
/*!40000 ALTER TABLE `raptor_user_recent_activity_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_service`
--

DROP TABLE IF EXISTS `raptor_user_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_service` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Must match the uid in drupal',
  `service_nm` varchar(10) NOT NULL COMMENT 'Service name',
  `specialist_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then user is a specialist in this service',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`service_nm`,`specialist_yn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Services which the user provides';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_service`
--

LOCK TABLES `raptor_user_service` WRITE;
/*!40000 ALTER TABLE `raptor_user_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_user_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_user_service_override`
--

DROP TABLE IF EXISTS `raptor_user_service_override`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_user_service_override` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Must match the uid in drupal',
  `service_nm` varchar(10) NOT NULL COMMENT 'Service name',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  PRIMARY KEY (`uid`,`service_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Override default services which the user provides for...';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_user_service_override`
--

LOCK TABLES `raptor_user_service_override` WRITE;
/*!40000 ALTER TABLE `raptor_user_service_override` DISABLE KEYS */;
/*!40000 ALTER TABLE `raptor_user_service_override` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raptor_workflow_state`
--

DROP TABLE IF EXISTS `raptor_workflow_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raptor_workflow_state` (
  `abbr` varchar(2) NOT NULL DEFAULT '' COMMENT 'Workflow state abbreviation',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT 'Workflow state name',
  `description` varchar(2048) NOT NULL DEFAULT '' COMMENT 'Workflow state description',
  PRIMARY KEY (`abbr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Workflow states dictionary';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raptor_workflow_state`
--

LOCK TABLES `raptor_workflow_state` WRITE;
/*!40000 ALTER TABLE `raptor_workflow_state` DISABLE KEYS */;
INSERT INTO `raptor_workflow_state` VALUES ('AC','Active','A ticket is availabe for protocol step.'),('CO','Collaborate','(DEPRECATED) A ticket may have been partially protocoled and someone has been identified for collaboration on it.'),('EC','Exam Completed','Patient examination has been completed'),('IA','Needs Cancel/Replace','The ticket needs cancel/replace.'),('PA','Protocol Acknowledged','The technologist acknowledges all the protocol settings before starting an exam.'),('QA','Quality Assurance','This is the ticket state after all patient care workflow is complete.'),('RV','Review','The protocol settings are ready for review by a patient care specialist that has approval authority.');
/*!40000 ALTER TABLE `raptor_workflow_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_demo_measure`
--

DROP TABLE IF EXISTS `simplerulesengine_demo_measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_demo_measure` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping in a logical way',
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this measure',
  `return_type` varchar(20) NOT NULL COMMENT 'The returned data type',
  `purpose_tx` varchar(1024) NOT NULL COMMENT 'Static text describing purpose of this measure',
  `criteria_tx` varchar(4000) DEFAULT NULL COMMENT 'The measure formula or INPUT if no formula',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this measure record should not be edited',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0 then this measure is not available for use in new expressions',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was updated',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`measure_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A measure used by the simple rules engine';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_demo_measure`
--

LOCK TABLES `simplerulesengine_demo_measure` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_demo_measure` DISABLE KEYS */;
INSERT INTO `simplerulesengine_demo_measure` VALUES ('Demographics','AGE',1,'number','The age of the patient in years','INPUT',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Demographics','GENDER',1,'letter','M if Male, F if Female','INPUT',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Protocol','GIVE_CONTRAST',1,'boolean','True if patient will receive contrast','INPUT',1,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Protocol','GIVE_SEDATION',1,'boolean','True if patient will receive sedation','INPUT',1,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Demographics','IS_AGE_50_OR_LESS',1,'boolean','True if patient is male','AGE <= 50',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Demographics','IS_AGE_60_OR_MORE',1,'boolean','True if patient is male','AGE >= 60',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Demographics','IS_FEMALE',1,'boolean','True if patient is female','GENDER = \"F\"',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Demographics','IS_MALE',1,'boolean','True if patient is male','GENDER = \"M\"',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00');
/*!40000 ALTER TABLE `simplerulesengine_demo_measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_demo_rule`
--

DROP TABLE IF EXISTS `simplerulesengine_demo_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_demo_rule` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping rules in a logical way',
  `rule_nm` varchar(40) NOT NULL COMMENT 'Must be unique',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Increases each time change is saved',
  `summary_msg_tx` varchar(80) NOT NULL COMMENT 'Static summary text to show the user when rule is triggered',
  `msg_tx` varchar(512) NOT NULL COMMENT 'Text to show the user when rule is triggered',
  `explanation` varchar(2048) NOT NULL COMMENT 'Explanation of the rule purpose',
  `req_ack_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 1 then an acknowledgement is required',
  `trigger_crit` varchar(4000) NOT NULL COMMENT 'The criteria that triggers the rule',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this rule record cannot be edited',
  `active_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 0 then this rule is not active',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `created_dt` datetime DEFAULT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`rule_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A simple rules engine rule';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_demo_rule`
--

LOCK TABLES `simplerulesengine_demo_rule` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_demo_rule` DISABLE KEYS */;
INSERT INTO `simplerulesengine_demo_rule` VALUES ('Demographics','ANXIETY_RISK',1,'Anxiety Risk','Patient has reaction to confined spaces','The MRI is a confined space which may provoke anxiety in the patient.',1,'AllFlagsTrue(IS_CLAUSTROPHOBIC,GIVE_MRI)',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Contrast','CONTRAST_ALLERGY',1,'Contrast Risk','Patient has allergy to contrast','Patient has a risk to some or all contrast',1,'AllFlagsTrue(GIVE_CONTRAST,HAS_CONTRAST_ALLERGY)',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Age','CONTRAST_RISK',1,'Contrast Risk','Confirm patient has appropriate renal function','Older patients may be at greater risk for contrast complications',1,'AllFlagsTrue(GIVE_CONTRAST,IS_AGE_60_OR_MORE)',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00'),('Demographics','PREG_RISK',1,'Embryo Risk','Confirm patient is not pregnant','Embryo would be at risk from radiation',1,'AllFlagsTrue(IS_FEMALE,IS_AGE_50_OR_LESS,GIVE_XRAY)',0,1,'2015-10-02 14:52:00','2015-10-02 14:52:00');
/*!40000 ALTER TABLE `simplerulesengine_demo_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_measure`
--

DROP TABLE IF EXISTS `simplerulesengine_measure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_measure` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping in a logical way',
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this measure',
  `return_type` varchar(20) NOT NULL COMMENT 'The returned data type',
  `purpose_tx` varchar(1024) NOT NULL COMMENT 'Static text describing purpose of this measure',
  `criteria_tx` varchar(4000) DEFAULT NULL COMMENT 'The measure formula or INPUT if no formula',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this measure record should not be edited',
  `active_yn` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'If 0 then this measure is not available for use in new expressions',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was updated',
  `created_dt` datetime NOT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`measure_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A measure used by the simple rules engine';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_measure`
--

LOCK TABLES `simplerulesengine_measure` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_measure` DISABLE KEYS */;
/*!40000 ALTER TABLE `simplerulesengine_measure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_measure_question`
--

DROP TABLE IF EXISTS `simplerulesengine_measure_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_measure_question` (
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'The version number of this measure',
  `question_significance` int(10) unsigned NOT NULL DEFAULT '50' COMMENT 'Higher values mean the question is more signficant for presentation to user than lower value questions',
  `question_type_cd` char(2) NOT NULL COMMENT 'TX=Simple text, NM=Simple number, TF=True/False, YN=Yes/No, MC=Multiple Choice, SC=Single Choice',
  `answer_limit` int(10) unsigned DEFAULT NULL COMMENT 'Length for text answers, count for multiple choice answers, ignored for others',
  `question_tx` varchar(256) NOT NULL COMMENT 'Static text question for user to answer',
  `explanation_tx` varchar(1024) NOT NULL COMMENT 'Static text explainin the question in more detail',
  PRIMARY KEY (`measure_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A measure question for a user interface to present';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_measure_question`
--

LOCK TABLES `simplerulesengine_measure_question` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_measure_question` DISABLE KEYS */;
/*!40000 ALTER TABLE `simplerulesengine_measure_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_measure_question_choices`
--

DROP TABLE IF EXISTS `simplerulesengine_measure_question_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_measure_question_choices` (
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `position` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Relative position of this choice in the presentation',
  `show_tx` varchar(256) DEFAULT NULL COMMENT 'Static text to show the user as the choice',
  `choice_tx` varchar(128) NOT NULL COMMENT 'The choice value',
  PRIMARY KEY (`measure_nm`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A collection of choices for a measure question';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_measure_question_choices`
--

LOCK TABLES `simplerulesengine_measure_question_choices` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_measure_question_choices` DISABLE KEYS */;
/*!40000 ALTER TABLE `simplerulesengine_measure_question_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_measure_question_validation`
--

DROP TABLE IF EXISTS `simplerulesengine_measure_question_validation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_measure_question_validation` (
  `measure_nm` varchar(40) NOT NULL COMMENT 'The measure name',
  `regex` varchar(1024) DEFAULT NULL COMMENT 'If not null, then answer must match this regex',
  `maxfloat` float DEFAULT NULL COMMENT 'If not null, then answer must be a number less than or equal to this',
  `minfloat` float DEFAULT NULL COMMENT 'If not null, then answer must be a number more than or equal to this',
  `maxint` int(11) DEFAULT NULL COMMENT 'If not null, then answer must be a number less than or equal to this',
  `minint` int(11) DEFAULT NULL COMMENT 'If not null, then answer must be a number more than or equal to this',
  PRIMARY KEY (`measure_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Validation for a question answer';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_measure_question_validation`
--

LOCK TABLES `simplerulesengine_measure_question_validation` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_measure_question_validation` DISABLE KEYS */;
/*!40000 ALTER TABLE `simplerulesengine_measure_question_validation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simplerulesengine_rule`
--

DROP TABLE IF EXISTS `simplerulesengine_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simplerulesengine_rule` (
  `category_nm` varchar(20) NOT NULL COMMENT 'Simply for grouping rules in a logical way',
  `rule_nm` varchar(40) NOT NULL COMMENT 'Must be unique',
  `version` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Increases each time change is saved',
  `summary_msg_tx` varchar(80) NOT NULL COMMENT 'Static summary text to show the user when rule is triggered',
  `msg_tx` varchar(512) NOT NULL COMMENT 'Text to show the user when rule is triggered',
  `explanation` varchar(2048) NOT NULL COMMENT 'Explanation of the rule purpose',
  `req_ack_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 1 then an acknowledgement is required',
  `trigger_crit` varchar(4000) NOT NULL COMMENT 'The criteria that triggers the rule',
  `readonly_yn` int(11) NOT NULL DEFAULT '0' COMMENT 'If 1 then this rule record cannot be edited',
  `active_yn` int(11) NOT NULL DEFAULT '1' COMMENT 'If 0 then this rule is not active',
  `updated_dt` datetime NOT NULL COMMENT 'When this record was last updated',
  `created_dt` datetime DEFAULT NULL COMMENT 'When this record was created',
  PRIMARY KEY (`rule_nm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A simple rules engine rule';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simplerulesengine_rule`
--

LOCK TABLES `simplerulesengine_rule` WRITE;
/*!40000 ALTER TABLE `simplerulesengine_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `simplerulesengine_rule` ENABLE KEYS */;
UNLOCK TABLES;


-- keeping this because it has vista specific account information... 
-- Dumping data for table `url_alias`
--

LOCK TABLES `url_alias` WRITE;
/*!40000 ALTER TABLE `url_alias` DISABLE KEYS */;
INSERT INTO `url_alias` VALUES (1,'user/1','users/admin','und'),(2,'node/1','content/welcome-your-new-acquia-drupal-website','und'),(3,'user/2','users/11radiologist','und'),(4,'user/3','users/5radiologist','und'),(5,'user/4','users/6radiologist','und'),(6,'user/5','users/7radiologist','und'),(7,'user/6','users/01vehu','und'),(8,'user/7','users/15radiologist','und'),(9,'user/8','users/1imaging','und'),(10,'user/9','users/1provider','und'),(11,'user/10','users/1radiologist','und'),(12,'user/11','users/25radiologist','und'),(13,'user/12','users/40radiologist','und'),(14,'user/13','users/14radiologist','und'),(15,'user/14','users/39radiologist','und'),(16,'user/15','users/10radiologist','und'),(17,'user/16','users/1masclerk','und'),(18,'user/17','users/1programmer','und'),(19,'user/18','users/4radiologist','und'),(20,'user/19','users/8radiologist','und'),(21,'user/20','users/9radiologist','und'),(22,'user/21','users/13radiologist','und'),(23,'user/22','users/limitedadmin','und'),(24,'user/23','users/12radiologist','und'),(25,'user/24','users/2radiologist','und'),(26,'user/25','users/38radiologist','und'),(27,'user/26','users/3radiologist','und'),(28,'user/27','users/16radiologist','und'),(29,'user/28','users/1labtech','und'),(30,'user/29','users/asu1','und'),(31,'user/30','users/aau1','und'),(32,'user/31','users/aaarad1','und'),(33,'user/32','users/ar2','und'),(34,'user/33','users/ar3','und'),(35,'user/34','users/ar4','und'),(36,'user/35','users/atech1','und'),(37,'user/36','users/sa11','und'),(38,'user/37','users/asched1','und'),(39,'user/38','users/as12','und'),(40,'user/39','users/11radiologist-0','und'),(41,'user/40','users/5radiologist-0','und'),(42,'user/41','users/6radiologist-0','und'),(43,'user/42','users/7radiologist-0','und'),(44,'user/43','users/01vehu-0','und'),(45,'user/44','users/15radiologist-0','und'),(46,'user/45','users/1imaging-0','und'),(47,'user/46','users/1provider-0','und'),(48,'user/47','users/1radiologist-0','und'),(49,'user/48','users/25radiologist-0','und'),(50,'user/49','users/40radiologist-0','und'),(51,'user/50','users/14radiologist-0','und'),(52,'user/51','users/39radiologist-0','und'),(53,'user/52','users/10radiologist-0','und'),(54,'user/53','users/1masclerk-0','und'),(55,'user/54','users/1programmer-0','und'),(56,'user/55','users/4radiologist-0','und'),(57,'user/56','users/8radiologist-0','und'),(58,'user/57','users/9radiologist-0','und'),(59,'user/58','users/13radiologist-0','und'),(60,'user/59','users/limitedadmin-0','und'),(61,'user/60','users/12radiologist-0','und'),(62,'user/61','users/2radiologist-0','und'),(63,'user/62','users/38radiologist-0','und'),(64,'user/63','users/3radiologist-0','und'),(65,'user/64','users/16radiologist-0','und'),(66,'user/65','users/1labtech-0','und'),(67,'user/66','users/admintest1','und'),(68,'user/67','users/testadmin1','und'),(69,'user/68','users/testadmin2','und'),(70,'user/69','users/testrad1','und'),(71,'user/70','users/testres1','und'),(72,'user/71','users/testa1','und'),(73,'user/72','users/testtec1','und'),(74,'user/73','users/testa1-0','und'),(75,'user/74','users/testrad2','und'),(76,'user/75','users/testrad11','und'),(77,'user/76','users/testrad1-0','und'),(78,'user/77','users/testsched1','und'),(79,'user/78','users/testadmin271','und'),(80,'user/79','users/testarad272','und'),(81,'user/80','users/testres12','und'),(82,'user/81','users/testarad1','und'),(83,'user/82','users/2masclerk','und'),(84,'user/83','users/mytechtest1','und'),(85,'user/84','users/mytechtest2','und'),(86,'user/85','users/5039eht','und'),(87,'user/86','users/zz0123','und');
/*!40000 ALTER TABLE `url_alias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Primary Key: Unique user ID.',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT 'Unique user name.',
  `pass` varchar(128) NOT NULL DEFAULT '' COMMENT 'User’s password (hashed).',
  `mail` varchar(254) DEFAULT '' COMMENT 'User’s e-mail address.',
  `theme` varchar(255) NOT NULL DEFAULT '' COMMENT 'User’s default theme.',
  `signature` varchar(255) NOT NULL DEFAULT '' COMMENT 'User’s signature.',
  `signature_format` varchar(255) DEFAULT NULL COMMENT 'The filter_format.format of the signature.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for when user was created.',
  `access` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for previous time user accessed the site.',
  `login` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp for user’s last login.',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Whether the user is active(1) or blocked(0).',
  `timezone` varchar(32) DEFAULT NULL COMMENT 'User’s time zone.',
  `language` varchar(12) NOT NULL DEFAULT '' COMMENT 'User’s default language.',
  `picture` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key: file_managed.fid of user’s picture.',
  `init` varchar(254) DEFAULT '' COMMENT 'E-mail address used for initial account creation.',
  `data` longblob COMMENT 'A serialized array of name value pairs that are related to the user. Any form values posted during user edit are stored and are loaded into the $user object during user_load(). Use of this field is discouraged and it will likely disappear in a future...',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name` (`name`),
  KEY `access` (`access`),
  KEY `created` (`created`),
  KEY `mail` (`mail`),
  KEY `picture` (`picture`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores user data.';
/*!40101 SET character_set_client = @saved_cs_client */;

-- keeping this table because it has users associated with RAPTOR and VistA Access
--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (0,'','','','','',NULL,0,0,0,0,NULL,'',0,'',NULL),(1,'admin','$S$DVa/VtEBczE7eAr52GL8uQQyDnro4Zt9FKtL62PXOpKQZhjwJENy','support@room4me.com','','',NULL,1417212211,1454341313,1454341313,1,'America/New_York','',0,'support@room4me.com','b:0;'),(29,' ASU1','','a@b.com','','',NULL,1418570151,0,0,1,NULL,'',0,'email address',NULL),(31,' AAARAD1','','','','',NULL,1418831768,0,0,1,NULL,'',0,'email address',NULL),(32,' AR2','','','','',NULL,1418832563,0,0,1,NULL,'',0,'email address',NULL),(33,' AR3','$S$D0QDzzrIBbTcefZnDwz4bG5ryl7djjyafZWZpkqYzNy34OkKdPhR','','','',NULL,1418832864,0,0,1,NULL,'',0,'email address',NULL),(34,' ar4','$S$Dyd7VxYA9HHzy.Hmp.ZXAZyPbhyGStnOnEYLNxBo2TJ1mbjyiAyM','','','',NULL,1418832957,0,0,1,NULL,'',0,'email address',NULL),(35,' ATech1','$S$DPobFjHCckB0N.TJjCXpV6R.E0Ok6H0mSqz1F/paLvXu69b1UDZE','','','',NULL,1418833083,0,0,1,NULL,'',0,'email address',NULL),(37,' ASched1','$S$D0WaCLH33JF8qbOcUwWGpQmC5Zlyb60kOjFx0oqt4QbI/Gwymazc','','','',NULL,1418833342,0,0,1,NULL,'',0,'email address',NULL),(39,'11radiologist','$S$D4lIdFywoJhqfT85rB/yCnxsSDhr7QaQC4f3SzzyIWfr960RN0OO','my email','','',NULL,1418917590,1432221091,1432220094,1,NULL,'',0,'my init',NULL),(40,'5radiologist','$S$D3RJR4.c4yodZjLbKoSdHJZDYREGZYjsW.LcMFFrL6v4MfPLSsoH','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(41,'6radiologist','$S$DW6m1mq4y1mutnmt.RLrNIo3qdSwImb.CJ2KTa4L.z7Nyz7cxrM3','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(42,'7radiologist','$S$D46kTD6zZvpP8AK1WWXgsyhfR/RrNln/EJmByBXAqd4Z54wgWQlu','my email','','',NULL,1418917590,1443648768,1443648768,1,NULL,'',0,'my init',NULL),(43,'01vehu','$S$DSER0RCen16FPY74pF.uexTVHj13PkWfR9Sh4mvrJlZRZgue7ZzP','my email','','',NULL,1418917590,1443721615,1443719831,1,NULL,'',0,'my init',NULL),(44,'15radiologist','$S$D3mGvbF5Z4kiZ99acvYoXpcJ/SE2RMyXiZlWFIj2F3Qk7nVNKgrX','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(45,'1imaging','$S$DBUF26Wp7dA4cPp.8UB2rexv5ENVGjTx7nWVaAHi.xe15g7k8Hsb','my email','','',NULL,1418917590,1418917590,1418917590,1,NULL,'',0,'my init',NULL),(46,'1provider','$S$DH2HXTZZ4KVNwgIxx8PgA20IE7iXyuDteVFcAgag0U32mJoXdEtd','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(47,'1radiologist','$S$DnlfROAl12uOuYnD9n.hwubPf1R.XioIRau4jqPXzmDpWMCJ31u.','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(48,'25radiologist','$S$DY8.d2k8e1Z.EbqVn6HPGnK9p3w2C2vTAW6EEKnhRTHGX9G2IsvY','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(49,'40radiologist','$S$D6oDyYQ8U2A05GnB8gr8fY5NKIrdFCdxBZpRQGlI8Flu2vpm1giH','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(50,'14radiologist','$S$DuFXNjMoA0VhISS.Z1XGwlgGGHBx0PDXvVPX8AqCBupr4GoYactk','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(51,'39radiologist','$S$DcGKd.bGOwvCbL0M6HjxvEto.gY7Wza.kuBptLpgNAUGfmqtT91v','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(52,'10radiologist','$S$Dp4ICOiAY/XgRWr4LkZgnzOmk3jAkK3AoZsDbH0l9.sVbwB/LiZP','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(53,'1masclerk','$S$Dul5vXG0UzEeAwc9Nboxp0FQ2rcFoZNtjxRIaWiDAv4uxAMGU7vB','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(54,'1programmer','$S$D4PMUnJ5kmic.Vqi.7Og2qn3JhgUc2NxjzNc32Sr5mfUf2Sh5/ek','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(55,'4radiologist','$S$DIIlcZZ1.NIRGto4GCrjnTc9beG3.l32oNJeDBv/UDocDwRyQ9i2','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(56,'8radiologist','$S$D1gfUTsCbC.dVn.Zv2sATF3OUXNBCrOK9LAMzg4IPCzkCrMRa3fj','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(57,'9radiologist','$S$DMbWSEiL/Ve9qgZtLOGi00eTdGm/le/rJzNCLICMRWVzMcnK53rR','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(58,'13radiologist','$S$DEOSHw5BVQeKWAbq69aMncMGX1nAwYefeP4VVjdPAWucNMCqTzCY','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(59,'limitedadmin','$S$DTOPmFZ5o8yqtDkUC7i5ZLp534XPyxz9HR.gnQ8G1Eco09rEUVKu','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(60,'12radiologist','$S$DR/cBLSRCnQhlnMewc8ef5qfZZQCmePLN6Teo5cOw8C4NCiTjUuf','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(61,'2radiologist','$S$D8C.JVzSXey/UXXkZH.HET8lxBnnHNRvCRxOCIC1zLZEjHckM47Y','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(62,'38radiologist','$S$Dsd.FyX8I0ti8eY2inOhlvY/7r8aBYgQzsJs1AWmFBFhOSrrSFda','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(63,'3radiologist','$S$DoP.YKfM/j.7j.g83O2DeifmjMKIK0e.SXP0Y02/qzaDol3NYNVr','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(64,'16radiologist','$S$DjQx3gpUjAg7jmkFMF5wPgWZYdT4KShIlmokZnBO.fMbSf8QccG5','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(65,'1labtech','$S$DEag6YtsKMqBhDbFf6HMIwHSmZY9wgjDHVkTEQ4fMKwKeMEt3iXn','my email','','',NULL,1418917590,0,0,1,NULL,'',0,'my init',NULL),(72,' testtec1','$S$DbwZh9Mt9bSqEp2S1EAXtDmh0.zeYnWOTbkcPemibH1lKSSmnaeh','','','',NULL,1419435886,0,0,1,NULL,'',0,'email address',NULL),(74,'testrad2','$S$DI20xO.uQGGKZRCzhEgYGWw2MUe6pQev6nVdFuRQJlW3eP94aIlJ','','','',NULL,1419446175,0,0,1,NULL,'',0,'email address',NULL),(77,' testsched1','$S$DxDGB/T2aJ7BKFYUfaIukSD2Tnk4Pbt/.4jZ1APCL2ljcvZkyuN2','','','',NULL,1419696093,0,0,1,NULL,'',0,'email address',NULL),(78,' testadmin271','$S$DjAvUcBSsX.GQY6CKYcpb3jizr1/wqs68gzJ0R13T8Fst6gs.DJ9','','','',NULL,1419697227,0,0,1,NULL,'',0,'email address',NULL),(79,' testarad272','$S$DnFvGrS8QIAQIlrloNXQGWKKZ5wdGvPXzorWrQN7dWvilZK.9rml','','','',NULL,1419699439,0,0,1,NULL,'',0,'email address',NULL),(80,' testres12','$S$D.706oB4bB7dWm/oV1g419EYNMw6KGKgvqCVGGJbHCgkTeem6MtL','','','',NULL,1419700743,0,0,1,NULL,'',0,'email address',NULL),(81,'testarad1','$S$D3OmMayZukA4RDhjtzNbVfsxdbHZoZbF2dx0o.o8OH3UmWjLeunI','','','',NULL,1420208869,0,0,1,NULL,'',0,'email address',NULL),(82,'2masclerk','$S$DQQ1.rTKn/pHaemSLUVVnhW9uY1beKC0iJ4lkDsBV2bJKquECip4','','','',NULL,1424959577,1429734924,1429734933,1,NULL,'',0,'email address',NULL),(83,'mytechtest1','$S$D4H7/KrZuWv8zXV3v7iazDd4WTP0p3WjDjPcqkWYELO6ASRZs2Eu','','','',NULL,1424997086,0,0,1,NULL,'',0,'email address',NULL),(84,'mytechtest2','$S$Dpt/VJAa89IMcclOMqx0KzUW9p5H3c9LTk8mGKTf4C18/cjKgqS3','','','',NULL,1424997192,0,0,1,NULL,'',0,'email address',NULL),(85,'5039EHT','$S$DK7yVDFRmTxVr.kAt6yzZjrrR5SW6JLyy5Gct8CSH3KHBJ9xdczV','','','',NULL,1431962707,1432062564,1432059918,1,NULL,'',0,'email address',NULL),(86,'zz0123','$S$DgTsU6MZ84wS8Re5pyXulME/p.icgOP0TzLcIRJvVNUbxhJLkp4n','','','',NULL,1431964251,1431966671,1431964655,1,NULL,'',0,'email address',NULL),(88,'aaa','$S$DbFSPntsbVVrw7Z8Hk0iJtz.pTrXTAAK.cWiCT0KU1yJczSaOWo.','','','',NULL,1436276297,0,0,1,NULL,'',0,'email address',NULL),(91,'aaaa1','','','','',NULL,1436566593,0,0,1,NULL,'',0,'email address',NULL),(92,'aaa2','$S$DblO4tyCVG33I3x6E42zkTaKEK.lIzMoH7SJo79HTLrp.GvFLGnM','','','',NULL,1436566702,1436568291,1436566755,1,NULL,'',0,'email address',NULL),(93,'aaa3','$S$D8y2wb0dvoQWVkcTCaRL39wOuHU7txHcXCGUDTvlew/vZg/XGed9','','','',NULL,1436566982,0,0,1,NULL,'',0,'email address',NULL),(94,'aaa4','$S$DC5f6Nu.Xmm9LzwbnzHECk5CRXRdUimDi7FrJcViuX1zY.42CbRj','','','',NULL,1436567316,0,0,1,NULL,'',0,'email address',NULL),(95,'atestuser1','$S$DkLxqUwSKIm4c75Jw/WvAps2JeIn36dWyxFC7GLhgcZoIqfgW0XW','','','',NULL,1436567355,0,0,1,NULL,'',0,'email address',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `views_display`
--

DROP TABLE IF EXISTS `views_display`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `views_display` (
  `vid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The view this display is attached to.',
  `id` varchar(64) NOT NULL DEFAULT '' COMMENT 'An identifier for this display; usually generated from the display_plugin, so should be something like page or page_1 or block_2, etc.',
  `display_title` varchar(64) NOT NULL DEFAULT '' COMMENT 'The title of the display, viewable by the administrator.',
  `display_plugin` varchar(64) NOT NULL DEFAULT '' COMMENT 'The type of the display. Usually page, block or embed, but is pluggable so may be other things.',
  `position` int(11) DEFAULT '0' COMMENT 'The order in which this display is loaded.',
  `display_options` longtext COMMENT 'A serialized array of options for this display; it contains options that are generally only pertinent to that display plugin type.',
  PRIMARY KEY (`vid`,`id`),
  KEY `vid` (`vid`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores information about each display attached to a view.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `views_display`
--

LOCK TABLES `views_display` WRITE;
/*!40000 ALTER TABLE `views_display` DISABLE KEYS */;
/*!40000 ALTER TABLE `views_display` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `views_view`
--

DROP TABLE IF EXISTS `views_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `views_view` (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The view ID of the field, defined by the database.',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT 'The unique name of the view. This is the primary field views are loaded from, and is used so that views may be internal and not necessarily in the database. May only be alphanumeric characters plus underscores.',
  `description` varchar(255) DEFAULT '' COMMENT 'A description of the view for the admin interface.',
  `tag` varchar(255) DEFAULT '' COMMENT 'A tag used to group/sort views in the admin interface',
  `base_table` varchar(64) NOT NULL DEFAULT '' COMMENT 'What table this view is based on, such as node, user, comment, or term.',
  `human_name` varchar(255) DEFAULT '' COMMENT 'A human readable name used to be displayed in the admin interface',
  `core` int(11) DEFAULT '0' COMMENT 'Stores the drupal core version of the view.',
  PRIMARY KEY (`vid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores the general data for a view.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `views_view`
--

LOCK TABLES `views_view` WRITE;
/*!40000 ALTER TABLE `views_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `views_view` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-02-01 15:44:06
