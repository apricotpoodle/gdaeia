-- DDL de référence de l'ancienne application DAE.
--
-- Copie de travail limitée au périmètre de migration des utilisateurs et des
-- habilitations. Les tables métier historiques sont volontairement exclues
-- de ce premier lot.
-- Base source : dae

CREATE TABLE `ts_user_usr` (
  `USR_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `USR_BASE` bigint(1) DEFAULT '1' COMMENT 'Modifiable ? 1:Oui 0:NON',
  `RLE_ID` bigint(11) NOT NULL DEFAULT '1',
  `USR_NOM` varchar(255) DEFAULT NULL,
  `USR_PRENOM` varchar(64) DEFAULT NULL,
  `USR_PATRONYME` varchar(64) DEFAULT NULL,
  `USR_ADR` varchar(255) NOT NULL,
  `USR_PWD` varchar(255) DEFAULT NULL,
  `USR_TOKEN_CONF` varchar(60) DEFAULT NULL,
  `USR_DATE_CONF` datetime DEFAULT NULL,
  `USR_TOKEN_REG` varchar(60) DEFAULT NULL,
  `USR_DATE_REG` datetime DEFAULT NULL,
  `USR_TOKEN_REM` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`USR_ID`),
  KEY `usr_name` (`USR_NOM`),
  KEY `FK_USR_RLE` (`RLE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tr_role_rle` (
  `RLE_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `RLE_CODE` varchar(16) NOT NULL,
  `RLE_LBL` varchar(32) NOT NULL,
  `RLE_BASE` tinyint(1) NOT NULL DEFAULT '0',
  `RLE_ORD` bigint(11) NOT NULL,
  PRIMARY KEY (`RLE_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tr_groupe_user_grp` (
  `GRP_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `GRP_CODE` varchar(32) NOT NULL,
  `GRP_LBL` varchar(128) NOT NULL DEFAULT '""',
  `GRP_BASE` tinyint(1) NOT NULL DEFAULT '0',
  `GRP_ORD` varchar(32) NOT NULL DEFAULT '""',
  PRIMARY KEY (`GRP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tr_nom_societe_stn` (
  `STN_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `STN_CODE` varchar(32) NOT NULL,
  `STN_LBL` varchar(64) NOT NULL,
  `STN_BASE` tinyint(1) NOT NULL DEFAULT '0',
  `STN_ORD` bigint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`STN_ID`),
  UNIQUE KEY `STN_CODE` (`STN_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tr_service_srv` (
  `SRV_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `SRV_CODE` varchar(32) NOT NULL,
  `SRV_LBL` varchar(64) NOT NULL,
  `SRV_BASE` tinyint(1) NOT NULL DEFAULT '0',
  `SRV_ORD` varchar(32) NOT NULL,
  PRIMARY KEY (`SRV_ID`),
  UNIQUE KEY `SRV_CODE` (`SRV_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tr_code_analytique_cgr` (
  `CGR_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `CGR_CODE` varchar(32) NOT NULL,
  `CGR_LBL` varchar(64) NOT NULL,
  `CGR_BASE` tinyint(1) NOT NULL DEFAULT '0',
  `CGR_ORD` varchar(64) NOT NULL,
  PRIMARY KEY (`CGR_ID`),
  UNIQUE KEY `ANA_CODE` (`CGR_CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tj_stn_usr_rle_sur` (
  `SUR_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `USR_ID` bigint(11) NOT NULL,
  `RLE_ID` bigint(11) NOT NULL,
  `STN_ID` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`SUR_ID`),
  UNIQUE KEY `SUR_USR_STN` (`USR_ID`,`STN_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tj_gru_user_gru` (
  `GRU_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `GRP_ID` bigint(11) NOT NULL,
  `USR_ID` bigint(11) NOT NULL,
  PRIMARY KEY (`GRU_ID`),
  KEY `FK_USR_ID` (`USR_ID`),
  KEY `FK_GRP_ID` (`GRP_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tj_usr_srv_visible_usv` (
  `USV_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `USR_ID` bigint(11) NOT NULL,
  `STN_ID` bigint(11) NOT NULL,
  `SST_ID` bigint(11) DEFAULT NULL,
  `a_virer` bigint(11) DEFAULT NULL,
  PRIMARY KEY (`USV_ID`),
  UNIQUE KEY `UNIQUE` (`USR_ID`,`STN_ID`,`SST_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tj_cgr_srv_usr_csu` (
  `CSU_ID` bigint(11) NOT NULL AUTO_INCREMENT,
  `CGR_ID` bigint(11) NOT NULL,
  `SRV_ID` bigint(11) NOT NULL,
  `USR_ASS_ID` bigint(11) DEFAULT NULL,
  `USR_CSV_ID` bigint(11) DEFAULT NULL,
  `STN_ID` bigint(11) NOT NULL,
  PRIMARY KEY (`CSU_ID`),
  UNIQUE KEY `CGR_SRV_ASS_CSV_STN_unique`
    (`CGR_ID`,`SRV_ID`,`USR_ASS_ID`,`USR_CSV_ID`,`STN_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
