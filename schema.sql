-- MySQL dump 10.13  Distrib 5.7.42, for Linux (x86_64)
--
-- Host: 10.10.10.12    Database: payglobe
-- ------------------------------------------------------
-- Server version	5.7.29-0ubuntu0.18.04.1

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
-- Table structure for table `Codice_esercente`
--

DROP TABLE IF EXISTS `Codice_esercente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Codice_esercente` (
  `TerminalID` varchar(45) DEFAULT NULL,
  `sia_pagobancomat` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acquirer`
--

DROP TABLE IF EXISTS `acquirer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acquirer` (
  `codice` varchar(20) NOT NULL,
  `nome` varchar(45) NOT NULL,
  `contoaccredito` varchar(45) DEFAULT NULL,
  `tid` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `binlist`
--

DROP TABLE IF EXISTS `binlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `binlist` (
  `PAN` text,
  `Circuito` text,
  `BancaEmettitrice` text,
  `LivelloCarta` text,
  `TipoCarta` text,
  `Nazione` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `BUID` int(11) DEFAULT NULL,
  `MERCHANT` text,
  `SHOP_SIGN` text,
  `ADDRESS` text,
  `NOMERIF` text,
  `POSID` int(11) DEFAULT NULL,
  `POS_VERSION` text,
  `ABI` text,
  `VENDOR_PRIV` text,
  `VENDOR_CODE` int(11) DEFAULT NULL,
  `SHOP_CODE` text,
  `VAT_CODE` text,
  `STATE_PV` text,
  `STATE_POS` text,
  `STATE` text,
  `PROFILE` text,
  `CONN` text,
  `CONN_BKUP` text,
  `CONN_TLG` text,
  `POS_TYPE` text,
  `POS_TYPE_CODE` int(11) DEFAULT NULL,
  `POS_MODEL` text,
  `POS_MATRICOLA` text,
  `POS_SWBASE` text,
  `POS_SWAPP` text,
  `PINPAD_MODEL` text,
  `CLESS_POS` text,
  `CLESSPB_PROFILOTEC` text,
  `CLESSCC_PROFILOTEC` text,
  `ACTIVATION_DATE` text,
  `DEACTIVATION_DATE` text,
  `CREATION_DATE` text,
  `CHECK APP` text,
  `CHECK SOFT` text,
  `CONFIGURATION_DATE` text,
  `LASTDLL_DATE` text,
  `LAST_TRX_DATE` text,
  `PRIV` text,
  `IDX` text,
  `BUSINESSUNITID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ftfs_transactions`
--

DROP TABLE IF EXISTS `ftfs_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ftfs_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Trid` bigint(20) DEFAULT NULL,
  `TermId` varchar(255) DEFAULT NULL,
  `Term` varchar(8) DEFAULT NULL,
  `SiaCode` varchar(255) DEFAULT NULL,
  `DtTrans` datetime DEFAULT NULL,
  `ApprNum` varchar(255) DEFAULT NULL,
  `Acid` varchar(255) DEFAULT NULL,
  `Acquirer` varchar(255) DEFAULT NULL,
  `Pan` varchar(255) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Currency` varchar(255) DEFAULT NULL,
  `DtIns` varchar(255) DEFAULT NULL,
  `PointOfService` varchar(255) DEFAULT NULL,
  `Cont` varchar(255) DEFAULT NULL,
  `NumOper` int(11) DEFAULT NULL,
  `DtPos` datetime DEFAULT NULL,
  `PosReq` varchar(255) DEFAULT NULL,
  `PosStan` int(11) DEFAULT NULL,
  `PfCode` varchar(255) DEFAULT NULL,
  `PMrc` varchar(255) DEFAULT NULL,
  `PosAcq` varchar(255) DEFAULT NULL,
  `GtResp` varchar(255) DEFAULT NULL,
  `NumTent` int(11) DEFAULT NULL,
  `TP` varchar(1) DEFAULT NULL,
  `CatMer` varchar(255) DEFAULT NULL,
  `VndId` varchar(255) DEFAULT NULL,
  `PvdId` int(11) DEFAULT NULL,
  `Bin` varchar(255) DEFAULT NULL,
  `Tpc` varchar(255) DEFAULT NULL,
  `VaFl` varchar(255) DEFAULT NULL,
  `FvFl` varchar(255) DEFAULT NULL,
  `TrKey` varchar(255) DEFAULT NULL,
  `CSeq` bigint(20) DEFAULT NULL,
  `Conf` varchar(255) DEFAULT NULL,
  `AutTime` int(11) DEFAULT NULL,
  `DBTime` int(11) DEFAULT NULL,
  `TOTTime` int(11) DEFAULT NULL,
  `DFN` varchar(255) DEFAULT NULL,
  `CED` varchar(255) DEFAULT NULL,
  `TTQ` varchar(255) DEFAULT NULL,
  `FFI` varchar(255) DEFAULT NULL,
  `TCAP` varchar(255) DEFAULT NULL,
  `ISR` varchar(255) DEFAULT NULL,
  `IST` varchar(255) DEFAULT NULL,
  `IAutD` varchar(255) DEFAULT NULL,
  `CryptCurr` varchar(255) DEFAULT NULL,
  `CryptType` varchar(255) DEFAULT NULL,
  `CrypAmnt` varchar(255) DEFAULT NULL,
  `CryptTD` varchar(255) DEFAULT NULL,
  `UN` varchar(255) DEFAULT NULL,
  `CVR` varchar(255) DEFAULT NULL,
  `TVR` varchar(255) DEFAULT NULL,
  `IAD` varchar(255) DEFAULT NULL,
  `CID` varchar(255) DEFAULT NULL,
  `AId` varchar(255) DEFAULT NULL,
  `HATC` varchar(255) DEFAULT NULL,
  `AIP` varchar(255) DEFAULT NULL,
  `ACrypt` varchar(255) DEFAULT NULL,
  `PaymentId` varchar(255) DEFAULT NULL,
  `CCode` varchar(255) DEFAULT NULL,
  `RespAcq` varchar(45) DEFAULT NULL,
  `MeId` varchar(45) DEFAULT NULL,
  `OperExpl` varchar(65) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ftfs_transactions_TermId` (`TermId`),
  KEY `idx_ftfs_transactions_DtPos` (`DtPos`),
  KEY `idx_ftfs_transactions_Trid` (`Trid`),
  KEY `idx_ft_filters` (`TermId`,`Conf`,`TP`,`GtResp`,`DtPos`),
  KEY `idx_ft_termid_dtpos` (`TermId`,`DtPos`),
  KEY `idx_ft_termid` (`TermId`),
  KEY `idx_ftfs_transactions_TermId_DtPos` (`TermId`,`DtPos`),
  KEY `idx_ftfs_transactions_Amount` (`Amount`),
  KEY `idx_ftfs_transactions_SiaCode` (`SiaCode`),
  KEY `idx_ftfs_transactions_MeId` (`MeId`),
  KEY `idx_ftfs_transactions_ApprNum` (`ApprNum`),
  KEY `idx_ftfs_transactions_Pan` (`Pan`),
  KEY `idx_ftfs_transactions_Conf` (`Conf`),
  KEY `idx_ftfs_transactions_GtResp` (`GtResp`),
  KEY `idx_ftfs_transactions_TP` (`TP`),
  KEY `idx_termid_gtresp_tp_dtpos` (`TermId`,`GtResp`,`TP`,`DtPos`),
  KEY `idx_ftfs_MeId_DtPos` (`MeId`,`DtPos`),
  KEY `idx_ftfs_TermId` (`TermId`)
) ENGINE=InnoDB AUTO_INCREMENT=1858999 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `merchants`
--

DROP TABLE IF EXISTS `merchants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchants` (
  `merchantID` varchar(40) NOT NULL,
  `insegna` varchar(45) DEFAULT NULL,
  `localita` varchar(45) DEFAULT NULL,
  `provincia` varchar(4) DEFAULT NULL,
  `ragionesociale` varchar(55) DEFAULT NULL,
  `indirizzo` varchar(60) DEFAULT NULL,
  `cap` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`merchantID`),
  UNIQUE KEY `merchantID_UNIQUE` (`merchantID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mid_acquirer`
--

DROP TABLE IF EXISTS `mid_acquirer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mid_acquirer` (
  `codice_mid` varchar(45) NOT NULL,
  `codice_sia` varchar(45) DEFAULT NULL,
  `istituto` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`codice_mid`),
  UNIQUE KEY `codice_UNIQUE` (`codice_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `network`
--

DROP TABLE IF EXISTS `network`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `network` (
  `TerminalID` varchar(10) NOT NULL,
  `TIPO_RETE` varchar(12) DEFAULT NULL,
  `IP` varchar(20) DEFAULT NULL,
  `MASK` varchar(20) DEFAULT NULL,
  `GW1` varchar(20) DEFAULT NULL,
  `DNS1` varchar(45) DEFAULT NULL,
  `NOME_RETE` varchar(45) DEFAULT NULL,
  `PASS_RETE` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`TerminalID`),
  UNIQUE KEY `TerminalID_UNIQUE` (`TerminalID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scarti`
--

DROP TABLE IF EXISTS `scarti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scarti` (
  `pgID` varchar(50) NOT NULL,
  `codificaStab` varchar(50) DEFAULT NULL,
  `tipoRiep` varchar(50) DEFAULT NULL,
  `terminalID` varchar(90) DEFAULT NULL,
  `domestico` varchar(50) DEFAULT NULL,
  `pan` varchar(50) DEFAULT NULL,
  `dataOperazione` date NOT NULL,
  `oraOperazione` time NOT NULL,
  `importo` varchar(50) DEFAULT NULL,
  `codiceAutorizzativo` varchar(50) DEFAULT NULL,
  `tipoOperazione` varchar(100) DEFAULT NULL,
  `flagLog` varchar(50) DEFAULT NULL,
  `actinCode` varchar(50) DEFAULT NULL,
  `insegna` varchar(50) DEFAULT NULL,
  `cap` varchar(50) DEFAULT NULL,
  `localita` varchar(50) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `ragioneSociale` varchar(50) DEFAULT NULL,
  `indirizzo` varchar(50) DEFAULT NULL,
  `acquirer` varchar(50) DEFAULT NULL,
  `tag4f` varchar(50) DEFAULT NULL,
  `contoaccredito` varchar(50) DEFAULT NULL,
  `rrn` varchar(45) DEFAULT NULL,
  `operazione` varchar(45) DEFAULT NULL,
  `orderID` varchar(45) DEFAULT NULL,
  `cardholdername` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`pgID`),
  UNIQUE KEY `pgID_UNIQUE` (`pgID`),
  KEY `idx_tracciato_domestico` (`domestico`),
  KEY `acquirer` (`acquirer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stores`
--

DROP TABLE IF EXISTS `stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stores` (
  `TerminalID` varchar(45) NOT NULL,
  `Ragione_Sociale` varchar(45) DEFAULT NULL,
  `Insegna` varchar(55) DEFAULT NULL,
  `indirizzo` varchar(95) DEFAULT NULL,
  `citta` varchar(45) DEFAULT NULL,
  `cap` varchar(45) DEFAULT NULL,
  `prov` varchar(45) DEFAULT NULL,
  `sia_pagobancomat` varchar(45) DEFAULT NULL,
  `six` varchar(45) DEFAULT NULL,
  `amex` varchar(45) DEFAULT NULL,
  `Modello_pos` varchar(45) DEFAULT NULL,
  `country` varchar(2) DEFAULT 'IT',
  `bu` varchar(8) DEFAULT NULL,
  `bu1` varchar(8) DEFAULT NULL,
  `bu2` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`TerminalID`),
  UNIQUE KEY `TerminalID_UNIQUE` (`TerminalID`),
  KEY `idx_stores_TerminalID` (`TerminalID`),
  KEY `idx_stores_bu` (`bu`),
  KEY `idx_stores_bu1` (`bu1`),
  KEY `idx_stores_TerminalID_prefix` (`TerminalID`),
  KEY `idx_st_terminal` (`TerminalID`,`bu`,`bu1`),
  KEY `idx_st_terminalid` (`TerminalID`),
  KEY `idx_stores_Modello_pos` (`Modello_pos`),
  KEY `idx_stores_Insegna` (`Insegna`),
  KEY `idx_stores_Ragione_Sociale` (`Ragione_Sociale`),
  KEY `idx_stores_indirizzo` (`indirizzo`),
  KEY `idx_terminalid` (`TerminalID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `stores_con_network`
--

DROP TABLE IF EXISTS `stores_con_network`;
/*!50001 DROP VIEW IF EXISTS `stores_con_network`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `stores_con_network` AS SELECT 
 1 AS `TerminalID`,
 1 AS `Modello_pos`,
 1 AS `insegna`,
 1 AS `Ragione_Sociale`,
 1 AS `indirizzo`,
 1 AS `Rete`,
 1 AS `IP`,
 1 AS `MASK`,
 1 AS `GATEWAY`,
 1 AS `DNS`,
 1 AS `Nome_rete_wifi`,
 1 AS `Password_wifi`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tid_profiles`
--

DROP TABLE IF EXISTS `tid_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tid_profiles` (
  `BUID` int(11) DEFAULT NULL,
  `MERCHANT` int(11) DEFAULT NULL,
  `SHOP_SIGN` int(11) DEFAULT NULL,
  `ADDRESS` int(11) DEFAULT NULL,
  `NOMERIF` int(11) DEFAULT NULL,
  `POSID` int(11) DEFAULT NULL,
  `POS_VERSION` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `ABI` int(11) DEFAULT NULL,
  `VENDOR_CODE` int(11) DEFAULT NULL,
  `SHOP_CODE` int(11) DEFAULT NULL,
  `VAT_CODE` int(11) DEFAULT NULL,
  `STATE_PV` int(11) DEFAULT NULL,
  `STATE_POS` int(11) DEFAULT NULL,
  `STATE` int(11) DEFAULT NULL,
  `PROFILE` int(11) DEFAULT NULL,
  `CONN` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `CONN_BKUP` varchar(25) CHARACTER SET utf8 DEFAULT NULL,
  `CONN_TLG` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `POS_TYPE` varchar(8) CHARACTER SET utf8 DEFAULT NULL,
  `POS_TYPE_CODE` int(11) DEFAULT NULL,
  `POS_MODEL` int(11) DEFAULT NULL,
  `POS_MATRICOLA` int(11) DEFAULT NULL,
  `POS_SWBASE` int(11) DEFAULT NULL,
  `POS_SWAPP` varchar(6) CHARACTER SET utf8 DEFAULT NULL,
  `PINPAD_MODEL` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `CLESS_POS` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `CLESSPB_PROFILOTEC` varchar(2) CHARACTER SET utf8 DEFAULT NULL,
  `CLESSCC_PROFILOTEC` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `ACTIVATION_DATE` int(11) DEFAULT NULL,
  `DEACTIVATION_DATE` int(11) DEFAULT NULL,
  `CREATION_DATE` datetime DEFAULT NULL,
  `TCKSA_DS` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `TCKSB_DS` varchar(11) CHARACTER SET utf8 DEFAULT NULL,
  `CONFIGURATION_DATE` datetime DEFAULT NULL,
  `LASTDLL_DATE` datetime DEFAULT NULL,
  `LAST_TRX_DATE` datetime DEFAULT NULL,
  `LAST_OP_DATE` datetime DEFAULT NULL,
  `IDX` int(11) DEFAULT NULL,
  `BRAND` varchar(16) CHARACTER SET utf8 DEFAULT NULL,
  `ACQUIRER` varchar(14) CHARACTER SET utf8 DEFAULT NULL,
  `MERCHANT_CODE` bigint(20) DEFAULT NULL,
  `TERM_CODE` int(11) DEFAULT NULL,
  `ABI_CODE` int(11) DEFAULT NULL,
  `FABIL` bigint(20) DEFAULT NULL,
  `CLESS_CONV` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `VENDOR_PRIV` int(11) DEFAULT NULL,
  `PRIV` int(11) DEFAULT NULL,
  `BUSINESSUNITID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tracciato`
--

DROP TABLE IF EXISTS `tracciato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracciato` (
  `pgID` varchar(50) NOT NULL,
  `codificaStab` varchar(50) DEFAULT NULL,
  `tipoRiep` varchar(50) DEFAULT NULL,
  `terminalID` varchar(190) DEFAULT NULL,
  `domestico` varchar(50) DEFAULT NULL,
  `pan` varchar(50) DEFAULT NULL,
  `dataOperazione` date NOT NULL,
  `oraOperazione` time NOT NULL,
  `importo` varchar(50) DEFAULT NULL,
  `codiceAutorizzativo` varchar(50) DEFAULT NULL,
  `tipoOperazione` varchar(100) DEFAULT NULL,
  `flagLog` varchar(50) DEFAULT NULL,
  `actinCode` varchar(50) DEFAULT NULL,
  `insegna` varchar(50) DEFAULT NULL,
  `cap` varchar(50) DEFAULT NULL,
  `localita` varchar(50) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `ragioneSociale` varchar(50) DEFAULT NULL,
  `indirizzo` varchar(50) DEFAULT NULL,
  `acquirer` varchar(50) DEFAULT NULL,
  `tag4f` varchar(50) DEFAULT NULL,
  `contoaccredito` varchar(50) DEFAULT NULL,
  `rrn` varchar(45) DEFAULT NULL,
  `operazione` varchar(45) DEFAULT NULL,
  `orderID` varchar(45) DEFAULT NULL,
  `cardholdername` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`pgID`),
  UNIQUE KEY `pgID_UNIQUE` (`pgID`),
  KEY `idx_tracciato_domestico` (`domestico`),
  KEY `acquirer` (`acquirer`),
  KEY `dataoperazione_idx` (`dataOperazione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `tracciato_con_modello_pos`
--

DROP TABLE IF EXISTS `tracciato_con_modello_pos`;
/*!50001 DROP VIEW IF EXISTS `tracciato_con_modello_pos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tracciato_con_modello_pos` AS SELECT 
 1 AS `codificaStab`,
 1 AS `TerminalID`,
 1 AS `Modello_pos`,
 1 AS `domestico`,
 1 AS `pan`,
 1 AS `dataOperazione`,
 1 AS `oraOperazione`,
 1 AS `importo`,
 1 AS `codiceAutorizzativo`,
 1 AS `flagLog`,
 1 AS `actinCode`,
 1 AS `insegna`,
 1 AS `Ragione_Sociale`,
 1 AS `indirizzo`,
 1 AS `citta`,
 1 AS `prov`,
 1 AS `cap`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tracciato_pos`
--

DROP TABLE IF EXISTS `tracciato_pos`;
/*!50001 DROP VIEW IF EXISTS `tracciato_pos`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tracciato_pos` AS SELECT 
 1 AS `codificaStab`,
 1 AS `terminalID`,
 1 AS `Modello_pos`,
 1 AS `domestico`,
 1 AS `pan`,
 1 AS `tag4f`,
 1 AS `dataOperazione`,
 1 AS `oraOperazione`,
 1 AS `importo`,
 1 AS `codiceAutorizzativo`,
 1 AS `acquirer`,
 1 AS `flagLog`,
 1 AS `actinCode`,
 1 AS `tipoOperazione`,
 1 AS `insegna`,
 1 AS `Ragione_Sociale`,
 1 AS `indirizzo`,
 1 AS `localita`,
 1 AS `prov`,
 1 AS `cap`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tracciato_pos_es`
--

DROP TABLE IF EXISTS `tracciato_pos_es`;
/*!50001 DROP VIEW IF EXISTS `tracciato_pos_es`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tracciato_pos_es` AS SELECT 
 1 AS `codificaStab`,
 1 AS `terminalID`,
 1 AS `Modello_pos`,
 1 AS `domestico`,
 1 AS `pan`,
 1 AS `tag4f`,
 1 AS `dataOperazione`,
 1 AS `oraOperazione`,
 1 AS `importo`,
 1 AS `codiceAutorizzativo`,
 1 AS `acquirer`,
 1 AS `flagLog`,
 1 AS `actinCode`,
 1 AS `insegna`,
 1 AS `Ragione_Sociale`,
 1 AS `indirizzo`,
 1 AS `localita`,
 1 AS `prov`,
 1 AS `cap`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tracciato_pos_nets`
--

DROP TABLE IF EXISTS `tracciato_pos_nets`;
/*!50001 DROP VIEW IF EXISTS `tracciato_pos_nets`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tracciato_pos_nets` AS SELECT 
 1 AS `codificaStab`,
 1 AS `terminalID`,
 1 AS `Modello_pos`,
 1 AS `domestico`,
 1 AS `pan`,
 1 AS `tag4f`,
 1 AS `dataOperazione`,
 1 AS `oraOperazione`,
 1 AS `importo`,
 1 AS `codiceAutorizzativo`,
 1 AS `acquirer`,
 1 AS `flagLog`,
 1 AS `actinCode`,
 1 AS `insegna`,
 1 AS `Ragione_Sociale`,
 1 AS `indirizzo`,
 1 AS `localita`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `tracciato_pos_online`
--

DROP TABLE IF EXISTS `tracciato_pos_online`;
/*!50001 DROP VIEW IF EXISTS `tracciato_pos_online`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tracciato_pos_online` AS SELECT 
 1 AS `codificaStab`,
 1 AS `terminalID`,
 1 AS `Modello_pos`,
 1 AS `pan`,
 1 AS `dataOperazione`,
 1 AS `importo`,
 1 AS `codiceAutorizzativo`,
 1 AS `acquirer`,
 1 AS `PosAcq`,
 1 AS `AId`,
 1 AS `PosStan`,
 1 AS `Conf`,
 1 AS `NumOper`,
 1 AS `TP`,
 1 AS `TPC`,
 1 AS `insegna`,
 1 AS `Ragione_Sociale`,
 1 AS `indirizzo`,
 1 AS `localita`,
 1 AS `prov`,
 1 AS `cap`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tracciatoes`
--

DROP TABLE IF EXISTS `tracciatoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracciatoes` (
  `pgID` varchar(50) NOT NULL,
  `codificaStab` varchar(50) DEFAULT NULL,
  `tipoRiep` varchar(50) DEFAULT NULL,
  `terminalID` varchar(50) DEFAULT NULL,
  `domestico` varchar(50) DEFAULT NULL,
  `pan` varchar(50) DEFAULT NULL,
  `dataOperazione` date NOT NULL,
  `oraOperazione` time NOT NULL,
  `importo` varchar(50) DEFAULT NULL,
  `codiceAutorizzativo` varchar(50) DEFAULT NULL,
  `tipoOperazione` varchar(100) DEFAULT NULL,
  `flagLog` varchar(50) DEFAULT NULL,
  `actinCode` varchar(50) DEFAULT NULL,
  `insegna` varchar(50) DEFAULT NULL,
  `cap` varchar(50) DEFAULT NULL,
  `localita` varchar(50) DEFAULT NULL,
  `provincia` varchar(50) DEFAULT NULL,
  `ragioneSociale` varchar(50) DEFAULT NULL,
  `indirizzo` varchar(50) DEFAULT NULL,
  `acquirer` varchar(50) DEFAULT NULL,
  `tag4f` varchar(50) DEFAULT NULL,
  `contoaccredito` varchar(50) DEFAULT NULL,
  `rrn` varchar(40) DEFAULT NULL,
  `operazione` varchar(45) DEFAULT NULL,
  `orderID` varchar(45) DEFAULT NULL,
  `cardholdername` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`pgID`),
  UNIQUE KEY `pgID_UNIQUE` (`pgID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_token_expiry` datetime DEFAULT NULL,
  `password_last_changed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bu` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `stores_con_network`
--

/*!50001 DROP VIEW IF EXISTS `stores_con_network`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`PGDBUSER`@`pgbedb02` SQL SECURITY DEFINER */
/*!50001 VIEW `stores_con_network` AS select `stores`.`TerminalID` AS `TerminalID`,`stores`.`Modello_pos` AS `Modello_pos`,`stores`.`Insegna` AS `insegna`,`stores`.`Ragione_Sociale` AS `Ragione_Sociale`,`stores`.`indirizzo` AS `indirizzo`,`network`.`TIPO_RETE` AS `Rete`,`network`.`IP` AS `IP`,`network`.`MASK` AS `MASK`,`network`.`GW1` AS `GATEWAY`,`network`.`DNS1` AS `DNS`,`network`.`NOME_RETE` AS `Nome_rete_wifi`,`network`.`PASS_RETE` AS `Password_wifi` from (`network` join `stores` on((`stores`.`TerminalID` = `network`.`TerminalID`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tracciato_con_modello_pos`
--

/*!50001 DROP VIEW IF EXISTS `tracciato_con_modello_pos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`PGDBUSER`@`10.10.10.9` SQL SECURITY DEFINER */
/*!50001 VIEW `tracciato_con_modello_pos` AS select `tracciato`.`codificaStab` AS `codificaStab`,`tracciato`.`terminalID` AS `TerminalID`,`stores`.`Modello_pos` AS `Modello_pos`,`tracciato`.`domestico` AS `domestico`,`tracciato`.`pan` AS `pan`,`tracciato`.`dataOperazione` AS `dataOperazione`,`tracciato`.`oraOperazione` AS `oraOperazione`,`tracciato`.`importo` AS `importo`,`tracciato`.`codiceAutorizzativo` AS `codiceAutorizzativo`,`tracciato`.`flagLog` AS `flagLog`,`tracciato`.`actinCode` AS `actinCode`,`stores`.`Insegna` AS `insegna`,`stores`.`Ragione_Sociale` AS `Ragione_Sociale`,`stores`.`indirizzo` AS `indirizzo`,`stores`.`citta` AS `citta`,`stores`.`prov` AS `prov`,`stores`.`cap` AS `cap` from (`tracciato` left join `stores` on((`stores`.`TerminalID` = `tracciato`.`terminalID`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tracciato_pos`
--

/*!50001 DROP VIEW IF EXISTS `tracciato_pos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tracciato_pos` AS select `tracciato`.`codificaStab` AS `codificaStab`,`tracciato`.`terminalID` AS `terminalID`,`stores`.`Modello_pos` AS `Modello_pos`,`tracciato`.`domestico` AS `domestico`,`tracciato`.`pan` AS `pan`,`tracciato`.`tag4f` AS `tag4f`,`tracciato`.`dataOperazione` AS `dataOperazione`,`tracciato`.`oraOperazione` AS `oraOperazione`,`tracciato`.`importo` AS `importo`,`tracciato`.`codiceAutorizzativo` AS `codiceAutorizzativo`,`tracciato`.`acquirer` AS `acquirer`,`tracciato`.`flagLog` AS `flagLog`,`tracciato`.`actinCode` AS `actinCode`,`tracciato`.`tipoOperazione` AS `tipoOperazione`,`stores`.`Insegna` AS `insegna`,`stores`.`Ragione_Sociale` AS `Ragione_Sociale`,`stores`.`indirizzo` AS `indirizzo`,`stores`.`citta` AS `localita`,`stores`.`prov` AS `prov`,`stores`.`cap` AS `cap` from (`tracciato` left join `stores` on((`stores`.`TerminalID` = `tracciato`.`terminalID`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tracciato_pos_es`
--

/*!50001 DROP VIEW IF EXISTS `tracciato_pos_es`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tracciato_pos_es` AS select `tracciatoes`.`codificaStab` AS `codificaStab`,`tracciatoes`.`terminalID` AS `terminalID`,`stores`.`Modello_pos` AS `Modello_pos`,`tracciatoes`.`domestico` AS `domestico`,`tracciatoes`.`pan` AS `pan`,`tracciatoes`.`tag4f` AS `tag4f`,`tracciatoes`.`dataOperazione` AS `dataOperazione`,`tracciatoes`.`oraOperazione` AS `oraOperazione`,`tracciatoes`.`importo` AS `importo`,`tracciatoes`.`codiceAutorizzativo` AS `codiceAutorizzativo`,`tracciatoes`.`acquirer` AS `acquirer`,`tracciatoes`.`flagLog` AS `flagLog`,`tracciatoes`.`actinCode` AS `actinCode`,`stores`.`Insegna` AS `insegna`,`stores`.`Ragione_Sociale` AS `Ragione_Sociale`,`stores`.`indirizzo` AS `indirizzo`,`stores`.`citta` AS `localita`,`stores`.`prov` AS `prov`,`stores`.`cap` AS `cap` from (`tracciatoes` join `stores` on((`stores`.`TerminalID` = `tracciatoes`.`terminalID`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tracciato_pos_nets`
--

/*!50001 DROP VIEW IF EXISTS `tracciato_pos_nets`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tracciato_pos_nets` AS select `tracciato`.`codificaStab` AS `codificaStab`,`tracciato`.`terminalID` AS `terminalID`,`devices`.`POS_MODEL` AS `Modello_pos`,`tracciato`.`domestico` AS `domestico`,`tracciato`.`pan` AS `pan`,`tracciato`.`tag4f` AS `tag4f`,`tracciato`.`dataOperazione` AS `dataOperazione`,`tracciato`.`oraOperazione` AS `oraOperazione`,`tracciato`.`importo` AS `importo`,`tracciato`.`codiceAutorizzativo` AS `codiceAutorizzativo`,`tracciato`.`acquirer` AS `acquirer`,`tracciato`.`flagLog` AS `flagLog`,`tracciato`.`actinCode` AS `actinCode`,`devices`.`SHOP_SIGN` AS `insegna`,`devices`.`MERCHANT` AS `Ragione_Sociale`,`devices`.`ADDRESS` AS `indirizzo`,right(`devices`.`SHOP_SIGN`,(length(`devices`.`SHOP_SIGN`) - locate('-',`devices`.`SHOP_SIGN`))) AS `localita` from (`tracciato` join `devices` on((`devices`.`POSID` = `tracciato`.`terminalID`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `tracciato_pos_online`
--

/*!50001 DROP VIEW IF EXISTS `tracciato_pos_online`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tracciato_pos_online` AS select `ftfs_transactions`.`MeId` AS `codificaStab`,`ftfs_transactions`.`TermId` AS `terminalID`,`stores`.`Modello_pos` AS `Modello_pos`,`ftfs_transactions`.`Pan` AS `pan`,`ftfs_transactions`.`DtPos` AS `dataOperazione`,`ftfs_transactions`.`Amount` AS `importo`,`ftfs_transactions`.`ApprNum` AS `codiceAutorizzativo`,`ftfs_transactions`.`Acquirer` AS `acquirer`,`ftfs_transactions`.`PosAcq` AS `PosAcq`,`ftfs_transactions`.`AId` AS `AId`,`ftfs_transactions`.`PosStan` AS `PosStan`,`ftfs_transactions`.`Conf` AS `Conf`,`ftfs_transactions`.`NumOper` AS `NumOper`,`ftfs_transactions`.`TP` AS `TP`,`ftfs_transactions`.`Tpc` AS `TPC`,`stores`.`Insegna` AS `insegna`,`stores`.`Ragione_Sociale` AS `Ragione_Sociale`,`stores`.`indirizzo` AS `indirizzo`,`stores`.`citta` AS `localita`,`stores`.`prov` AS `prov`,`stores`.`cap` AS `cap` from (`ftfs_transactions` left join `stores` on((convert(`stores`.`TerminalID` using utf8mb4) = `ftfs_transactions`.`TermId`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-16 11:05:45
