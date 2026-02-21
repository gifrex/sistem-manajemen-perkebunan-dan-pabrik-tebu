/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 11.7.2-MariaDB : Database - tebu
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`tebu` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `tebu`;

/*Table structure for table `absenhdr` */

DROP TABLE IF EXISTS `absenhdr`;

CREATE TABLE `absenhdr` (
  `absenno` varchar(20) NOT NULL,
  `companycode` char(4) NOT NULL,
  `mandorid` varchar(30) DEFAULT NULL,
  `totalpekerja` int(3) DEFAULT NULL,
  `approvaluserid` varchar(50) DEFAULT NULL,
  `approvalstatus` char(1) DEFAULT NULL,
  `approvaldate` datetime DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `uploaddate` datetime DEFAULT NULL,
  `status` varchar(4) DEFAULT NULL,
  `rejectdate` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`absenno`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `absenlst` */

DROP TABLE IF EXISTS `absenlst`;

CREATE TABLE `absenlst` (
  `absenno` varchar(20) DEFAULT NULL,
  `companycode` char(4) DEFAULT NULL,
  `id` int(3) DEFAULT NULL,
  `tenagakerjaid` varchar(11) DEFAULT NULL,
  `absentype` enum('HADIR','LOKASI') DEFAULT 'HADIR',
  `absenmasuk` datetime DEFAULT NULL,
  `absenpulang` datetime DEFAULT NULL,
  `keterangan` varchar(75) DEFAULT NULL,
  `fotoabsenmasuk` varchar(255) DEFAULT NULL,
  `fotomasukapprovalstatus` char(1) DEFAULT NULL,
  `fotomasukapprovalreason` varchar(500) DEFAULT NULL,
  `fotoabsenpulang` varchar(255) DEFAULT NULL,
  `lokasifotolat` decimal(10,8) DEFAULT NULL,
  `lokasifotolng` decimal(11,8) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `accountingcredit` */

DROP TABLE IF EXISTS `accountingcredit`;

CREATE TABLE `accountingcredit` (
  `id` int(11) DEFAULT NULL,
  `jurnalaccname` varchar(50) NOT NULL,
  `jurnalaccno` char(12) NOT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`jurnalaccname`,`jurnalaccno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `accountingdebit` */

DROP TABLE IF EXISTS `accountingdebit`;

CREATE TABLE `accountingdebit` (
  `activitycode` varchar(50) NOT NULL,
  `jurnalaccno` char(12) NOT NULL,
  `materialaccno` varchar(12) DEFAULT NULL,
  `bbmaccno` varchar(12) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`activitycode`,`jurnalaccno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `activity` */

DROP TABLE IF EXISTS `activity`;

CREATE TABLE `activity` (
  `activitycode` varchar(50) NOT NULL,
  `activitygroup` varchar(45) DEFAULT NULL,
  `activityname` varchar(150) DEFAULT NULL,
  `activityname2` varchar(150) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `jenistenagakerja` tinyint(1) DEFAULT NULL,
  `usingmaterial` tinyint(4) DEFAULT NULL,
  `usingvehicle` tinyint(1) DEFAULT NULL,
  `jumlahvar` tinyint(1) DEFAULT NULL,
  `var1` varchar(30) DEFAULT NULL,
  `satuan1` varchar(30) DEFAULT NULL,
  `var2` varchar(30) DEFAULT NULL,
  `satuan2` varchar(30) DEFAULT NULL,
  `var3` varchar(30) DEFAULT NULL,
  `satuan3` varchar(30) DEFAULT NULL,
  `var4` varchar(30) DEFAULT NULL,
  `satuan4` varchar(30) DEFAULT NULL,
  `var5` varchar(30) DEFAULT NULL,
  `satuan5` varchar(30) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `inputby` varchar(30) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL,
  `updatedby` varchar(30) DEFAULT NULL,
  `accno` varchar(25) DEFAULT NULL,
  `active` tinyint(4) DEFAULT NULL,
  `isblokactivity` tinyint(1) DEFAULT 0 COMMENT '1=Activity per blok (tidak perlu plot), 0=Activity per plot (default)',
  PRIMARY KEY (`activitycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `activitygroup` */

DROP TABLE IF EXISTS `activitygroup`;

CREATE TABLE `activitygroup` (
  `activitygroup` varchar(50) NOT NULL,
  `groupname` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`activitygroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `agro_hdr` */

DROP TABLE IF EXISTS `agro_hdr`;

CREATE TABLE `agro_hdr` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plotcode` char(5) NOT NULL DEFAULT '',
  `plotcodesample` char(5) NOT NULL DEFAULT '',
  `varietas` varchar(10) NOT NULL DEFAULT '',
  `kat` char(3) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL,
  `tglamat` date NOT NULL,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`companycode`,`tanggaltanam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `agro_lst` */

DROP TABLE IF EXISTS `agro_lst`;

CREATE TABLE `agro_lst` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL,
  `nourut` int(11) NOT NULL DEFAULT 0,
  `jm_batang` int(11) NOT NULL DEFAULT 0,
  `pan_gap` int(11) NOT NULL DEFAULT 0,
  `per_gap` decimal(7,2) NOT NULL DEFAULT 0.00,
  `per_germinasi` decimal(7,2) NOT NULL DEFAULT 0.00,
  `ph_tanah` decimal(4,1) NOT NULL DEFAULT 0.0,
  `populasi` decimal(7,1) NOT NULL DEFAULT 0.0,
  `ktk_gulma` int(11) NOT NULL DEFAULT 0,
  `per_gulma` decimal(7,2) NOT NULL DEFAULT 0.00,
  `t_primer` int(11) NOT NULL DEFAULT 0,
  `t_sekunder` int(11) NOT NULL DEFAULT 0,
  `t_tersier` int(11) NOT NULL DEFAULT 0,
  `t_kuarter` int(11) NOT NULL DEFAULT 0,
  `d_primer` decimal(4,1) NOT NULL DEFAULT 0.0,
  `d_sekunder` decimal(5,2) NOT NULL DEFAULT 0.00,
  `d_tersier` decimal(6,3) NOT NULL DEFAULT 0.000,
  `d_kuarter` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`companycode`,`tanggaltanam`,`nourut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `agrohdr` */

DROP TABLE IF EXISTS `agrohdr`;

CREATE TABLE `agrohdr` (
  `nosample` char(4) NOT NULL DEFAULT '',
  `companycode` char(6) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plot` char(10) NOT NULL DEFAULT '',
  `varietas` varchar(10) NOT NULL DEFAULT '',
  `kat` char(3) NOT NULL DEFAULT '',
  `pkp` int(11) DEFAULT 0,
  `tanggaltanam` date NOT NULL DEFAULT '0000-00-00',
  `tanggalpengamatan` date NOT NULL DEFAULT '0000-00-00',
  `bulanpanen` char(12) DEFAULT NULL,
  `umurpanen` int(11) DEFAULT NULL,
  `tanggalzpk` date DEFAULT NULL,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `tanggalposting` date DEFAULT NULL,
  `closingperiode` enum('T','F') NOT NULL DEFAULT 'F',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updatedat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nosample`,`companycode`,`tanggalpengamatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `agrolst` */

DROP TABLE IF EXISTS `agrolst`;

CREATE TABLE `agrolst` (
  `nosample` char(4) NOT NULL DEFAULT '',
  `companycode` char(6) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL DEFAULT '0000-00-00',
  `tanggalpengamatan` date NOT NULL DEFAULT '0000-00-00',
  `kat` char(3) NOT NULL DEFAULT '',
  `nourut` int(11) NOT NULL DEFAULT 0,
  `jumlahbatang` int(11) NOT NULL DEFAULT 0,
  `bat_primer` int(11) DEFAULT NULL,
  `bat_sekunder` int(11) DEFAULT NULL,
  `bat_tersier` int(11) DEFAULT NULL,
  `bat_kuarter` int(11) DEFAULT NULL,
  `pan_gap` int(11) NOT NULL DEFAULT 0,
  `per_gap` decimal(7,2) NOT NULL DEFAULT 0.00,
  `per_germinasi` decimal(7,2) NOT NULL DEFAULT 0.00,
  `ph_tanah` decimal(4,1) NOT NULL DEFAULT 0.0,
  `populasi` decimal(7,0) NOT NULL DEFAULT 0,
  `ktk_gulma` int(11) NOT NULL DEFAULT 0,
  `per_gulma` decimal(7,2) NOT NULL DEFAULT 0.00,
  `t_primer` int(11) NOT NULL DEFAULT 0,
  `t_sekunder` int(11) NOT NULL DEFAULT 0,
  `t_tersier` int(11) NOT NULL DEFAULT 0,
  `t_kuarter` int(11) NOT NULL DEFAULT 0,
  `d_primer` decimal(4,1) NOT NULL DEFAULT 0.0,
  `d_sekunder` decimal(5,2) NOT NULL DEFAULT 0.00,
  `d_tersier` decimal(6,3) NOT NULL DEFAULT 0.000,
  `berat_primer` int(11) DEFAULT NULL,
  `berat_sekunder` int(11) DEFAULT NULL,
  `berat_tersier` int(11) DEFAULT NULL,
  `berat_kuarter` int(11) DEFAULT NULL,
  `brix_primer` decimal(4,2) DEFAULT NULL,
  `brix_sekunder` decimal(4,2) DEFAULT NULL,
  `brix_tersier` decimal(4,2) DEFAULT NULL,
  `brix_kuarter` decimal(4,2) DEFAULT NULL,
  `d_kuarter` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `closingperiode` enum('T','F') NOT NULL DEFAULT 'F',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updatedat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nosample`,`companycode`,`tanggalpengamatan`,`nourut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `app` */

DROP TABLE IF EXISTS `app`;

CREATE TABLE `app` (
  `appid` varchar(30) DEFAULT NULL,
  `appname` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `appmenu` */

DROP TABLE IF EXISTS `appmenu`;

CREATE TABLE `appmenu` (
  `menuid` varchar(15) DEFAULT NULL,
  `appid` varchar(15) DEFAULT NULL,
  `menuname` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `appmenusubmenu` */

DROP TABLE IF EXISTS `appmenusubmenu`;

CREATE TABLE `appmenusubmenu` (
  `submenuid` varchar(15) DEFAULT NULL,
  `menuid` varchar(15) DEFAULT NULL,
  `appid` varchar(15) DEFAULT NULL,
  `submenuname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*Table structure for table `approval` */

DROP TABLE IF EXISTS `approval`;

CREATE TABLE `approval` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `category` varchar(150) NOT NULL,
  `activitygroup` varchar(5) DEFAULT NULL,
  `jumlahapproval` int(11) NOT NULL,
  `idjabatanapproval1` tinyint(3) unsigned DEFAULT NULL,
  `idjabatanapproval2` tinyint(3) unsigned DEFAULT NULL,
  `idjabatanapproval3` tinyint(3) unsigned DEFAULT NULL,
  `idjabatanapproval4` tinyint(3) unsigned DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_approval_natural` (`companycode`,`category`),
  UNIQUE KEY `unique_company_activitygroup` (`companycode`,`activitygroup`),
  KEY `fk_approval_jabatan1` (`idjabatanapproval1`),
  KEY `fk_approval_jabatan2` (`idjabatanapproval2`),
  KEY `fk_approval_jabatan3` (`idjabatanapproval3`),
  KEY `fk_approval_company` (`companycode`),
  CONSTRAINT `fk_approval_company` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_approval_jabatan1` FOREIGN KEY (`idjabatanapproval1`) REFERENCES `jabatan` (`idjabatan`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_approval_jabatan2` FOREIGN KEY (`idjabatanapproval2`) REFERENCES `jabatan` (`idjabatan`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_approval_jabatan3` FOREIGN KEY (`idjabatanapproval3`) REFERENCES `jabatan` (`idjabatan`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `approvaltransaction` */

DROP TABLE IF EXISTS `approvaltransaction`;

CREATE TABLE `approvaltransaction` (
  `approvalno` varchar(20) NOT NULL COMMENT 'Format: APV[YYMMDD][SEQ]',
  `companycode` char(4) NOT NULL,
  `approvalcategoryid` int(11) NOT NULL COMMENT 'FK to approval.id',
  `transactionnumber` varchar(20) NOT NULL COMMENT 'Reference number dari tabel asal (misal: plottransaction.transactionnumber)',
  `jumlahapproval` int(11) DEFAULT NULL COMMENT 'Total level approval yang dibutuhkan',
  `approval1idjabatan` tinyint(3) unsigned DEFAULT NULL,
  `approval1userid` varchar(50) DEFAULT NULL,
  `approval1flag` char(1) DEFAULT NULL COMMENT '1=approved, 0=declined, NULL=pending',
  `approval1date` datetime DEFAULT NULL,
  `approval2idjabatan` tinyint(3) unsigned DEFAULT NULL,
  `approval2userid` varchar(50) DEFAULT NULL,
  `approval2flag` char(1) DEFAULT NULL COMMENT '1=approved, 0=declined, NULL=pending',
  `approval2date` datetime DEFAULT NULL,
  `approval3idjabatan` tinyint(3) unsigned DEFAULT NULL,
  `approval3userid` varchar(50) DEFAULT NULL,
  `approval3flag` char(1) DEFAULT NULL COMMENT '1=approved, 0=declined, NULL=pending',
  `approval3date` datetime DEFAULT NULL,
  `approvalstatus` char(1) DEFAULT NULL COMMENT '1=fully approved, 0=declined, NULL=pending',
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL,
  PRIMARY KEY (`companycode`,`approvalno`),
  UNIQUE KEY `unique_transaction` (`companycode`,`transactionnumber`),
  KEY `idx_approvalcategory` (`approvalcategoryid`),
  KEY `idx_approvalstatus` (`companycode`,`approvalstatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Generic approval transaction table for low-frequency modules (Split/Merge, Purchase Request, etc)';

/*Table structure for table `batch` */

DROP TABLE IF EXISTS `batch`;

CREATE TABLE `batch` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `batchno` varchar(20) NOT NULL,
  `companycode` char(4) NOT NULL,
  `plot` char(5) NOT NULL,
  `batcharea` decimal(6,2) NOT NULL,
  `batchdate` date NOT NULL,
  `tanggalulangtahun` date DEFAULT NULL,
  `lifecyclestatus` enum('PC','RC1','RC2','RC3') DEFAULT 'PC',
  `previousbatchno` varchar(20) DEFAULT NULL COMMENT 'Previous batch number for lifecycle tracking',
  `plantinglkhno` varchar(15) DEFAULT NULL COMMENT 'LKH number for planting activity (copied from PC to RC1/RC2/RC3)',
  `tanggalpanen` date DEFAULT NULL,
  `kontraktorid` varchar(10) DEFAULT NULL COMMENT 'Fixed contractor for this plot (copied across lifecycle)',
  `kodevarietas` varchar(10) DEFAULT NULL,
  `pkp` int(11) DEFAULT NULL,
  `lastactivity` varchar(100) DEFAULT NULL,
  `isactive` tinyint(1) DEFAULT 1,
  `closedat` datetime DEFAULT NULL COMMENT 'Timestamp when batch was closed/completed',
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `plottype` enum('KBD','KTG') DEFAULT NULL COMMENT 'KBD = Kebun Bibit; KTG = Kebun Tebu Giling',
  `splitfrombatchno` varchar(20) DEFAULT NULL COMMENT 'Parent batch jika hasil split',
  `mergedtobatchno` varchar(20) DEFAULT NULL COMMENT 'Batch tujuan jika sudah merge',
  `splitmergedreason` text DEFAULT NULL COMMENT 'Alasan operasi split/merge',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_batch_natural` (`batchno`,`companycode`),
  KEY `idx_company_plot` (`companycode`,`plot`),
  KEY `idx_active` (`isactive`),
  KEY `idx_plottype` (`plottype`),
  KEY `idx_previousbatch` (`previousbatchno`),
  KEY `idx_plantinglkh` (`plantinglkhno`),
  KEY `idx_kontraktor` (`kontraktorid`),
  KEY `idx_lifecycle_history` (`plot`,`lifecyclestatus`,`closedat`),
  KEY `idx_company_active_lifecycle` (`companycode`,`isactive`,`lifecyclestatus`),
  KEY `idx_kodevarietas` (`kodevarietas`),
  KEY `idx_pkp` (`pkp`),
  KEY `idx_batchdate` (`batchdate`),
  KEY `idx_splitfrom` (`splitfrombatchno`),
  KEY `idx_mergedto` (`mergedtobatchno`),
  CONSTRAINT `fk_batch_company` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_batch_plantinglkh` FOREIGN KEY (`plantinglkhno`) REFERENCES `lkhhdr` (`lkhno`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_batch_plot` FOREIGN KEY (`companycode`, `plot`) REFERENCES `masterlist` (`companycode`, `plot`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3003 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `batchgenealogy` */

DROP TABLE IF EXISTS `batchgenealogy`;

CREATE TABLE `batchgenealogy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `childbatchno` varchar(20) NOT NULL,
  `parentbatchno` varchar(20) NOT NULL,
  `relationshiptype` enum('SPLIT','MERGE','TRANSITION') NOT NULL,
  `generationlevel` int(11) NOT NULL DEFAULT 0,
  `createdat` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_genealogy` (`companycode`,`childbatchno`,`parentbatchno`),
  KEY `idx_child` (`companycode`,`childbatchno`),
  KEY `idx_parent` (`companycode`,`parentbatchno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `blok` */

DROP TABLE IF EXISTS `blok`;

CREATE TABLE `blok` (
  `blok` char(3) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`blok`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `cache` */

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `cache_locks` */

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `chat_messages` */

DROP TABLE IF EXISTS `chat_messages`;

CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `user_name` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_user_id_foreign` (`user_id`),
  KEY `chat_messages_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `closing_agro_hdr` */

DROP TABLE IF EXISTS `closing_agro_hdr`;

CREATE TABLE `closing_agro_hdr` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `kd_comp` char(4) NOT NULL DEFAULT '',
  `kd_blok` char(2) NOT NULL DEFAULT '',
  `kd_plot` char(5) NOT NULL DEFAULT '',
  `kd_plotsample` char(5) NOT NULL DEFAULT '',
  `varietas` varchar(10) NOT NULL DEFAULT '',
  `kat` char(3) NOT NULL DEFAULT '',
  `tgltanam` date NOT NULL,
  `tglamat` date NOT NULL,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `user_input` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`kd_comp`,`tgltanam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `closing_agro_lst` */

DROP TABLE IF EXISTS `closing_agro_lst`;

CREATE TABLE `closing_agro_lst` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `kd_comp` char(4) NOT NULL DEFAULT '',
  `tgltanam` date NOT NULL,
  `no_urut` int(11) NOT NULL DEFAULT 0,
  `jm_batang` int(11) NOT NULL DEFAULT 0,
  `pan_gap` int(11) NOT NULL DEFAULT 0,
  `per_gap` decimal(7,2) NOT NULL DEFAULT 0.00,
  `per_germinasi` decimal(7,2) NOT NULL DEFAULT 0.00,
  `ph_tanah` decimal(4,1) NOT NULL DEFAULT 0.0,
  `populasi` decimal(7,1) NOT NULL DEFAULT 0.0,
  `ktk_gulma` int(11) NOT NULL DEFAULT 0,
  `per_gulma` decimal(7,2) NOT NULL DEFAULT 0.00,
  `t_primer` int(11) NOT NULL DEFAULT 0,
  `t_sekunder` int(11) NOT NULL DEFAULT 0,
  `t_tersier` int(11) NOT NULL DEFAULT 0,
  `t_kuarter` int(11) NOT NULL DEFAULT 0,
  `d_primer` decimal(4,1) NOT NULL DEFAULT 0.0,
  `d_sekunder` decimal(5,2) NOT NULL DEFAULT 0.00,
  `d_tersier` decimal(6,3) NOT NULL DEFAULT 0.000,
  `d_kuarter` decimal(7,4) NOT NULL DEFAULT 0.0000,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `user_input` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`kd_comp`,`tgltanam`,`no_urut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `closing_hpt_hdr` */

DROP TABLE IF EXISTS `closing_hpt_hdr`;

CREATE TABLE `closing_hpt_hdr` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `kd_comp` char(4) NOT NULL DEFAULT '',
  `kd_blok` char(2) NOT NULL DEFAULT '',
  `kd_plot` char(5) NOT NULL DEFAULT '',
  `kd_plotsample` char(5) NOT NULL DEFAULT '',
  `varietas` varchar(10) NOT NULL DEFAULT '',
  `tgltanam` date NOT NULL,
  `tglamat` date NOT NULL,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `user_input` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`kd_comp`,`tgltanam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `closing_hpt_lst` */

DROP TABLE IF EXISTS `closing_hpt_lst`;

CREATE TABLE `closing_hpt_lst` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `kd_comp` char(4) NOT NULL DEFAULT '',
  `tgltanam` date NOT NULL,
  `no_urut` int(11) NOT NULL DEFAULT 0,
  `jm_batang` int(11) NOT NULL DEFAULT 0,
  `ppt` int(11) NOT NULL DEFAULT 0,
  `pbt` int(11) NOT NULL DEFAULT 0,
  `skor0` int(11) NOT NULL DEFAULT 0,
  `skor1` int(11) NOT NULL DEFAULT 0,
  `skor2` int(11) NOT NULL DEFAULT 0,
  `skor3` int(11) NOT NULL DEFAULT 0,
  `skor4` int(11) NOT NULL DEFAULT 0,
  `per_ppt` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_ppt_aktif` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_pbt` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_pbt_aktif` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `sum_ni` int(11) NOT NULL DEFAULT 0,
  `int_rusak` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `telur_ppt` int(11) NOT NULL DEFAULT 0,
  `larva_ppt1` int(11) NOT NULL DEFAULT 0,
  `larva_ppt2` int(11) NOT NULL DEFAULT 0,
  `larva_ppt3` int(11) NOT NULL DEFAULT 0,
  `larva_ppt4` int(11) NOT NULL DEFAULT 0,
  `pupa_ppt` int(11) NOT NULL DEFAULT 0,
  `ngengat_ppt` int(11) NOT NULL DEFAULT 0,
  `kosong_ppt` int(11) NOT NULL DEFAULT 0,
  `telur_pbt` int(11) NOT NULL DEFAULT 0,
  `larva_pbt1` int(11) NOT NULL DEFAULT 0,
  `larva_pbt2` int(11) NOT NULL DEFAULT 0,
  `larva_pbt3` int(11) NOT NULL DEFAULT 0,
  `larva_pbt4` int(11) NOT NULL DEFAULT 0,
  `pupa_pbt` int(11) NOT NULL DEFAULT 0,
  `ngengat_pbt` int(11) NOT NULL DEFAULT 0,
  `kosong_pbt` int(11) NOT NULL DEFAULT 0,
  `dh` int(11) NOT NULL DEFAULT 0,
  `dt` int(11) NOT NULL DEFAULT 0,
  `kbp` int(11) NOT NULL DEFAULT 0,
  `kbb` int(11) NOT NULL DEFAULT 0,
  `kp` int(11) NOT NULL DEFAULT 0,
  `cabuk` int(11) NOT NULL DEFAULT 0,
  `belalang` int(11) NOT NULL DEFAULT 0,
  `serang_grayak` int(11) NOT NULL DEFAULT 0,
  `jum_grayak` int(11) NOT NULL DEFAULT 0,
  `serang_smut` int(11) NOT NULL DEFAULT 0,
  `smut_stadia1` int(11) NOT NULL DEFAULT 0,
  `smut_stadia2` int(11) NOT NULL DEFAULT 0,
  `smut_stadia3` int(11) NOT NULL DEFAULT 0,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `user_input` varchar(50) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`kd_comp`,`tgltanam`,`no_urut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `company` */

DROP TABLE IF EXISTS `company`;

CREATE TABLE `company` (
  `companycode` char(4) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `address` text NOT NULL,
  `companyinventory` varchar(5) DEFAULT NULL,
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  `companyperiod` date DEFAULT NULL,
  `companygl` varchar(5) DEFAULT NULL,
  `companygroup` varchar(4) DEFAULT NULL,
  PRIMARY KEY (`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `gps_hdr` */

DROP TABLE IF EXISTS `gps_hdr`;

CREATE TABLE `gps_hdr` (
  `fid` int(11) NOT NULL DEFAULT 0,
  `petak` varchar(10) NOT NULL DEFAULT '',
  `divisi` varchar(5) NOT NULL DEFAULT '',
  `blok` char(1) NOT NULL DEFAULT '',
  `luas` decimal(10,3) NOT NULL DEFAULT 0.000,
  `shape_leng` decimal(20,15) NOT NULL DEFAULT 0.000000000000000,
  `shape_area` decimal(20,15) NOT NULL DEFAULT 0.000000000000000,
  `companycode` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`fid`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Table structure for table `gps_lst` */

DROP TABLE IF EXISTS `gps_lst`;

CREATE TABLE `gps_lst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL DEFAULT 0,
  `lon` decimal(20,15) NOT NULL DEFAULT 0.000000000000000,
  `lat` decimal(20,15) NOT NULL DEFAULT 0.000000000000000,
  `companycode` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`companycode`)
) ENGINE=InnoDB AUTO_INCREMENT=169495 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Table structure for table `gpshdr` */

DROP TABLE IF EXISTS `gpshdr`;

CREATE TABLE `gpshdr` (
  `fid` int(11) NOT NULL DEFAULT 0,
  `divisi` varchar(10) NOT NULL DEFAULT '',
  `blok` char(3) NOT NULL DEFAULT '',
  `plot` varchar(10) NOT NULL DEFAULT '',
  `luas` decimal(10,3) NOT NULL DEFAULT 0.000,
  `companycode` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`fid`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Table structure for table `gpslst` */

DROP TABLE IF EXISTS `gpslst`;

CREATE TABLE `gpslst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) NOT NULL DEFAULT 0,
  `longitude` decimal(20,15) NOT NULL DEFAULT 0.000000000000000,
  `latitude` decimal(20,15) NOT NULL DEFAULT 0.000000000000000,
  `companycode` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`companycode`)
) ENGINE=InnoDB AUTO_INCREMENT=169495 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Table structure for table `hargapanen` */

DROP TABLE IF EXISTS `hargapanen`;

CREATE TABLE `hargapanen` (
  `companycode` varchar(5) DEFAULT NULL,
  `tebangmanual` int(11) DEFAULT NULL,
  `muatmanual` int(11) DEFAULT NULL,
  `angkutmanual` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `hargapanentebu` */

DROP TABLE IF EXISTS `hargapanentebu`;

CREATE TABLE `hargapanentebu` (
  `kodeharga` varchar(10) DEFAULT NULL,
  `companycode` char(6) DEFAULT NULL,
  `periode` char(4) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `manualtebang` int(11) DEFAULT NULL,
  `manualmuat` int(11) DEFAULT NULL,
  `manualangkutan` int(11) DEFAULT NULL,
  `manualfeekont` int(11) DEFAULT NULL,
  `manualnonpremi` int(11) DEFAULT NULL,
  `manualjumlah1` int(11) DEFAULT NULL,
  `manualbsm` int(11) DEFAULT NULL,
  `manualtebusulit` int(11) DEFAULT NULL,
  `manualpremiton` int(11) DEFAULT NULL,
  `manualjumlah2` int(11) DEFAULT NULL,
  `glkebuntebang` int(11) DEFAULT NULL,
  `glkebunmuat` int(11) DEFAULT NULL,
  `glkebunangkutan` int(11) DEFAULT NULL,
  `glkebunfeekont` int(11) DEFAULT NULL,
  `glkebunnonpremi` int(11) DEFAULT NULL,
  `glkebunjumlah1` int(11) DEFAULT NULL,
  `glkebunbsm` int(11) DEFAULT NULL,
  `glkebuntebusulit` int(11) DEFAULT NULL,
  `glkebunpremiton` int(11) DEFAULT NULL,
  `glkebunjumlah2` int(11) DEFAULT NULL,
  `glkontraktortebang` int(11) DEFAULT NULL,
  `glkontraktormuat` int(11) DEFAULT NULL,
  `glkontraktorangkutan` int(11) DEFAULT NULL,
  `glkontraktorfeekont` int(11) DEFAULT NULL,
  `glkontraktornonpremi` int(11) DEFAULT NULL,
  `glkontraktorjumlah1` int(11) DEFAULT NULL,
  `glkontraktorbsm` int(11) DEFAULT NULL,
  `glkontraktortebusulit` int(11) DEFAULT NULL,
  `glkontraktorpremiton` int(11) DEFAULT NULL,
  `glkontraktorjumlah2` int(11) DEFAULT NULL,
  `extrafooding` int(11) DEFAULT NULL,
  `tebutdkseset` int(11) DEFAULT NULL,
  `langsir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `herbisida` */

DROP TABLE IF EXISTS `herbisida`;

CREATE TABLE `herbisida` (
  `companycode` char(4) NOT NULL,
  `itemcode` varchar(30) NOT NULL,
  `jenis` varchar(30) DEFAULT NULL,
  `itemname` varchar(50) NOT NULL,
  `measure` varchar(10) DEFAULT NULL,
  `isactive` tinyint(4) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  `factoryinv` varchar(4) DEFAULT NULL,
  `companyinv` char(4) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`itemcode`),
  CONSTRAINT `fk_herbisida_company_companycode` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `herbisidadosage` */

DROP TABLE IF EXISTS `herbisidadosage`;

CREATE TABLE `herbisidadosage` (
  `companycode` char(4) NOT NULL,
  `herbisidagroupid` tinyint(3) NOT NULL,
  `itemcode` varchar(30) NOT NULL,
  `dosageperha` float DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`companycode`,`herbisidagroupid`,`itemcode`),
  KEY `fk_herbisidadosage_herbisida_itemcode` (`companycode`,`itemcode`),
  KEY `fk_herbisidadosage_herbisidagroup_id` (`herbisidagroupid`),
  CONSTRAINT `fk_herbisidadosage_company_companycode` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_herbisidadosage_herbisida_itemcode` FOREIGN KEY (`companycode`, `itemcode`) REFERENCES `herbisida` (`companycode`, `itemcode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_herbisidadosage_herbisidagroup_id` FOREIGN KEY (`herbisidagroupid`) REFERENCES `herbisidagroup` (`herbisidagroupid`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `herbisidagroup` */

DROP TABLE IF EXISTS `herbisidagroup`;

CREATE TABLE `herbisidagroup` (
  `herbisidagroupid` tinyint(3) NOT NULL,
  `herbisidagroupname` varchar(30) DEFAULT NULL,
  `activitycode` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `rounddosage` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`herbisidagroupid`),
  KEY `fk_herbisidagroup_activity_activitycode` (`activitycode`),
  CONSTRAINT `fk_herbisidagroup_activity_activitycode` FOREIGN KEY (`activitycode`) REFERENCES `activity` (`activitycode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `hpt_hdr` */

DROP TABLE IF EXISTS `hpt_hdr`;

CREATE TABLE `hpt_hdr` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plotcode` char(5) NOT NULL DEFAULT '',
  `plotcodesample` char(5) NOT NULL DEFAULT '',
  `varietas` varchar(10) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL,
  `tglamat` date NOT NULL,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`companycode`,`tanggaltanam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `hpt_lst` */

DROP TABLE IF EXISTS `hpt_lst`;

CREATE TABLE `hpt_lst` (
  `no_sample` char(4) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL,
  `nourut` int(11) NOT NULL DEFAULT 0,
  `jm_batang` int(11) NOT NULL DEFAULT 0,
  `ppt` int(11) NOT NULL DEFAULT 0,
  `ppt_aktif` int(11) NOT NULL DEFAULT 0,
  `pbt` int(11) NOT NULL DEFAULT 0,
  `pbt_aktif` int(11) NOT NULL DEFAULT 0,
  `skor0` int(11) NOT NULL DEFAULT 0,
  `skor1` int(11) NOT NULL DEFAULT 0,
  `skor2` int(11) NOT NULL DEFAULT 0,
  `skor3` int(11) NOT NULL DEFAULT 0,
  `skor4` int(11) NOT NULL DEFAULT 0,
  `per_ppt` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_ppt_aktif` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_pbt` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_pbt_aktif` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `sum_ni` int(11) NOT NULL DEFAULT 0,
  `int_rusak` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `telur_ppt` int(11) NOT NULL DEFAULT 0,
  `larva_ppt1` int(11) NOT NULL DEFAULT 0,
  `larva_ppt2` int(11) NOT NULL DEFAULT 0,
  `larva_ppt3` int(11) NOT NULL DEFAULT 0,
  `larva_ppt4` int(11) NOT NULL DEFAULT 0,
  `pupa_ppt` int(11) NOT NULL DEFAULT 0,
  `ngengat_ppt` int(11) NOT NULL DEFAULT 0,
  `kosong_ppt` int(11) NOT NULL DEFAULT 0,
  `telur_pbt` int(11) NOT NULL DEFAULT 0,
  `larva_pbt1` int(11) NOT NULL DEFAULT 0,
  `larva_pbt2` int(11) NOT NULL DEFAULT 0,
  `larva_pbt3` int(11) NOT NULL DEFAULT 0,
  `larva_pbt4` int(11) NOT NULL DEFAULT 0,
  `pupa_pbt` int(11) NOT NULL DEFAULT 0,
  `ngengat_pbt` int(11) NOT NULL DEFAULT 0,
  `kosong_pbt` int(11) NOT NULL DEFAULT 0,
  `dh` int(11) NOT NULL DEFAULT 0,
  `dt` int(11) NOT NULL DEFAULT 0,
  `kbp` int(11) NOT NULL DEFAULT 0,
  `kbb` int(11) NOT NULL DEFAULT 0,
  `kp` int(11) NOT NULL DEFAULT 0,
  `cabuk` int(11) NOT NULL DEFAULT 0,
  `belalang` int(11) NOT NULL DEFAULT 0,
  `serang_grayak` int(11) NOT NULL DEFAULT 0,
  `jum_grayak` int(11) NOT NULL DEFAULT 0,
  `serang_smut` int(11) NOT NULL DEFAULT 0,
  `smut_stadia1` int(11) NOT NULL DEFAULT 0,
  `smut_stadia2` int(11) NOT NULL DEFAULT 0,
  `smut_stadia3` int(11) NOT NULL DEFAULT 0,
  `jum_larva_ppt` int(11) NOT NULL DEFAULT 0,
  `jum_larva_pbt` int(11) NOT NULL DEFAULT 0,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no_sample`,`companycode`,`tanggaltanam`,`nourut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `hpthdr` */

DROP TABLE IF EXISTS `hpthdr`;

CREATE TABLE `hpthdr` (
  `nosample` char(4) NOT NULL DEFAULT '',
  `companycode` char(6) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plot` char(10) NOT NULL DEFAULT '',
  `varietas` varchar(10) NOT NULL DEFAULT '',
  `kat` char(3) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL DEFAULT '0000-00-00',
  `tanggalpengamatan` date NOT NULL DEFAULT '0000-00-00',
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `tanggalposting` date DEFAULT NULL,
  `closingperiode` enum('T','F') NOT NULL DEFAULT 'F',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updatedat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nosample`,`companycode`,`tanggalpengamatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `hptlst` */

DROP TABLE IF EXISTS `hptlst`;

CREATE TABLE `hptlst` (
  `nosample` char(4) NOT NULL DEFAULT '',
  `companycode` char(6) NOT NULL DEFAULT '',
  `tanggaltanam` date NOT NULL DEFAULT '0000-00-00',
  `tanggalpengamatan` date NOT NULL DEFAULT '0000-00-00',
  `kat` char(3) NOT NULL DEFAULT '',
  `nourut` int(11) NOT NULL DEFAULT 0,
  `jumlahbatang` int(11) NOT NULL DEFAULT 0,
  `ppt` int(11) NOT NULL DEFAULT 0,
  `ppt_aktif` int(11) NOT NULL DEFAULT 0,
  `pbt` int(11) NOT NULL DEFAULT 0,
  `pbt_aktif` int(11) NOT NULL DEFAULT 0,
  `skor0` int(11) NOT NULL DEFAULT 0,
  `skor1` int(11) NOT NULL DEFAULT 0,
  `skor2` int(11) NOT NULL DEFAULT 0,
  `skor3` int(11) NOT NULL DEFAULT 0,
  `skor4` int(11) NOT NULL DEFAULT 0,
  `per_ppt` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_ppt_aktif` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_pbt` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `per_pbt_aktif` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `sum_ni` int(11) NOT NULL DEFAULT 0,
  `int_rusak` decimal(12,9) NOT NULL DEFAULT 0.000000000,
  `telur_ppt` int(11) NOT NULL DEFAULT 0,
  `larva_ppt1` int(11) NOT NULL DEFAULT 0,
  `larva_ppt2` int(11) NOT NULL DEFAULT 0,
  `larva_ppt3` int(11) NOT NULL DEFAULT 0,
  `larva_ppt4` int(11) NOT NULL DEFAULT 0,
  `pupa_ppt` int(11) NOT NULL DEFAULT 0,
  `ngengat_ppt` int(11) NOT NULL DEFAULT 0,
  `kosong_ppt` int(11) NOT NULL DEFAULT 0,
  `telur_pbt` int(11) NOT NULL DEFAULT 0,
  `larva_pbt1` int(11) NOT NULL DEFAULT 0,
  `larva_pbt2` int(11) NOT NULL DEFAULT 0,
  `larva_pbt3` int(11) NOT NULL DEFAULT 0,
  `larva_pbt4` int(11) NOT NULL DEFAULT 0,
  `pupa_pbt` int(11) NOT NULL DEFAULT 0,
  `ngengat_pbt` int(11) NOT NULL DEFAULT 0,
  `kosong_pbt` int(11) NOT NULL DEFAULT 0,
  `dh` int(11) NOT NULL DEFAULT 0,
  `dt` int(11) NOT NULL DEFAULT 0,
  `kbp` int(11) NOT NULL DEFAULT 0,
  `kbb` int(11) NOT NULL DEFAULT 0,
  `kp` int(11) NOT NULL DEFAULT 0,
  `cabuk` int(11) NOT NULL DEFAULT 0,
  `belalang` int(11) NOT NULL DEFAULT 0,
  `serang_grayak` int(11) NOT NULL DEFAULT 0,
  `jum_grayak` int(11) NOT NULL DEFAULT 0,
  `serang_smut` int(11) NOT NULL DEFAULT 0,
  `smut_stadia1` int(11) NOT NULL DEFAULT 0,
  `smut_stadia2` int(11) NOT NULL DEFAULT 0,
  `smut_stadia3` int(11) NOT NULL DEFAULT 0,
  `jum_larva_ppt` int(11) NOT NULL DEFAULT 0,
  `jum_larva_pbt` int(11) NOT NULL DEFAULT 0,
  `status` enum('Posted','Unposted') NOT NULL DEFAULT 'Unposted',
  `count` int(11) NOT NULL DEFAULT 0,
  `closingperiode` enum('T','F') NOT NULL DEFAULT 'F',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updatedat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`nosample`,`companycode`,`tanggalpengamatan`,`nourut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `jabatan` */

DROP TABLE IF EXISTS `jabatan`;

CREATE TABLE `jabatan` (
  `idjabatan` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `namajabatan` varchar(30) NOT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idjabatan`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `jabatanpermission` */

DROP TABLE IF EXISTS `jabatanpermission`;

CREATE TABLE `jabatanpermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idjabatan` tinyint(3) unsigned NOT NULL,
  `permissionid` int(11) NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `grantedby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniquejabatanpermission` (`idjabatan`,`permissionid`),
  KEY `idxjabatan` (`idjabatan`),
  KEY `idxpermission` (`permissionid`),
  KEY `idxactive` (`isactive`),
  CONSTRAINT `fkjabatanpermjabatan` FOREIGN KEY (`idjabatan`) REFERENCES `jabatan` (`idjabatan`) ON DELETE CASCADE,
  CONSTRAINT `fkjabatanpermpermission` FOREIGN KEY (`permissionid`) REFERENCES `permission` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1843 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role-based permissions (Jabatan = Role)';

/*Table structure for table `jenistenagakerja` */

DROP TABLE IF EXISTS `jenistenagakerja`;

CREATE TABLE `jenistenagakerja` (
  `idjenistenagakerja` varchar(5) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idjenistenagakerja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `kategoriupah` */

DROP TABLE IF EXISTS `kategoriupah`;

CREATE TABLE `kategoriupah` (
  `id` tinyint(3) NOT NULL,
  `jenisupah` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `kendaraan` */

DROP TABLE IF EXISTS `kendaraan`;

CREATE TABLE `kendaraan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `companycode` varchar(5) NOT NULL,
  `idtenagakerja` varchar(11) DEFAULT NULL,
  `nokendaraan` varchar(30) NOT NULL,
  `companygroup` varchar(4) DEFAULT NULL,
  `hourmeter` float DEFAULT 0,
  `jenis` varchar(50) DEFAULT NULL,
  `tahunterima` varchar(4) DEFAULT NULL,
  `statuskendaraan` varchar(50) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `updatedate` datetime DEFAULT NULL,
  `isactive` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kendaraan_natural` (`companycode`,`nokendaraan`),
  KEY `idx_kendaraan_active` (`companycode`,`isactive`),
  KEY `idx_kendaraan_operator` (`idtenagakerja`,`isactive`),
  CONSTRAINT `fk_kendaraan_company` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_kendaraan_tenagakerja` FOREIGN KEY (`idtenagakerja`) REFERENCES `tenagakerja` (`tenagakerjaid`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `kontraktor` */

DROP TABLE IF EXISTS `kontraktor`;

CREATE TABLE `kontraktor` (
  `id` varchar(10) NOT NULL,
  `companycode` char(4) NOT NULL,
  `namakontraktor` varchar(100) NOT NULL,
  `isactive` tinyint(4) DEFAULT 1,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`companycode`),
  KEY `idx_kontraktor_company` (`companycode`),
  KEY `idx_kontraktor_active` (`isactive`),
  CONSTRAINT `fk_kontraktor_company_companycode` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `lkhdetailbsm` */

DROP TABLE IF EXISTS `lkhdetailbsm`;

CREATE TABLE `lkhdetailbsm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `lkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `suratjalanno` varchar(30) DEFAULT NULL,
  `plot` char(5) NOT NULL,
  `kodetebang` varchar(15) DEFAULT NULL,
  `batchno` varchar(20) DEFAULT NULL,
  `batchid` bigint(20) unsigned DEFAULT NULL,
  `nilaibersih` decimal(6,2) DEFAULT NULL COMMENT 'Nilai B (Bersih)',
  `nilaisegar` decimal(6,2) DEFAULT NULL COMMENT 'Nilai S (Segar)',
  `nilaimanis` decimal(6,2) DEFAULT NULL COMMENT 'Nilai M (Manis)',
  `averagescore` decimal(6,2) DEFAULT NULL COMMENT 'Rata-rata BSM',
  `grade` enum('A','B','C') DEFAULT NULL COMMENT 'Grade kualitas',
  `keterangan` text DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `parentbsm` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lkhno` (`lkhno`),
  KEY `idx_plot` (`companycode`,`plot`),
  KEY `idx_batchno` (`batchno`),
  KEY `idx_batchid` (`batchid`),
  KEY `idx_lkhhdrid` (`lkhhdrid`),
  CONSTRAINT `fk_lkhdetailbsm_batch` FOREIGN KEY (`batchid`) REFERENCES `batch` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_lkhdetailbsm_company` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_lkhdetailbsm_lkh` FOREIGN KEY (`lkhno`) REFERENCES `lkhhdr` (`lkhno`) ON DELETE CASCADE,
  CONSTRAINT `fk_lkhdetailbsm_lkhhdr` FOREIGN KEY (`lkhhdrid`) REFERENCES `lkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BSM (Bersih Segar Manis) inspection results per plot';

/*Table structure for table `lkhdetailkendaraan` */

DROP TABLE IF EXISTS `lkhdetailkendaraan`;

CREATE TABLE `lkhdetailkendaraan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `lkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `nokendaraan` varchar(10) NOT NULL,
  `kendaraanid` int(10) unsigned DEFAULT NULL,
  `operatorid` varchar(11) NOT NULL,
  `helperid` varchar(50) DEFAULT NULL,
  `jammulai` time DEFAULT NULL,
  `jamselesai` time DEFAULT NULL,
  `hourmeterstart` decimal(10,2) DEFAULT NULL,
  `hourmeterend` decimal(10,2) DEFAULT NULL,
  `solar` decimal(10,3) DEFAULT NULL,
  `adminupdateby` varchar(100) DEFAULT NULL,
  `adminupdatedat` timestamp NULL DEFAULT NULL,
  `ordernumber` varchar(10) DEFAULT NULL,
  `printedby` varchar(100) DEFAULT NULL,
  `printedat` timestamp NULL DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL COMMENT 'INPUTTED, PRINTED',
  `gudangconfirm` tinyint(1) DEFAULT 0,
  `gudangconfirmedby` varchar(100) DEFAULT NULL,
  `gudangconfirmedat` timestamp NULL DEFAULT NULL,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lkh_kendaraan` (`companycode`,`lkhhdrid`,`nokendaraan`),
  KEY `idx_lkhno` (`lkhno`),
  KEY `idx_kendaraan` (`nokendaraan`),
  KEY `idx_operator` (`operatorid`),
  KEY `idx_status` (`status`),
  KEY `idx_ordernumber` (`ordernumber`),
  KEY `idx_lkhhdrid` (`lkhhdrid`),
  KEY `idx_kendaraanid` (`kendaraanid`),
  CONSTRAINT `fk_lkhdetailkendaraan_kendaraan` FOREIGN KEY (`kendaraanid`) REFERENCES `kendaraan` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_lkhdetailkendaraan_lkhhdr` FOREIGN KEY (`lkhhdrid`) REFERENCES `lkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `lkhdetailmaterial` */

DROP TABLE IF EXISTS `lkhdetailmaterial`;

CREATE TABLE `lkhdetailmaterial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `lkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `plot` varchar(10) DEFAULT NULL,
  `itemcode` varchar(30) NOT NULL,
  `qtyditerima` decimal(10,3) DEFAULT 0.000,
  `qtysisa` decimal(10,3) DEFAULT 0.000,
  `qtydigunakan` decimal(10,3) DEFAULT 0.000,
  `keterangan` varchar(255) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_company_lkh` (`companycode`,`lkhno`),
  KEY `idx_lkhhdrid` (`lkhhdrid`),
  CONSTRAINT `fk_lkhdetailmaterial_lkhhdr` FOREIGN KEY (`lkhhdrid`) REFERENCES `lkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2473 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `lkhdetailplot` */

DROP TABLE IF EXISTS `lkhdetailplot`;

CREATE TABLE `lkhdetailplot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `lkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `blok` char(3) NOT NULL DEFAULT '',
  `plot` varchar(10) DEFAULT NULL,
  `luasrkh` decimal(10,2) DEFAULT 0.00 COMMENT 'Luas dari RKH planning',
  `luashasil` decimal(10,2) DEFAULT 0.00 COMMENT 'Luas hasil actual work',
  `luassisa` decimal(10,2) DEFAULT NULL COMMENT 'Auto-calculated: luasrkh - luashasil',
  `rework` tinyint(1) DEFAULT 0 COMMENT '1=rework (bypass luas sisa), 0=normal',
  `createdat` datetime DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `batchno` varchar(20) DEFAULT NULL,
  `batchid` bigint(20) unsigned DEFAULT NULL,
  `fieldbalancerit` decimal(10,2) DEFAULT NULL COMMENT 'Field balance in rit (trips)',
  `fieldbalanceton` decimal(10,2) DEFAULT NULL COMMENT 'Field balance in ton (weight)',
  `keterangan` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_lkhno` (`lkhno`),
  KEY `idx_blok_plot` (`blok`,`plot`),
  KEY `idx_batchid` (`batchid`),
  KEY `idx_lkhhdrid` (`lkhhdrid`),
  CONSTRAINT `fk_lkhdetailplot_batch` FOREIGN KEY (`batchid`) REFERENCES `batch` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_lkhdetailplot_lkhhdr` FOREIGN KEY (`lkhhdrid`) REFERENCES `lkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7494 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='LKH Plot Details - Updated structure for Option B material tracking';

/*Table structure for table `lkhdetailworker` */

DROP TABLE IF EXISTS `lkhdetailworker`;

CREATE TABLE `lkhdetailworker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `lkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `tenagakerjaid` varchar(11) NOT NULL,
  `tenagakerjaurutan` int(3) NOT NULL,
  `jammasuk` time DEFAULT NULL,
  `jamselesai` time DEFAULT NULL,
  `totaljamkerja` decimal(4,2) DEFAULT 0.00,
  `overtimehours` decimal(4,2) DEFAULT 0.00,
  `premi` decimal(10,2) DEFAULT 0.00,
  `upahharian` decimal(10,2) DEFAULT 0.00,
  `upahperjam` decimal(10,2) DEFAULT 0.00,
  `upahlembur` decimal(10,2) DEFAULT 0.00,
  `upahborongan` decimal(12,2) DEFAULT 0.00,
  `totalupah` decimal(12,2) DEFAULT 0.00,
  `keterangan` varchar(255) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lkhno` (`lkhno`),
  KEY `idx_tenagakerja` (`tenagakerjaid`),
  KEY `idx_lkhhdrid` (`lkhhdrid`),
  CONSTRAINT `fk_lkhdetailworker_lkhhdr` FOREIGN KEY (`lkhhdrid`) REFERENCES `lkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6652 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `lkhfotolampiran` */

DROP TABLE IF EXISTS `lkhfotolampiran`;

CREATE TABLE `lkhfotolampiran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lkhno` varchar(15) NOT NULL,
  `lkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `companycode` char(4) NOT NULL,
  `blok` char(3) DEFAULT NULL,
  `plot` varchar(10) DEFAULT NULL,
  `photopath` varchar(255) NOT NULL COMMENT 'S3 path to photo',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `accuracy` decimal(10,2) DEFAULT NULL COMMENT 'GPS accuracy in meters',
  `uploadfrom` enum('mobile','web') DEFAULT 'mobile',
  `createdat` datetime DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lkhno` (`lkhno`),
  KEY `idx_lkhhdrid` (`lkhhdrid`),
  KEY `idx_company_lkh` (`companycode`,`lkhno`),
  KEY `idx_blok_plot` (`blok`,`plot`),
  CONSTRAINT `fk_lkhfoto_lkhhdr` FOREIGN KEY (`lkhhdrid`) REFERENCES `lkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='LKH Photo Attachments - Multiple photos per LKH work';

/*Table structure for table `lkhhdr` */

DROP TABLE IF EXISTS `lkhhdr`;

CREATE TABLE `lkhhdr` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lkhno` varchar(15) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `rkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `companycode` char(4) NOT NULL,
  `activitycode` varchar(10) NOT NULL,
  `mandorid` varchar(30) NOT NULL,
  `lkhdate` date NOT NULL,
  `jenistenagakerja` tinyint(1) NOT NULL COMMENT '1=Harian, 2=Borongan',
  `totalworkers` int(11) DEFAULT 0,
  `totalluasactual` decimal(10,2) DEFAULT 0.00,
  `totalhasil` decimal(10,2) DEFAULT 0.00,
  `totalsisa` decimal(10,2) DEFAULT 0.00,
  `totalupahall` decimal(15,2) DEFAULT 0.00,
  `status` enum('EMPTY','DRAFT','COMPLETED','SUBMITTED','APPROVED') DEFAULT 'EMPTY',
  `keterangan` text DEFAULT NULL,
  `jumlahapproval` int(11) DEFAULT 0,
  `approval1idjabatan` int(10) DEFAULT NULL,
  `approval1userid` varchar(50) DEFAULT NULL,
  `approval1flag` char(1) DEFAULT NULL,
  `approval1date` datetime DEFAULT NULL,
  `approval2idjabatan` int(11) DEFAULT NULL,
  `approval2userid` varchar(50) DEFAULT NULL,
  `approval2flag` char(1) DEFAULT NULL,
  `approval2date` datetime DEFAULT NULL,
  `approval3idjabatan` int(11) DEFAULT NULL,
  `approval3userid` varchar(50) DEFAULT NULL,
  `approval3flag` char(1) DEFAULT NULL,
  `approval3date` datetime DEFAULT NULL,
  `approvalstatus` char(1) DEFAULT NULL COMMENT '1=Approved, 0=Rejected, NULL=In Progress',
  `issubmit` tinyint(1) DEFAULT 0,
  `submitby` varchar(50) DEFAULT NULL,
  `submitat` datetime DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL,
  `mobileupdatedat` datetime DEFAULT NULL,
  `mobile_status` enum('EMPTY','DRAFT','COMPLETED') DEFAULT 'EMPTY',
  `isedit` tinyint(1) DEFAULT 0,
  `editedby` varchar(50) DEFAULT NULL,
  `editedat` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lkhhdr_natural` (`lkhno`,`companycode`),
  KEY `idx_rkhno` (`rkhno`),
  KEY `idx_company_date` (`companycode`,`lkhdate`),
  KEY `idx_mandor_date` (`mandorid`,`lkhdate`),
  KEY `idx_lkhhdr_status` (`status`),
  KEY `idx_lkhhdr_jenistenaga` (`jenistenagakerja`),
  KEY `idx_lkhhdr_approvalstatus` (`approvalstatus`),
  KEY `idx_rkhhdrid` (`rkhhdrid`),
  CONSTRAINT `fk_lkhhdr_rkhhdr` FOREIGN KEY (`rkhhdrid`) REFERENCES `rkhhdr` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=847 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `log_closing` */

DROP TABLE IF EXISTS `log_closing`;

CREATE TABLE `log_closing` (
  `kd_comp` char(4) NOT NULL DEFAULT '',
  `tgl1` date NOT NULL,
  `tgl2` date NOT NULL,
  PRIMARY KEY (`kd_comp`,`tgl1`,`tgl2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `mapping` */

DROP TABLE IF EXISTS `mapping`;

CREATE TABLE `mapping` (
  `idblokplot` char(5) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plot` char(5) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idblokplot`,`blok`,`plot`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `mappingblokplot` */

DROP TABLE IF EXISTS `mappingblokplot`;

CREATE TABLE `mappingblokplot` (
  `idblokplot` char(5) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plot` char(5) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`idblokplot`,`blok`,`plot`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `masterlist` */

DROP TABLE IF EXISTS `masterlist`;

CREATE TABLE `masterlist` (
  `companycode` varchar(4) NOT NULL,
  `plot` char(5) NOT NULL,
  `blok` char(2) DEFAULT NULL,
  `activebatchno` varchar(20) DEFAULT NULL,
  `isactive` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`companycode`,`plot`),
  KEY `fk_activebatch` (`activebatchno`),
  KEY `idx_company_active` (`companycode`,`isactive`),
  CONSTRAINT `fk_activebatch` FOREIGN KEY (`activebatchno`) REFERENCES `batch` (`batchno`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `nfc` */

DROP TABLE IF EXISTS `nfc`;

CREATE TABLE `nfc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `mandorid` varchar(50) DEFAULT NULL COMMENT 'FK to user.userid, NULL = kantor/warehouse',
  `balance` int(11) DEFAULT 0 COMMENT 'Current card balance',
  `lasttransaction` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_holder` (`mandorid`,`companycode`),
  KEY `idx_mandor` (`mandorid`),
  KEY `idx_company` (`companycode`),
  CONSTRAINT `fk_nfc_company` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `nfctransaction` */

DROP TABLE IF EXISTS `nfctransaction`;

CREATE TABLE `nfctransaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transactionno` varchar(50) NOT NULL COMMENT 'Format: NFCYYYYMMDDXXXX',
  `companycode` char(4) NOT NULL,
  `transactiondate` date NOT NULL,
  `transactiontype` enum('OUT','IN') NOT NULL COMMENT 'OUT=keluar ke mandor, IN=balik ke kantor',
  `mandorid` varchar(30) NOT NULL COMMENT 'Mandor involved',
  `qty` int(11) NOT NULL COMMENT 'Quantity of cards',
  `notes` text DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`,`transactionno`),
  KEY `idx_mandor` (`mandorid`),
  KEY `idx_company` (`companycode`),
  KEY `idx_date` (`transactiondate`),
  KEY `idx_type` (`transactiontype`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `notification` */

DROP TABLE IF EXISTS `notification`;

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_type` enum('manual','support_ticket','system') NOT NULL DEFAULT 'manual',
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'support_ticket, lkh, material, etc',
  `reference_id` varchar(100) DEFAULT NULL COMMENT 'ticket_id, lkhno, etc',
  `companycode` varchar(500) NOT NULL COMMENT 'Comma-separated: SBK1,SBK2,SBK3',
  `target_jabatan` varchar(200) DEFAULT NULL COMMENT 'Comma-separated idjabatan: 1,7,10 (NULL=all jabatan)',
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL COMMENT 'Redirect URL when clicked',
  `icon` varchar(50) DEFAULT 'bell' COMMENT 'Icon name for UI',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('active','archived','deleted') DEFAULT 'active',
  `readby` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of userids who have read this' CHECK (json_valid(`readby`)),
  `inputby` varchar(50) NOT NULL,
  `createdat` timestamp NULL DEFAULT current_timestamp(),
  `updatedat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `idx_companycode` (`companycode`(100)),
  KEY `idx_type` (`notification_type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_status_created` (`status`,`createdat`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `openrework` */

DROP TABLE IF EXISTS `openrework`;

CREATE TABLE `openrework` (
  `transactionnumber` varchar(20) NOT NULL,
  `companycode` char(4) NOT NULL,
  `requestdate` date NOT NULL,
  `plots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '["A001", "A002"]' CHECK (json_valid(`plots`)),
  `activities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '["4.1.1", "4.2.2"]' CHECK (json_valid(`activities`)),
  `reason` text DEFAULT NULL,
  `inputby` varchar(50) NOT NULL,
  `createdat` datetime NOT NULL,
  PRIMARY KEY (`transactionnumber`,`companycode`),
  KEY `idx_company_date` (`companycode`,`requestdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `panentebuhistory` */

DROP TABLE IF EXISTS `panentebuhistory`;

CREATE TABLE `panentebuhistory` (
  `nodoc` varchar(20) NOT NULL,
  `companycode` char(4) NOT NULL,
  `userid` varchar(100) NOT NULL,
  `idkontraktor` varchar(20) NOT NULL,
  `namakontraktor` varchar(100) NOT NULL,
  `kodeharga` varchar(10) NOT NULL,
  `startdate` date NOT NULL,
  `enddate` date NOT NULL,
  `grandtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `dataresult` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dataresult`)),
  `hargasnapshot` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`hargasnapshot`)),
  `hargaperplot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`hargaperplot`)),
  `createdat` timestamp NULL DEFAULT current_timestamp(),
  `updatedat` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`nodoc`),
  KEY `idx_company_created` (`companycode`,`createdat`),
  KEY `idx_pembuat_company` (`userid`,`companycode`),
  KEY `idx_created_at` (`createdat`),
  KEY `idx_kode_harga` (`kodeharga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `permission` */

DROP TABLE IF EXISTS `permission`;

CREATE TABLE `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module` varchar(30) NOT NULL COMMENT 'masterdata, input, report, dashboard, process, usermanagement, pabrik',
  `resource` varchar(50) NOT NULL COMMENT 'company, blok, plotting, agronomi, user, etc',
  `action` varchar(30) NOT NULL COMMENT 'view, create, edit, delete, export, approve, etc',
  `displayname` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniquepermission` (`module`,`resource`,`action`),
  KEY `idxmodule` (`module`),
  KEY `idxresource` (`resource`),
  KEY `idxaction` (`action`),
  KEY `idxactive` (`isactive`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permission master - Format: module.resource.action';

/*Table structure for table `personal_access_tokens` */

DROP TABLE IF EXISTS `personal_access_tokens`;

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `piashdr` */

DROP TABLE IF EXISTS `piashdr`;

CREATE TABLE `piashdr` (
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `generateddate` datetime DEFAULT NULL,
  `tj` decimal(10,3) DEFAULT NULL,
  `tc` decimal(10,3) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `statustj` smallint(6) DEFAULT NULL,
  `statustc` smallint(6) DEFAULT NULL,
  `updateddate` datetime DEFAULT NULL,
  `sisatj` int(11) DEFAULT NULL,
  `sisatc` int(11) DEFAULT NULL,
  `dosage` smallint(6) DEFAULT NULL,
  `totalneedtj` decimal(10,3) DEFAULT NULL,
  `totalneedtc` decimal(10,3) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`rkhno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `piaslst` */

DROP TABLE IF EXISTS `piaslst`;

CREATE TABLE `piaslst` (
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `blok` char(2) NOT NULL,
  `plot` char(5) NOT NULL,
  `tj` decimal(10,3) DEFAULT NULL,
  `tc` decimal(10,3) DEFAULT NULL,
  `needtj` decimal(10,3) DEFAULT NULL,
  `needtc` decimal(10,3) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`rkhno`,`lkhno`,`blok`,`plot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `plottransaction` */

DROP TABLE IF EXISTS `plottransaction`;

CREATE TABLE `plottransaction` (
  `transactionnumber` varchar(20) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `transactiontype` enum('SPLIT','MERGE') NOT NULL,
  `transactiondate` date NOT NULL,
  `sourceplots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '["A001"] or ["A001", "A056"]' CHECK (json_valid(`sourceplots`)),
  `resultplots` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '["A001", "A056"] or ["A001"]' CHECK (json_valid(`resultplots`)),
  `sourcebatches` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '["BATCH001"]' CHECK (json_valid(`sourcebatches`)),
  `resultbatches` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '["BATCH002", "BATCH003"]' CHECK (json_valid(`resultbatches`)),
  `areamap` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '{"A001": 3.0, "A056": 2.0}' CHECK (json_valid(`areamap`)),
  `dominantplot` char(5) NOT NULL COMMENT 'Plot area terbesar (keep name)',
  `splitmergedreason` text DEFAULT NULL,
  `inputby` varchar(50) NOT NULL,
  `createdat` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_transaction` (`companycode`,`transactionnumber`),
  KEY `idx_company_date` (`companycode`,`transactiondate`),
  KEY `idx_dominant` (`companycode`,`dominantplot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `rkhauditlog` */

DROP TABLE IF EXISTS `rkhauditlog`;

CREATE TABLE `rkhauditlog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `actiontype` enum('DELETE','CHANGE_DATE') NOT NULL COMMENT 'Type of audit action',
  `rkhdate` date DEFAULT NULL,
  `olddate` date DEFAULT NULL COMMENT 'Old RKH date (for CHANGE_DATE action)',
  `newdate` date DEFAULT NULL COMMENT 'New RKH date (for CHANGE_DATE action)',
  `affectedtablessummary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Count of related records deleted from each table' CHECK (json_valid(`affectedtablessummary`)),
  `hasmaterialimpact` tinyint(1) DEFAULT 0 COMMENT '1 if usematerialhdr/lst existed',
  `hassuratjalanimpact` tinyint(1) DEFAULT 0 COMMENT '1 if suratjalanpos existed',
  `hastimbanganimpact` tinyint(1) DEFAULT 0 COMMENT '1 if timbanganpayload existed',
  `materialusedsummary` longtext DEFAULT NULL COMMENT 'JSON of material used (nouse, itemcode, qty, etc)',
  `actionreason` text NOT NULL COMMENT 'Reason for deletion or date change',
  `actionby` varchar(50) NOT NULL COMMENT 'User who performed the action',
  `actionat` datetime NOT NULL COMMENT 'Timestamp when action occurred',
  PRIMARY KEY (`id`),
  KEY `idxrkhno` (`companycode`,`rkhno`),
  KEY `idxactionat` (`actionat`),
  KEY `idxactionby` (`actionby`),
  KEY `idxactiontype` (`actiontype`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Universal audit trail for RKH operations (DELETE, CHANGE_DATE) - retention: 2 years';

/*Table structure for table `rkhhdr` */

DROP TABLE IF EXISTS `rkhhdr`;

CREATE TABLE `rkhhdr` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `rkhdate` date DEFAULT NULL,
  `manpower` int(11) DEFAULT NULL,
  `totalluas` float DEFAULT NULL,
  `mandorid` varchar(30) DEFAULT NULL,
  `activitygroup` varchar(30) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `jumlahapproval` int(11) DEFAULT NULL,
  `approval1idjabatan` tinyint(3) DEFAULT NULL,
  `approval1userid` varchar(50) DEFAULT NULL,
  `approval1flag` char(1) DEFAULT NULL,
  `approval1date` datetime DEFAULT NULL,
  `approval2idjabatan` tinyint(3) DEFAULT NULL,
  `approval2userid` varchar(50) DEFAULT NULL,
  `approval2flag` char(1) DEFAULT NULL,
  `approval2date` datetime DEFAULT NULL,
  `approval3idjabatan` tinyint(3) DEFAULT NULL,
  `approval3userid` varchar(50) DEFAULT NULL,
  `approval3flag` char(1) DEFAULT NULL,
  `approval3date` datetime DEFAULT NULL,
  `isdownload` tinyint(1) DEFAULT NULL,
  `approvalstatus` char(1) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL,
  `batalat` datetime DEFAULT NULL COMMENT 'Timestamp pembatalan',
  `batalby` varchar(50) DEFAULT NULL COMMENT 'User yang membatalkan',
  `batalalasan` text DEFAULT NULL COMMENT 'Alasan pembatalan',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rkhhdr_natural` (`companycode`,`rkhno`)
) ENGINE=InnoDB AUTO_INCREMENT=816 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `rkhlst` */

DROP TABLE IF EXISTS `rkhlst`;

CREATE TABLE `rkhlst` (
  `companycode` char(4) DEFAULT NULL,
  `rkhno` varchar(11) DEFAULT NULL,
  `rkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `rkhdate` date DEFAULT NULL,
  `blok` char(3) NOT NULL DEFAULT '',
  `plot` char(5) DEFAULT NULL,
  `activitycode` varchar(50) DEFAULT NULL,
  `luasarea` decimal(6,2) DEFAULT NULL,
  `jenistenagakerja` tinyint(1) DEFAULT NULL,
  `usingmaterial` tinyint(4) DEFAULT NULL,
  `herbisidagroupid` tinyint(3) DEFAULT NULL,
  `batchno` varchar(20) DEFAULT NULL,
  `batchid` bigint(20) unsigned DEFAULT NULL,
  KEY `idx_batchid` (`batchid`),
  KEY `idx_rkhhdrid` (`rkhhdrid`),
  CONSTRAINT `fk_rkhlst_batch` FOREIGN KEY (`batchid`) REFERENCES `batch` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_rkhlst_rkhhdr` FOREIGN KEY (`rkhhdrid`) REFERENCES `rkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `rkhlstkendaraan` */

DROP TABLE IF EXISTS `rkhlstkendaraan`;

CREATE TABLE `rkhlstkendaraan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `rkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `activitycode` varchar(50) NOT NULL,
  `nokendaraan` varchar(10) NOT NULL,
  `kendaraanid` int(10) unsigned DEFAULT NULL,
  `operatorid` varchar(11) NOT NULL,
  `usinghelper` tinyint(1) DEFAULT 0,
  `helperid` varchar(50) DEFAULT NULL,
  `urutan` int(3) NOT NULL COMMENT 'Urutan kendaraan dalam activity',
  `createdat` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rkh_activity` (`rkhno`,`activitycode`),
  KEY `idx_operator` (`operatorid`),
  KEY `idx_kendaraan` (`nokendaraan`),
  KEY `idx_rkhhdrid` (`rkhhdrid`),
  KEY `idx_kendaraanid` (`kendaraanid`),
  CONSTRAINT `fk_rkhlstkendaraan_kendaraan` FOREIGN KEY (`kendaraanid`) REFERENCES `kendaraan` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_rkhlstkendaraan_rkhhdr` FOREIGN KEY (`rkhhdrid`) REFERENCES `rkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `rkhlstworker` */

DROP TABLE IF EXISTS `rkhlstworker`;

CREATE TABLE `rkhlstworker` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companycode` varchar(10) NOT NULL,
  `rkhno` varchar(20) NOT NULL,
  `rkhhdrid` bigint(20) unsigned DEFAULT NULL,
  `activitycode` varchar(20) NOT NULL,
  `jumlahlaki` int(11) NOT NULL DEFAULT 0,
  `jumlahperempuan` int(11) NOT NULL DEFAULT 0,
  `jumlahtenagakerja` int(11) NOT NULL DEFAULT 0,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rkhlstworker` (`companycode`,`rkhno`,`activitycode`),
  KEY `idx_rkhlstworker_rkhno` (`companycode`,`rkhno`),
  KEY `idx_rkhhdrid` (`rkhhdrid`),
  CONSTRAINT `fk_rkhlstworker_rkhhdr` FOREIGN KEY (`rkhhdrid`) REFERENCES `rkhhdr` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=940 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `rkmhdr` */

DROP TABLE IF EXISTS `rkmhdr`;

CREATE TABLE `rkmhdr` (
  `rkmno` varchar(11) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `rkmdate` date NOT NULL DEFAULT '0000-00-00',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date NOT NULL DEFAULT '0000-00-00',
  `activitycode` varchar(12) NOT NULL DEFAULT '',
  `isclosing` int(11) DEFAULT NULL,
  `inputby` varchar(50) NOT NULL DEFAULT '',
  `createdat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updateby` varchar(50) NOT NULL DEFAULT '',
  `updatedat` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`rkmno`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `rkmlst` */

DROP TABLE IF EXISTS `rkmlst`;

CREATE TABLE `rkmlst` (
  `rkmno` varchar(11) NOT NULL DEFAULT '',
  `companycode` char(4) NOT NULL DEFAULT '',
  `blok` char(2) NOT NULL DEFAULT '',
  `plot` char(10) NOT NULL DEFAULT '',
  `totalluasactual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalestimasi` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalhasil` decimal(10,2) NOT NULL DEFAULT 0.00,
  `totalsisa` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`userid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `subkontraktor` */

DROP TABLE IF EXISTS `subkontraktor`;

CREATE TABLE `subkontraktor` (
  `id` varchar(10) NOT NULL,
  `companycode` char(4) NOT NULL,
  `kontraktorid` varchar(10) NOT NULL,
  `namasubkontraktor` varchar(100) NOT NULL,
  `isactive` tinyint(4) DEFAULT 1,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`companycode`),
  KEY `idx_subkontraktor_company` (`companycode`),
  KEY `idx_subkontraktor_kontraktor` (`kontraktorid`,`companycode`),
  KEY `idx_subkontraktor_active` (`isactive`),
  CONSTRAINT `fk_subkontraktor_company_companycode` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE,
  CONSTRAINT `fk_subkontraktor_kontraktor` FOREIGN KEY (`kontraktorid`, `companycode`) REFERENCES `kontraktor` (`id`, `companycode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `supporttickets` */

DROP TABLE IF EXISTS `supporttickets`;

CREATE TABLE `supporttickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(30) NOT NULL,
  `category` enum('forgot_password','bug_report','support','other') NOT NULL DEFAULT 'support',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `inprogress_by` varchar(50) DEFAULT NULL,
  `inprogress_at` timestamp NULL DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `companycode` char(4) NOT NULL,
  `description` text DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolved_by` varchar(50) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT current_timestamp(),
  `updatedat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`ticket_id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `idx_status` (`status`),
  KEY `idx_category` (`category`),
  KEY `idx_username` (`username`),
  KEY `fk_tickets_company` (`companycode`),
  CONSTRAINT `fk_tickets_company` FOREIGN KEY (`companycode`) REFERENCES `company` (`companycode`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `suratjalanpos` */

DROP TABLE IF EXISTS `suratjalanpos`;

CREATE TABLE `suratjalanpos` (
  `companycode` char(4) NOT NULL,
  `suratjalanno` varchar(30) NOT NULL,
  `mandorid` varchar(10) DEFAULT NULL,
  `plot` char(5) DEFAULT NULL,
  `varietas` varchar(10) DEFAULT NULL,
  `kategori` varchar(10) DEFAULT NULL,
  `umur` int(11) DEFAULT NULL,
  `kodetebang` varchar(15) DEFAULT NULL,
  `langsir` int(11) DEFAULT NULL,
  `tebusulit` int(11) DEFAULT NULL,
  `kendaraankontraktor` int(11) DEFAULT NULL,
  `muatgl` int(11) DEFAULT NULL,
  `nomorkendaraan` varchar(11) DEFAULT NULL,
  `nomorpolisi` varchar(11) DEFAULT NULL,
  `namasupir` varchar(25) DEFAULT NULL,
  `namakontraktor` varchar(25) DEFAULT NULL,
  `namasubkontraktor` varchar(25) DEFAULT NULL,
  `tanggaltebang` datetime DEFAULT NULL,
  `tanggalangkut` datetime DEFAULT NULL,
  `tanggalcetakpossecurity` datetime DEFAULT NULL,
  `flagprocessed` int(2) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`suratjalanno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `tenagakerja` */

DROP TABLE IF EXISTS `tenagakerja`;

CREATE TABLE `tenagakerja` (
  `tenagakerjaid` varchar(11) NOT NULL,
  `mandoruserid` varchar(15) NOT NULL,
  `companycode` char(4) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `jenistenagakerja` tinyint(1) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL,
  `isactive` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`tenagakerjaid`,`companycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `testgpshdr` */

DROP TABLE IF EXISTS `testgpshdr`;

CREATE TABLE `testgpshdr` (
  `companycode` varchar(5) DEFAULT NULL,
  `plot` varchar(7) DEFAULT NULL,
  `centerlatitude` decimal(20,15) DEFAULT NULL,
  `centerlongitude` decimal(20,15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `testgpslst` */

DROP TABLE IF EXISTS `testgpslst`;

CREATE TABLE `testgpslst` (
  `companycode` varchar(5) DEFAULT NULL,
  `plot` varchar(7) DEFAULT NULL,
  `latitude` decimal(20,15) DEFAULT NULL,
  `longitude` decimal(20,15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `timbanganpayload` */

DROP TABLE IF EXISTS `timbanganpayload`;

CREATE TABLE `timbanganpayload` (
  `payload` text DEFAULT NULL,
  `createddate` datetime DEFAULT NULL,
  `nom` char(8) DEFAULT NULL,
  `companycode` varchar(6) NOT NULL DEFAULT '',
  `suratjalanno` varchar(30) NOT NULL DEFAULT '',
  `tgl1` date DEFAULT NULL,
  `jam1` char(10) DEFAULT NULL,
  `tgl2` date DEFAULT NULL,
  `jam2` char(10) DEFAULT NULL,
  `nopol` varchar(11) DEFAULT NULL,
  `jnsk` varchar(4) DEFAULT NULL,
  `supl` varchar(15) DEFAULT NULL,
  `gsupl` varchar(2) DEFAULT NULL,
  `area` varchar(2) DEFAULT NULL,
  `item` varchar(3) DEFAULT NULL,
  `note` varchar(15) DEFAULT NULL,
  `ket1` varchar(30) DEFAULT NULL,
  `ket2` varchar(30) DEFAULT NULL,
  `ket3` varchar(30) DEFAULT NULL,
  `donom` varchar(15) DEFAULT NULL,
  `dotgl` date DEFAULT NULL,
  `bruto` decimal(7,0) DEFAULT NULL,
  `brkend` decimal(7,0) DEFAULT NULL,
  `raf` decimal(4,2) DEFAULT NULL,
  `traf` decimal(7,0) DEFAULT NULL,
  `netto` decimal(7,0) DEFAULT NULL,
  `flag` decimal(2,0) DEFAULT NULL,
  `usr1` varchar(9) DEFAULT NULL,
  `usr2` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`suratjalanno`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `trash` */

DROP TABLE IF EXISTS `trash`;

CREATE TABLE `trash` (
  `suratjalanno` varchar(30) DEFAULT NULL,
  `companycode` varchar(6) DEFAULT NULL,
  `jenis` varchar(10) DEFAULT NULL,
  `pucuk` decimal(10,3) DEFAULT NULL,
  `daungulma` decimal(10,3) DEFAULT NULL,
  `sogolan` decimal(10,3) DEFAULT NULL,
  `siwilan` decimal(10,3) DEFAULT NULL,
  `tebumati` decimal(10,3) DEFAULT NULL,
  `tanahetc` decimal(10,3) DEFAULT NULL,
  `total` decimal(10,3) DEFAULT NULL,
  `toleransi` int(2) DEFAULT NULL,
  `nettotrash` decimal(10,3) DEFAULT NULL,
  `createdby` varchar(20) DEFAULT NULL,
  `createddate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `upah` */

DROP TABLE IF EXISTS `upah`;

CREATE TABLE `upah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `activitygroup` varchar(50) NOT NULL,
  `wagetype` enum('DAILY','HOURLY','OVERTIME','WEEKEND_SATURDAY','WEEKEND_SUNDAY','PER_HECTARE','PER_KG') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `effectivedate` date NOT NULL,
  `enddate` date DEFAULT NULL,
  `parameter` varchar(50) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lookup` (`companycode`,`activitygroup`,`wagetype`,`effectivedate`),
  KEY `idx_company_date` (`companycode`,`effectivedate`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `upahborongan` */

DROP TABLE IF EXISTS `upahborongan`;

CREATE TABLE `upahborongan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companycode` varchar(4) NOT NULL,
  `activitycode` varchar(10) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `effectivedate` date NOT NULL,
  `enddate` date DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT current_timestamp(),
  `updatedat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_active_wage` (`companycode`,`activitycode`,`effectivedate`),
  KEY `idx_company_activity` (`companycode`,`activitycode`),
  KEY `idx_effective_date` (`effectivedate`,`enddate`),
  KEY `fk_upahborongan_activity` (`activitycode`),
  CONSTRAINT `fk_upahborongan_activity` FOREIGN KEY (`activitycode`) REFERENCES `activity` (`activitycode`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `usematerialapproval` */

DROP TABLE IF EXISTS `usematerialapproval`;

CREATE TABLE `usematerialapproval` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `companycode` char(4) NOT NULL,
  `approvalno` varchar(30) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `plot` varchar(10) NOT NULL,
  `itemseq` smallint(6) NOT NULL,
  `itemcode` varchar(30) NOT NULL,
  `itemname` varchar(50) DEFAULT NULL,
  `dosageperha` decimal(10,3) DEFAULT NULL,
  `qty` decimal(10,3) DEFAULT NULL,
  `unit` varchar(3) DEFAULT NULL,
  `flagstatus` varchar(20) NOT NULL,
  `costcenter` varchar(30) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approvedby` varchar(50) DEFAULT NULL,
  `approvedat` datetime DEFAULT NULL,
  `createdat` datetime NOT NULL,
  `type` varchar(15) DEFAULT NULL,
  `errorcode` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_approvalno` (`companycode`,`approvalno`),
  KEY `idx_rkhno` (`companycode`,`rkhno`),
  KEY `idx_item` (`companycode`,`itemcode`)
) ENGINE=InnoDB AUTO_INCREMENT=353 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `usematerialhdr` */

DROP TABLE IF EXISTS `usematerialhdr`;

CREATE TABLE `usematerialhdr` (
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `totalluas` decimal(10,2) DEFAULT NULL,
  `flagstatus` varchar(20) DEFAULT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` datetime DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `updatedat` datetime DEFAULT NULL,
  `errorcode` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`rkhno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `usemateriallst` */

DROP TABLE IF EXISTS `usemateriallst`;

CREATE TABLE `usemateriallst` (
  `companycode` char(4) NOT NULL,
  `rkhno` varchar(11) NOT NULL,
  `lkhno` varchar(15) NOT NULL,
  `plot` varchar(10) NOT NULL,
  `itemcode` varchar(30) NOT NULL,
  `qty` decimal(10,3) DEFAULT NULL,
  `qtydigunakan` decimal(10,3) DEFAULT NULL,
  `qtyretur` decimal(10,3) DEFAULT NULL,
  `unit` varchar(3) DEFAULT NULL,
  `nouse` char(15) DEFAULT NULL,
  `noretur` char(15) DEFAULT NULL,
  `itemname` varchar(50) DEFAULT NULL,
  `dosageperha` decimal(10,3) DEFAULT NULL,
  `returby` varchar(50) DEFAULT NULL,
  `tglretur` datetime DEFAULT NULL,
  `tglterimaretur` datetime DEFAULT NULL,
  `terimareturby` varchar(50) DEFAULT NULL,
  `mobiledate` datetime DEFAULT NULL,
  `itemprice` decimal(20,10) DEFAULT NULL,
  `costcenter` varchar(30) DEFAULT NULL,
  `startstock` decimal(20,10) DEFAULT NULL,
  `endstock` decimal(20,10) DEFAULT NULL,
  `tgluse` datetime DEFAULT NULL,
  `itemseq` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`companycode`,`lkhno`,`plot`,`itemcode`),
  KEY `fk_usemateriallst_herbisida` (`companycode`,`itemcode`),
  KEY `fk_usemateriallst_lkhhdr` (`lkhno`,`companycode`),
  CONSTRAINT `fk_usemateriallst_herbisida` FOREIGN KEY (`companycode`, `itemcode`) REFERENCES `herbisida` (`companycode`, `itemcode`),
  CONSTRAINT `fk_usemateriallst_lkhhdr` FOREIGN KEY (`lkhno`, `companycode`) REFERENCES `lkhhdr` (`lkhno`, `companycode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `userid` varchar(50) NOT NULL,
  `companycode` char(4) NOT NULL,
  `name` varchar(30) NOT NULL,
  `idjabatan` tinyint(3) DEFAULT NULL,
  `password` varchar(70) NOT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `createdat` date DEFAULT NULL,
  `updatedat` date DEFAULT NULL,
  `divisionid` tinyint(4) DEFAULT NULL,
  `isactive` tinyint(4) DEFAULT NULL,
  `mpassword` varchar(70) DEFAULT 'ac52c5fad6dddae348a37acaf7ba8bf1',
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `useractivity` */

DROP TABLE IF EXISTS `useractivity`;

CREATE TABLE `useractivity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL,
  `companycode` varchar(4) NOT NULL,
  `activitygroup` varchar(10) NOT NULL,
  `isactive` tinyint(1) DEFAULT 1,
  `grantedby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid_company_activity` (`userid`,`companycode`,`activitygroup`),
  KEY `idx_userid` (`userid`),
  KEY `idx_companycode` (`companycode`),
  KEY `idx_isactive` (`isactive`)
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `usercompany` */

DROP TABLE IF EXISTS `usercompany`;

CREATE TABLE `usercompany` (
  `userid` varchar(50) NOT NULL DEFAULT '',
  `companycode` varchar(50) NOT NULL,
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `grantedby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`userid`,`companycode`),
  KEY `idx_usercompany_active` (`isactive`),
  KEY `idx_usercompany_granted` (`grantedby`),
  CONSTRAINT `fk_usercompany_user` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `userpermission` */

DROP TABLE IF EXISTS `userpermission`;

CREATE TABLE `userpermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(50) NOT NULL,
  `companycode` varchar(4) NOT NULL,
  `permissionid` int(11) NOT NULL,
  `permissiontype` enum('GRANT','DENY') NOT NULL DEFAULT 'GRANT',
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `reason` varchar(255) DEFAULT NULL COMMENT 'Why this override exists',
  `grantedby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueuserpermission` (`userid`,`companycode`,`permissionid`),
  KEY `idxuser` (`userid`),
  KEY `idxcompany` (`companycode`),
  KEY `idxpermission` (`permissionid`),
  KEY `idxtype` (`permissiontype`),
  KEY `idxactive` (`isactive`),
  CONSTRAINT `fkuserpermpermission` FOREIGN KEY (`permissionid`) REFERENCES `permission` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fkuserpermuser` FOREIGN KEY (`userid`) REFERENCES `user` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User-specific permission overrides';

/*Table structure for table `varietas` */

DROP TABLE IF EXISTS `varietas`;

CREATE TABLE `varietas` (
  `kodevarietas` varchar(10) NOT NULL,
  `description` varchar(100) NOT NULL,
  `inputby` varchar(50) DEFAULT NULL,
  `updateby` varchar(50) DEFAULT NULL,
  `createdat` timestamp NULL DEFAULT NULL,
  `updatedat` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`kodevarietas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* Procedure structure for procedure `sp_mobileCheckLKHStatus` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileCheckLKHStatus` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileCheckLKHStatus`(
    IN p_companycode CHAR(4),
    in p_lkhno varchar(15),
    in p_activitycode varchar(10)
)
BEGIN
    declare v_status varchar(20);
    
    set v_status = (select mobile_status from lkhhdr
    where lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
    and companycode = p_companycode COLLATE utf8mb4_unicode_ci
    and activitycode = p_activitycode COLLATE utf8mb4_unicode_ci);
    
    if v_status = 'COMPLETED' then
        set @status = '2';
        set @statusDesc = 'LKH sudah terupload, anda tidak dapat mengupload ulang LKH';
        SELECT @status, @statusDesc;
    else
        set @status = '1';
        set @statusDesc = 'LKH masih belum terupload';
        SELECT @status, @statusDesc;
    end if;
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileCheckLKHStatus_getRKH` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileCheckLKHStatus_getRKH` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileCheckLKHStatus_getRKH`(
    IN p_mandorid VARCHAR(10),
    IN p_rkhdate DATE,
    IN p_companycode CHAR(4)
)
BEGIN
    DECLARE v_count int; 
    DECLARE v_apprvstatus varchar(15);
    
    set v_count = (SELECT count(*) FROM `tebu`.rkhhdr r
    left join `tebu`.`lkhhdr` l
    on r.companycode = l.companycode and r.rkhno = l.rkhno
    WHERE r.mandorid = p_mandorid COLLATE utf8mb4_unicode_ci 
    AND r.rkhdate = p_rkhdate
    AND r.companycode = p_companycode COLLATE utf8mb4_unicode_ci
    and l.mobile_status = 'COMPLETED');
    
    set v_apprvstatus = (SELECT COALESCE(approvalstatus, '0') 
    FROM `tebu`.`rkhhdr` r
    WHERE r.mandorid = p_mandorid COLLATE utf8mb4_unicode_ci 
    AND r.rkhdate = p_rkhdate
    AND r.companycode = p_companycode COLLATE utf8mb4_unicode_ci LIMIT 1);
    
        
    IF v_count > 0 THEN
        SET @status = 0;
        SET @statusDesc = 'LKH sudah pernah diupload, anda tidak dapat mendownload ulang RKH';
        SELECT @status, @statusDesc;
    ELSE
    
	IF v_apprvstatus = '1' THEN
	    SET @status = 1;
	    SET @statusDesc = 'RKH dapat didownload';
	    SELECT @status AS status, @statusDesc AS statusDesc;
	ELSE
	    SET @status = 0;
	    SET @statusDesc = 'RKH belum diapprove';
	    SELECT @status AS status, @statusDesc AS statusDesc;
        END IF;
        
    END IF;
    
    
    
    -- if v_count > 0 then
--         set @status = 0;
--         set @statusDesc = 'LKH sudah pernah diupload, anda tidak dapat mendownload ulang RKH';
--         select @status, @statusDesc;
--     else
--         SET @status = 1;
--         set @statusDesc = 'RKH dapat didownload';
--         select @status, @statusDesc;
--     end if;
    
    
--     set v_count_totallkh = (select count(*) from lkhhdr
--     where companycode = p_companycode COLLATE utf8mb4_unicode_ci
--     and rkhno = p_rkhno COLLATE utf8mb4_unicode_ci);
--     
--     SET v_count_completed = (SELECT COUNT(*) FROM lkhhdr
--     WHERE companycode = p_companycode COLLATE utf8mb4_unicode_ci
--     AND rkhno = p_rkhno COLLATE utf8mb4_unicode_ci
--     and mobile_status = 'COMPLETED');
--     
--     select v_count_totallkh 'total_LKH', v_count_completed 'completed';
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileCheckTKAbsen` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileCheckTKAbsen` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileCheckTKAbsen`(
    IN p_mandorid VARCHAR(5),
    IN p_rkhdate DATE,
    IN p_companycode CHAR(4)
)
BEGIN
    declare v_count int; 
    
    set v_count = (SELECT count(*) FROM `tebu`.`tenagakerja` tk
    LEFT JOIN `tebu`.`absenhdr` ah ON tk.mandoruserid = ah.`mandorid` AND tk.companycode = ah.`companycode`
    LEFT JOIN `tebu`.`absenlst` al ON ah.absenno = al.absenno AND al.tenagakerjaid = tk.tenagakerjaid
    WHERE tk.`mandoruserid` = p_mandorid  COLLATE utf8mb4_unicode_ci
    AND tk.companycode = p_companycode  COLLATE utf8mb4_unicode_ci
    AND DATE(al.absenmasuk) = p_rkhdate);
    
    if v_count > 0 then
        SET @status = 1;
        SET @statusDesc = 'Anda sudah melakukan absen tenaga kerja';
        SELECT @status, @statusDesc;
    else
        SET @status = 0;
        SET @statusDesc = 'Anda masih belum absen tenaga kerja';
        SELECT @status, @statusDesc;
    end if;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetKontraktor` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetKontraktor` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetKontraktor`(IN `p_rkhno` VARCHAR(11), IN `p_companycode` CHAR(4))
BEGIN
    
	DECLARE v_count INT;
	
	SELECT COUNT(*) INTO v_count
        FROM `tebu`.`rkhlst`
        WHERE companycode = p_companycode COLLATE utf8mb4_unicode_ci
        AND rkhno = p_rkhno COLLATE utf8mb4_unicode_ci 
        and activitycode in('4.3.3','4.4.3','4.5.2');
    
    IF v_count <> 0 THEN
        select id, `namakontraktor` from `tebu`.`kontraktor`
        where companycode = p_companycode COLLATE utf8mb4_unicode_ci and isactive = '1';
        
     -- ELSE
--      SET @status = '0';
--         SET @statusDesc = 'tidak perlu kontraktor';
--         SELECT @status, @statusDesc;
        END IF;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetRKH` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetRKH` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetRKH`(IN `p_mandorid` VARCHAR(5), IN `p_rkhdate` DATE, IN `p_companycode` char(4))
BEGIN
	
	-- SELECT rh.*, rl.* FROM `tebu`.`rkhhdr` rh
-- 	left join `tebu`.`rkhlst` rl
-- 	on rh.companycode = rl.companycode and rh.rkhno = rl.rkhno
-- 	WHERE rh.mandorid = p_mandorid COLLATE utf8mb4_unicode_ci 
-- 	and rh.rkhdate = p_rkhdate
-- 	and rh.companycode = p_companycode COLLATE utf8mb4_unicode_ci;
	
    SELECT rh.rkhno, a.blok, a.plot, us.userid 'mandorId', e.activitycode, e.herbisidagroupid, e.herbisidagroupname, 
    c.itemname, d.itemcode, a.luasarea, d.dosageperha, d.dosageunit, u.qty
    FROM `tebu`.rkhhdr as rh
    join `tebu`.rkhlst AS a on rh.companycode = a.companycode and rh.rkhno = a.rkhno
    JOIN `tebu`.usematerialhdr AS b ON b.rkhno = a.rkhno
    JOIN `tebu`.herbisidagroup AS e ON e.herbisidagroupid = a.herbisidagroupid
    JOIN `tebu`.herbisidadosage AS d ON d.companycode = a.companycode AND d.herbisidagroupid = a.herbisidagroupid
    JOIN `tebu`.usemateriallst AS u ON u.rkhno = b.rkhno AND u.itemcode = d.itemcode AND u.companycode = b.companycode
    JOIN `tebu`.herbisida AS c ON c.companycode = a.companycode AND c.itemcode = d.itemcode
--     JOIN `tebu`.lkhhdr AS l ON u.lkhno = l.lkhno
    JOIN `tebu`.USER AS us ON us.userid =  rh.mandorid
    WHERE rh.mandorid = p_mandorid COLLATE utf8mb4_unicode_ci 
    and rh.rkhdate = p_rkhdate
    AND rh.companycode = p_companycode COLLATE utf8mb4_unicode_ci 
    AND a.usingmaterial = 1
    ORDER BY a.blok, a.plot, d.itemcode;
	
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetRKHHdr` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetRKHHdr` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetRKHHdr`(IN `p_mandorid` VARCHAR(5), IN `p_rkhdate` DATE, IN `p_companycode` CHAR(4))
BEGIN
    SELECT rkhno FROM `tebu`.rkhhdr
    WHERE mandorid = p_mandorid COLLATE utf8mb4_unicode_ci 
    AND rkhdate = p_rkhdate
    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
    AND STATUS LIKE 'In Progress' ORDER BY `createdat` DESC;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetRKHHerbisida` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetRKHHerbisida` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetRKHHerbisida`(IN `p_rkhno` VARCHAR(11), IN `p_companycode` char(4))
BEGIN
-- LAMA
--     SELECT DISTINCT rl.activitycode, uml.plot, uml.itemcode, uml.itemname, uml.qty, uml.unit, uml.dosageperha
--     FROM `tebu`.`rkhlst` rl
--     LEFT JOIN `tebu`.`usemateriallst` uml 
--     ON rl.companycode = uml.companycode and rl.rkhno = uml.rkhno and rl.plot = uml.plot
--     WHERE rl.companycode = p_companycode COLLATE utf8mb4_unicode_ci
--     AND rl.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci
--     AND rl.usingmaterial = 1;
--  tambah status
SELECT DISTINCT rl.activitycode, uml.plot, uml.itemcode, uml.itemname, uml.qty, uml.unit, uml.dosageperha, umdr.flagstatus
    FROM `tebu`.`rkhlst` rl
    LEFT JOIN `tebu`.`usemateriallst` uml
    ON rl.companycode = uml.companycode AND rl.rkhno = uml.rkhno AND rl.plot = uml.plot and rl.companycode = uml.companycode
    LEFT JOIN  `tebu`.`usematerialhdr` umdr ON rl.rkhno = umdr.rkhno AND rl.companycode = umdr.companycode
    WHERE rl.companycode = p_companycode COLLATE utf8mb4_unicode_ci
    AND rl.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci
    AND rl.usingmaterial = 1;
    
    -- select distinct rl.activitycode, hg.herbisidagroupid, h.itemcode, h.itemname, hd.dosageperha, h.measure
--     from `tebu`.`rkhlst` rl
--     left join `tebu`.`herbisidagroup` hg on rl.herbisidagroupid = hg.herbisidagroupid
--     left join `tebu`.`herbisidadosage` hd on hg.herbisidagroupid = hd.herbisidagroupid
--     left join `tebu`.`herbisida` h on hd.itemcode = h.itemcode
--     where rl.companycode = p_companycode COLLATE utf8mb4_unicode_ci
--     and rl.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci
--     AND rl.usingmaterial = 1;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetRKHLst` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetRKHLst` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetRKHLst`(IN `p_rkhno` VARCHAR(11), IN `p_companycode` char(4))
BEGIN
/*
    select rl.*, a.activityname, ll.lkhno from `tebu`.`rkhlst` rl
    left join `tebu`.`activity` a on rl.activitycode = a.activitycode
    left join `tebu`.`lkhhdr` ll ON rl.rkhno = ll.rkhno AND rl.activitycode = ll.activitycode
    where rl.companycode = p_companycode COLLATE utf8mb4_unicode_ci
    and rl.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci;
    */
    
    SELECT 
     rl.companycode
    ,rl.rkhno
    ,rl.rkhdate
    ,rl.blok
    ,rl.plot
    ,rl.activitycode
    ,rl.luasarea
    ,rl.jenistenagakerja
    ,rl.usingmaterial
    ,rl.herbisidagroupid
    -- ,rl.usingvehicle
--     ,rl.operatorid
--     ,rl.usinghelper
--     ,rl.helperid
    ,a.activityname
    ,ll.lkhno
    ,bt. lifecyclestatus
    ,bt.batchdate
    ,bt.kodevarietas
    , '' as kontraktorid
    FROM `tebu`.`rkhlst` rl
    LEFT JOIN `tebu`.`activity` a ON rl.activitycode = a.activitycode
    LEFT JOIN `tebu`.`lkhhdr` ll ON rl.rkhno = ll.rkhno AND rl.activitycode = ll.activitycode and rl.companycode = ll.companycode
    LEFT JOIN `tebu`.`batch` bt ON rl.plot = bt.plot AND rl.companycode = bt.companycode AND bt.isactive = 1 and bt.companycode = rl.companycode
    where rl.companycode = p_companycode COLLATE utf8mb4_unicode_ci
    and rl.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci;
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetRKHVehicle` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetRKHVehicle` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetRKHVehicle`(IN `p_rkhno` VARCHAR(11), IN `p_companycode` char(4))
BEGIN
    -- select distinct k.idtenagakerja, k.nokendaraan, k.hourmeter, k.jenis
--     from `tebu`.`rkhlst` rl
--     left join `tebu`.`kendaraan` k on rl.operatorid = k.idtenagakerja
--     where rl.companycode = p_companycode COLLATE utf8mb4_unicode_ci
--     and rl.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci
--     AND rl.usingvehicle = 1;
SELECT
r.companycode
,r.rkhno
,r.activitycode
,r.nokendaraan
,r.operatorid
,r.usinghelper
,r.helperid
,r.urutan
,ll.lkhno
,kn.jenis
 FROM rkhlstkendaraan r LEFT JOIN `tebu`.`lkhhdr` ll ON r.rkhno = ll.rkhno AND r.activitycode = ll.activitycode AND r.companycode = ll.companycode
 LEFT JOIN `tebu`.`kendaraan` kn ON r.nokendaraan = kn.nokendaraan and isactive = '1' and r.companycode = kn.companycode
    where r.companycode = p_companycode COLLATE utf8mb4_unicode_ci
    and r.rkhno = p_rkhno COLLATE utf8mb4_unicode_ci;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetSubKontraktor` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetSubKontraktor` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetSubKontraktor`(IN `p_rkhno` VARCHAR(11), IN `p_companycode` CHAR(4))
BEGIN
    
	DECLARE v_count INT;
	
	SELECT COUNT(*) INTO v_count
        FROM `tebu`.`rkhlst`
        WHERE companycode = p_companycode COLLATE utf8mb4_unicode_ci
        AND rkhno = p_rkhno COLLATE utf8mb4_unicode_ci 
        and activitycode in('4.3.3','4.4.3','4.5.2');
    
    IF v_count <> 0 THEN
        select id, kontraktorid, `namasubkontraktor` from `tebu`.`subkontraktor`
        where companycode = p_companycode COLLATE utf8mb4_unicode_ci and isactive = '1';
        
     -- ELSE
--      SET @status = '0';
--         SET @statusDesc = 'tidak perlu subkontraktor';
--         SELECT @status, @statusDesc;
        END IF;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetTenagaKerja` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetTenagaKerja` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetTenagaKerja`(IN `p_mandorUserId` VARCHAR(15),IN `p_companycode` char(4))
BEGIN
	SELECT * FROM `tebu`.`tenagakerja` 
	WHERE mandoruserid = p_mandorUserId COLLATE utf8mb4_unicode_ci 
	AND companycode = p_companycode COLLATE utf8mb4_unicode_ci LIMIT 0, 1000;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileGetTenagaKerja2` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileGetTenagaKerja2` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileGetTenagaKerja2`(IN `p_mandorUserId` VARCHAR(15),IN `p_companycode` char(4))
BEGIN
	SELECT tk.tenagakerjaid, tk.mandoruserid, tk.companycode, tk.nama, tk.nik, tk.gender, tk.jenistenagakerja,
	CASE WHEN a.absenmasuk IS NULL THEN 0 ELSE 1 END 'isHadir', 
	a.status, a.absenno, jtk.idjenistenagakerja, jtk.nama
	FROM `tebu`.`tenagakerja` tk 
	LEFT JOIN (
		SELECT ah.absenno, ah.companycode, ah.mandorid, ah.status, al.tenagakerjaid, al.absenmasuk FROM `tebu`.`absenhdr` ah
		LEFT JOIN `tebu`.`absenlst` al ON ah.absenno = al.absenno
		WHERE DATE(al.absenmasuk) = CURDATE()
	) a ON tk.companycode = a.companycode AND tk.mandoruserid = a.mandorid AND tk.tenagakerjaid = a.tenagakerjaid
	JOIN `tebu`.`jenistenagakerja` jtk ON tk.jenistenagakerja = jtk.idjenistenagakerja
	WHERE tk.mandoruserid = p_mandorUserId COLLATE utf8mb4_unicode_ci 
	AND tk.isactive = '1'
	AND tk.companycode = p_companycode COLLATE utf8mb4_unicode_ci LIMIT 0, 1000;
	
	-- SELECT tk.tenagakerjaid, tk.mandoruserid, tk.companycode, tk.nama, tk.nik, tk.gender, tk.jenistenagakerja, 
-- 	case when a.absenmasuk is null then 0 else 1 end 'isHadir', 
-- 	a.status
-- 	FROM `tebu`.`tenagakerja` tk 
-- 	LEFT JOIN (
-- 		SELECT ah.companycode, ah.mandorid, ah.status, al.tenagakerjaid, al.absenmasuk FROM `tebu`.`absenhdr` ah
-- 		LEFT JOIN `tebu`.`absenlst` al ON ah.absenno = al.absenno
-- 		WHERE DATE(al.absenmasuk) = CURDATE()
-- 	) a on tk.companycode = a.companycode and tk.mandoruserid = a.mandorid and tk.tenagakerjaid = a.tenagakerjaid
-- 	WHERE tk.mandoruserid = p_mandorUserId COLLATE utf8mb4_unicode_ci 
-- 	AND tk.isactive = '1'
-- 	AND tk.companycode = p_companycode COLLATE utf8mb4_unicode_ci LIMIT 0, 1000;
	
	-- select ah.companycode, ah.mandorid, ah.status, al.tenagakerjaid, al.absenmasuk from `tebu`.`absenhdr` ah
-- 	left join `tebu`.`absenlst` al on ah.absenno = al.absenno
-- 	where date(al.absenmasuk) = curdate();
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileInsertLKH` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileInsertLKH` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileInsertLKH`()
BEGIN
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileInsertSuratJalanPos` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileInsertSuratJalanPos` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileInsertSuratJalanPos`(
     IN p_companycode CHAR(4),
    IN p_suratjalanno VARCHAR(30),
    IN p_plot CHAR(5),
    IN p_varietas VARCHAR(10),
    IN p_kategori VARCHAR(10),
    IN p_umur INT,
    IN p_kodetebang VARCHAR(15),
    IN p_langsir INT,
    IN p_tebusulit INT,  
    IN p_kendaraankontraktor INT, -- baru
    IN p_muatgl INT, -- baru
    IN p_nomorkendaraan VARCHAR(11),
    IN p_nomorpolisi VARCHAR(11),
    IN p_namasupir VARCHAR(25),
    IN p_namakontraktor VARCHAR(25),
    IN p_namasubkontraktor VARCHAR(25),
    IN p_tanggaltebang DATE,
    IN p_tanggalangkut DATETIME,
    IN p_mandorid VARCHAR(10) -- baru
    -- tanggal scan pos
    -- akun pos yang upload
    )
BEGIN
    
    DECLARE v_count INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Jika ada error saat insert, return 0
        SET @status = '0';
        SET @statusDesc = 'Surat Jalan Gagal Dikirim';
        SELECT @status AS status, @statusDesc AS statusDesc;
    END;
    
    -- Cek apakah kombinasi companycode dan suratjalanno sudah ada
    SELECT COUNT(*) INTO v_count
    FROM suratjalanpos
    WHERE companycode = p_companycode COLLATE utf8mb4_unicode_ci
    AND suratjalanno = p_suratjalanno COLLATE utf8mb4_unicode_ci;
    
    IF v_count = 0 THEN
        INSERT INTO suratjalanpos (
	    mandorid, -- baru
            companycode,
            suratjalanno,
            plot,
            varietas,
            kategori,
            umur,
            kodetebang,
            langsir,
            tebusulit,
            kendaraankontraktor, -- baru
            muatgl, -- baru
            nomorkendaraan,
            nomorpolisi,
            namasupir,
            namakontraktor,
            namasubkontraktor,
            tanggaltebang,
            tanggalangkut,
            tanggalcetakpossecurity,
            flagprocessed
        ) VALUES (
	    p_mandorid, -- baru
            p_companycode,
            p_suratjalanno,
            p_plot,
            p_varietas,
            p_kategori,
            p_umur,
            p_kodetebang,
            p_langsir,
            p_tebusulit,
            p_kendaraankontraktor, -- baru
            p_muatgl, -- baru
            p_nomorkendaraan,
            p_nomorpolisi,
            p_namasupir,
            p_namakontraktor,
            p_namasubkontraktor,
            p_tanggaltebang,
            p_tanggalangkut,
            NOW(),
            0
        );
        
        -- UPDATE `batch` 
--         SET kontraktorid = p_namakontraktor
--         WHERE companycode = p_companycode COLLATE utf8mb4_unicode_ci
--         AND plot = p_plot COLLATE utf8mb4_unicode_ci
--         AND (kontraktorid IS NULL OR kontraktorid = '')
--         AND isactive = '1';
        
        SET @status = '1';
        SET @statusDesc = 'Surat Jalan Berhasil Dikirim';
        SELECT @status, @statusDesc;
    ELSE
        SET @status = '1';
        SET @statusDesc = 'Surat Jalan Sudah Pernah Dikirim';
        SELECT @status, @statusDesc;
    END IF;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileLogin` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileLogin` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileLogin`(IN `p_userid` VARCHAR(50), IN `p_password` varchar(70))
BEGIN
	
	if exists (select * from `tebu`.`user` where userid = p_userid COLLATE utf8mb4_unicode_ci) then
		
		if exists (SELECT * FROM `tebu`.`user` WHERE userid = p_userid COLLATE utf8mb4_unicode_ci
			AND mpassword = p_password COLLATE utf8mb4_unicode_ci) then
			
			SET @isActive = (select isactive from `tebu`.`user` where userid = p_userid COLLATE utf8mb4_unicode_ci);
			
			if @isActive <> 1 then 
				SET @status = 0;
				SET @statusDesc = 'User tidak aktif';
				SELECT @status 'Status', @statusDesc 'StatusDesc';
			else
				SET @status = 1;
				SELECT u.*, j.namaJabatan, @status 'Status' FROM `tebu`.`user` u 
				LEFT JOIN `tebu`.`jabatan` j ON u.idJabatan = j.idJabatan 
				WHERE u.userid = p_userid COLLATE utf8mb4_unicode_ci
				AND u.mpassword = p_password COLLATE utf8mb4_unicode_ci
				LIMIT 0, 1000;
			end if;
			
			
		else
			SET @status = 0;
			SET @statusDesc = 'Password salah';
			SELECT @status 'Status', @statusDesc 'StatusDesc';
		end if;
		
	else
		SET @status = 0;
		SET @statusDesc = 'User tidak ditemukan';
		SELECT @status 'Status', @statusDesc 'StatusDesc';
	end if;
	
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUpdateLKHStatusDone` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUpdateLKHStatusDone` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUpdateLKHStatusDone`(
    IN p_companycode CHAR(4),
    IN p_lkhnolist JSON
)
BEGIN
    DECLARE v_lkhno VARCHAR(15);
    DECLARE v_activitycode VARCHAR(11);
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_totallkh INT;
    DECLARE v_totallkhupdated INT;
    DECLARE v_count INT;
    DECLARE v_tmpstatus varchar(20);
    
    -- Deklarasi cursor
    DECLARE cur CURSOR FOR 
        SELECT  
            CONVERT(JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.lkhno')) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
            CONVERT(JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.activitycode')) USING utf8mb4) COLLATE utf8mb4_unicode_ci
        FROM JSON_TABLE(
            p_lkhnolist,
            '$[*]' COLUMNS (
                VALUE JSON PATH '$'
            )
        ) AS json_data;
        
    -- Deklarasi handler untuk exit loop saat tidak ada baris lagi
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Validasi Sintaksis JSON
    IF NOT JSON_VALID(p_lkhnolist) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Input JSON tidak valid secara sintaksis.';
    END IF;
    
    -- Validasi apakah array JSON tidak kosong
    IF JSON_LENGTH(p_lkhnolist) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Array JSON kosong.';
    END IF;
    
    SET v_totallkh = JSON_LENGTH(p_lkhnolist);
    SET v_totallkhupdated = 0;
    
    -- Membuka cursor
    OPEN cur;
    
    -- Loop untuk mengambil data per baris
    read_loop: LOOP
        FETCH cur INTO v_lkhno, v_activitycode;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Validasi Struktur dan Nilai
        IF v_lkhno IS NULL OR v_activitycode IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Setiap objek JSON harus memiliki kunci lkhno dan activitycode.';
        END IF;
        IF v_lkhno = '' OR v_activitycode = '' THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Nilai lkhno atau activitycode tidak boleh kosong.';
        END IF;
        
        SET v_count = (SELECT COUNT(*) FROM `tebu`.`lkhhdr`
            WHERE lkhno = v_lkhno COLLATE utf8mb4_unicode_ci
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
            AND activitycode = v_activitycode COLLATE utf8mb4_unicode_ci);
        
        IF v_count > 0 THEN
            set v_tmpstatus = (SELECT `mobile_status` FROM `tebu`.`lkhhdr`
            WHERE lkhno = v_lkhno COLLATE utf8mb4_unicode_ci
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
            AND activitycode = v_activitycode COLLATE utf8mb4_unicode_ci);
            
            IF v_tmpstatus <> 'COMPLETED' THEN
                UPDATE `tebu`.`lkhhdr`
                SET 
			`status` = 'DRAFT',
                    `mobile_status` = 'COMPLETED',
                    `mobileupdatedat` = NOW()
                WHERE lkhno = v_lkhno COLLATE utf8mb4_unicode_ci
                    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
                    AND activitycode = v_activitycode COLLATE utf8mb4_unicode_ci;
            END IF;      
            
            SET v_totallkhupdated = v_totallkhupdated + 1;
            
        END IF;
        
    END LOOP;
    
    -- Menutup cursor
    CLOSE cur;
    
    IF v_totallkh = v_totallkhupdated THEN
        SET @status = '1';
    ELSE
        SET @status = '0';
    END IF;
    
    SET @statusDesc = CONCAT(v_totallkhupdated, ' dari ', v_totallkh, ' lkh berhasil diupdate');
    SELECT @status, @statusDesc;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUpdateUseMatStatus` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUpdateUseMatStatus` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUpdateUseMatStatus`(
    in p_companycode char(4),
    in p_rkhno varchar(11)
)
BEGIN
    declare v_count int;
    declare v_flagstatus varchar(20);
    
    set v_count = (select count(*) from `tebu`.`usematerialhdr`
    where `companycode` = p_companycode COLLATE utf8mb4_unicode_ci
        and `rkhno` = p_rkhno COLLATE utf8mb4_unicode_ci);
    
    if v_count > 0 then
        set v_flagstatus = (select `flagstatus` from `tebu`.`usematerialhdr` where `companycode` = p_companycode COLLATE utf8mb4_unicode_ci
            AND `rkhno` = p_rkhno COLLATE utf8mb4_unicode_ci);
        
        if v_flagstatus = 'DISPATCHED' then
            UPDATE `tebu`.`usematerialhdr`
            SET 
                `flagstatus` = 'UPLOADED'
            WHERE `companycode` = p_companycode COLLATE utf8mb4_unicode_ci
                AND `rkhno` = p_rkhno COLLATE utf8mb4_unicode_ci;
            
            SET @status = '1';
            SET @statusDesc = 'Status material berhasil diubah';
            SELECT @status, @statusDesc;
        else
            SET @status = '1';
            SET @statusDesc = 'Status material sudah pernah diubah';
            SELECT @status, @statusDesc;
        end if;
            
        
    else
        SET @status = '0';
        SET @statusDesc = 'Material tidak ditemukan';
        SELECT @status, @statusDesc;
    end if;
    
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadAbsen` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadAbsen` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadAbsen`(
    in p_mandorid varchar(5),
    in p_companycode char(4),
    IN p_is_absen_datang BOOLEAN,
    IN p_json_data JSON
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_tenagakerjaid VARCHAR(11);
    DECLARE v_jamdatang DATETIME;
    DECLARE v_tanggal DATE;
    DECLARE v_tanggal_format VARCHAR(8);
    DECLARE v_absenno VARCHAR(20);
    DECLARE v_count INT;
    DECLARE v_max_id INT;
    DECLARE v_new_id INT;
    DECLARE v_total_processed INT DEFAULT 0;
    
    DECLARE cur CURSOR FOR
        SELECT 
            JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.tenagakerjaid')) AS tenagakerjaid,
            STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.jamdatang')), '%Y-%m-%d %H:%i:%s') AS jamdatang
        FROM JSON_TABLE(
            p_json_data,
            '$[*]' COLUMNS (
                value JSON PATH '$'
            )
        ) AS json_data;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    START TRANSACTION;
    set v_new_id = 0;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_tenagakerjaid, v_jamdatang;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET v_tanggal = DATE(v_jamdatang);
        SET v_tanggal_format = DATE_FORMAT(v_tanggal, '%Y%m%d');
        SET v_absenno = v_tanggal_format;
        
        -- Cek apakah record sudah ada berdasarkan tenagakerjaid dan tanggal
        SELECT COUNT(*) INTO v_count 
        FROM tebu.absenlst 
        WHERE tenagakerjaid = v_tenagakerjaid 
        AND DATE(COALESCE(absenmasuk, absenpulang)) = v_tanggal;
        
        IF v_count > 0 THEN
            -- UPDATE existing record
            IF p_is_absen_datang = TRUE THEN
                UPDATE tebu.absenlst 
                SET absenmasuk = v_jamdatang
                WHERE tenagakerjaid = v_tenagakerjaid 
                AND DATE(COALESCE(absenmasuk, absenpulang)) = v_tanggal;
            ELSE
                UPDATE tebu.absenlst 
                SET absenpulang = v_jamdatang
                WHERE tenagakerjaid = v_tenagakerjaid 
                AND DATE(COALESCE(absenmasuk, absenpulang)) = v_tanggal;
            END IF;
        ELSE
            -- INSERT new record
            SET v_new_id = v_new_id + 1;
            
            IF p_is_absen_datang = TRUE THEN
                INSERT INTO tebu.absenlst (
                    absenno, id, tenagakerjaid, absenmasuk, absenpulang, keterangan
                ) VALUES (
                    v_absenno, v_new_id, v_tenagakerjaid, v_jamdatang, NULL, NULL
                );
            ELSE
                INSERT INTO tebu.absenlst (
                    absenno, id, tenagakerjaid, absenmasuk, absenpulang, keterangan
                ) VALUES (
                    v_absenno, v_new_id, v_tenagakerjaid, NULL, v_jamdatang, NULL
                );
            END IF;
        END IF;
        
        SET v_total_processed = v_total_processed + 1;
        
    END LOOP;
    
    CLOSE cur;
    COMMIT;
    
    SET @status = 1;
    SET @statusDesc = 'Data Berhasil Diupload';
    SELECT @status, @statusDesc;
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadAbsenDatang` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadAbsenDatang` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadAbsenDatang`(
    IN p_mandorid VARCHAR(5),
    IN p_companycode CHAR(4),
    IN p_dateabsen datetime,
    IN p_json_data JSON
)
BEGIN
    -- ABSENHDR Variables
    DECLARE v_absenno VARCHAR(20);
    DECLARE v_dateabsen_format VARCHAR(8);
    DECLARE v_totalpekerja INT;
    DECLARE v_count INT;
    DECLARE v_status CHAR(1);
    DECLARE v_insert_count INT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Validasi JSON data
    IF p_json_data IS NULL OR JSON_VALID(p_json_data) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid JSON data provided';
    END IF;
    
    -- Validasi JSON array tidak kosong
    IF JSON_LENGTH(p_json_data) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'JSON array is empty';
    END IF;
    
    -- Format tanggal untuk absenno (YYYYMMDD)
    SET v_dateabsen_format = DATE_FORMAT(p_dateabsen, '%Y%m%d');
    
    -- Format absenno: ABN + mandorid + dateabsen (YYYYMMDD)
    SET v_absenno = CONCAT('ABN', p_mandorid, v_dateabsen_format);
    
    -- Hitung total pekerja dari array JSON
    SET v_totalpekerja = JSON_LENGTH(p_json_data);
    
    -- ========================================================
    -- PROSES ABSENHDR
    -- ========================================================
    
    -- Cek apakah record sudah ada berdasarkan absenno dan companycode
    SELECT COUNT(*) INTO v_count 
    FROM tebu.absenhdr 
    WHERE absenno COLLATE utf8mb4_unicode_ci = v_absenno COLLATE utf8mb4_unicode_ci 
      AND companycode COLLATE utf8mb4_unicode_ci = p_companycode COLLATE utf8mb4_unicode_ci;
    
    -- Cek status approval TERLEBIH DAHULU sebelum proses apapun
    SELECT COALESCE(STATUS, 'O') INTO v_status 
    FROM tebu.absenhdr 
    WHERE absenno COLLATE utf8mb4_unicode_ci = v_absenno COLLATE utf8mb4_unicode_ci 
      AND companycode COLLATE utf8mb4_unicode_ci = p_companycode COLLATE utf8mb4_unicode_ci
    LIMIT 1;
    
    -- Jika sudah diapprove, STOP sebelum operasi absenlst
    IF v_status = 'A' THEN
        ROLLBACK;
        SET @status = 0;
        SET @statusDesc = 'Absen sudah diapprove';
        SELECT @status, @statusDesc;
        -- Keluar dari procedure
    ELSE
        -- Insert atau Update ABSENHDR
        IF v_count > 0 THEN
            -- UPDATE existing record
            UPDATE tebu.absenhdr 
            SET 
                companycode = p_companycode,
                mandorid = p_mandorid,
                totalpekerja = v_totalpekerja,
                uploaddate = NOW(),
                updateby = p_mandorid
            WHERE absenno COLLATE utf8mb4_unicode_ci = v_absenno COLLATE utf8mb4_unicode_ci 
              AND companycode COLLATE utf8mb4_unicode_ci = p_companycode COLLATE utf8mb4_unicode_ci;
            
            SET @operation_hdr = 'UPDATE';
            
        ELSE
            -- INSERT new record
            INSERT INTO tebu.absenhdr (
                absenno,
                companycode,
                mandorid,
                totalpekerja,
                STATUS,
                uploaddate,
                approvaldate,
                rejectdate,
                updateby
            ) VALUES (
                v_absenno,
                p_companycode,
                p_mandorid,
                v_totalpekerja,
                'O',                    -- status = O (uploaded, belum approve)
                NOW(),                  -- uploaddate = current timestamp
                NULL,                   -- approvaldate = NULL (belum approve)
                NULL,                   -- rejectdate = NULL (belum reject)
                p_mandorid              -- updateby = mandorid
            );
            
            SET @operation_hdr = 'INSERT';
            
        END IF;
        
        -- ========================================================
        -- PROSES ABSENLST (HANYA JIKA STATUS BUKAN 'A')
        -- ========================================================
        
        -- DELETE existing data di absenlst berdasarkan absenno
        DELETE FROM tebu.absenlst 
        WHERE absenno COLLATE utf8mb4_unicode_ci = v_absenno COLLATE utf8mb4_unicode_ci;
        
        
        
        -- INSERT data baru ke absenlst menggunakan JSON_TABLE langsung
        INSERT INTO tebu.absenlst (
            absenno,
            id,
            tenagakerjaid,
            absenmasuk,
            absenpulang,
            keterangan
        )
        SELECT 
            v_absenno,
            ROW_NUMBER() OVER (ORDER BY CONVERT(JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.tenagakerjaid')) USING utf8mb4) COLLATE utf8mb4_unicode_ci),
            CONVERT(JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.tenagakerjaid')) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
            p_dateabsen,
            NULL,
            NULL
        FROM JSON_TABLE(
            p_json_data,
            '$[*]' COLUMNS (
                VALUE JSON PATH '$'
            )
        ) AS json_data
        WHERE JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.tenagakerjaid')) IS NOT NULL
          AND JSON_UNQUOTE(JSON_EXTRACT(json_data.value, '$.tenagakerjaid')) != '';
        
        -- Cek berapa banyak data yang berhasil diinsert
        SELECT ROW_COUNT() INTO v_insert_count;
        
        COMMIT;
        
        -- Return success result dengan info jumlah data yang diinsert
        SET @status = 1;
        SET @statusDesc = CONCAT('Data Berhasil Diupload - ', v_insert_count, ' pekerja diproses');
        SELECT @status, @statusDesc;
    END IF;
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadAbsen_tes` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadAbsen_tes` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadAbsen_tes`(
	IN p_is_absen_datang TINYINT(1),
	IN p_json_data JSON
)
BEGIN
    DECLARE p_affected_rows INT;
    DECLARE p_result VARCHAR(500);
    DECLARE v_sql LONGTEXT DEFAULT '';
    DECLARE v_columns VARCHAR(1000) DEFAULT '';
    DECLARE v_values LONGTEXT DEFAULT '';
    DECLARE v_record JSON;
    DECLARE v_counter INT DEFAULT 0;
    DECLARE v_total_records INT DEFAULT 0;
    DECLARE v_col_counter INT DEFAULT 0;
    DECLARE v_col_total INT DEFAULT 0;
    DECLARE v_col_name VARCHAR(100);
    DECLARE v_col_value TEXT;
    DECLARE v_keys JSON;
    
    declare p_table_name VARCHAR(64);
    -- 
--     DECLARE v_status INT;
--     DECLARE v_statusdesc VARCHAR(20);
    
--     SET @status = 0;
	
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        DECLARE v_error_msg VARCHAR(500);
        GET DIAGNOSTICS CONDITION 1 v_error_msg = MESSAGE_TEXT;
        SET @status = 0; 
        SET @statusDesc = CONCAT('ERROR: ', v_error_msg);
        SELECT @status, @statusDesc;
        ROLLBACK;
        
--         SET p_result = CONCAT('ERROR: ', v_error_msg);
--         SET p_affected_rows = 0;
    END;
    
    SET @status = 0;    
    
    SET p_table_name = '`tebu`.`absenlst`';
    select p_is_absen_datang;
    
    START TRANSACTION;
	
    -- Validate JSON data
    IF p_json_data IS NULL OR JSON_TYPE(p_json_data) != 'ARRAY' THEN
--         SET p_result = 'ERROR: Invalid JSON data - must be an array';
--         SET p_affected_rows = 0;
        set @statusDesc = 'ERROR: Invalid JSON data - must be an array';
        SELECT @status, @statusDesc;
        ROLLBACK;
    ELSE
        -- Get total records
        SET v_total_records = JSON_LENGTH(p_json_data);
        
        IF v_total_records = 0 THEN
--             SET p_result = 'ERROR: No data records found in JSON';
--             SET p_affected_rows = 0;
	    set @statusDesc = 'ERROR: No data records found in JSON';
	    SELECT @status, @statusDesc;
            ROLLBACK;
        ELSE
            -- Get column names from first record
            SET v_record = JSON_EXTRACT(p_json_data, '$[0]');
            SET v_keys = JSON_KEYS(v_record);
            SET v_col_total = JSON_LENGTH(v_keys);
            SELECT v_record, v_keys, v_col_total;
            
            -- Build column names
            SET v_col_counter = 0;
            WHILE v_col_counter < v_col_total DO
                SET v_col_name = JSON_UNQUOTE(JSON_EXTRACT(v_keys, CONCAT('$[', v_col_counter, ']')));
                
                IF v_col_counter > 0 THEN
                    SET v_columns = CONCAT(v_columns, ', ');
                END IF;
                
                SET v_columns = CONCAT(v_columns, '`', v_col_name, '`');
                SET v_col_counter = v_col_counter + 1;
            END WHILE;
            
            -- Build VALUES clause for all records
            SET v_counter = 0;
            WHILE v_counter < v_total_records DO
                SET v_record = JSON_EXTRACT(p_json_data, CONCAT('$[', v_counter, ']'));
                
                IF v_counter > 0 THEN
                    SET v_values = CONCAT(v_values, ', ');
                END IF;
                
                SET v_values = CONCAT(v_values, '(');
                
                -- Add values for each column
                SET v_col_counter = 0;
                WHILE v_col_counter < v_col_total DO
                    SET v_col_name = JSON_UNQUOTE(JSON_EXTRACT(v_keys, CONCAT('$[', v_col_counter, ']')));
                    SET v_col_value = JSON_UNQUOTE(JSON_EXTRACT(v_record, CONCAT('$.', v_col_name)));
                    
                    IF v_col_counter > 0 THEN
                        SET v_values = CONCAT(v_values, ', ');
                    END IF;
                    
                    -- Handle NULL values
                    IF v_col_value IS NULL OR v_col_value = 'null' THEN
                        SET v_values = CONCAT(v_values, 'NULL');
                    ELSE
                        SET v_values = CONCAT(v_values, QUOTE(v_col_value));
                    END IF;
                    
                    SET v_col_counter = v_col_counter + 1;
                END WHILE;
                
                SET v_values = CONCAT(v_values, ')');
                SET v_counter = v_counter + 1;
            END WHILE;
            
            -- Build and execute final query
            SET v_sql = CONCAT('INSERT INTO `tebu`.`absenlst` (', v_columns, ') VALUES ', v_values);
            
            select v_sql, v_columns, v_values
            -- SET @sql = v_sql;
--             PREPARE stmt FROM @sql;
--             EXECUTE stmt;
--             DEALLOCATE PREPARE stmt;
            
--             SET p_affected_rows = ROW_COUNT();
            COMMIT;
            
--             SET p_result = CONCAT('SUCCESS: ', p_affected_rows, ' rows inserted into ', p_table_name);
            
            SET @status = 1;
	    SET @statusDesc = 'Data Berhasil Diupload';
	    SELECT @status, @statusDesc;
        END IF;
    END IF;
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHDetailBSM` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHDetailBSM` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHDetailBSM`(
    IN p_companycode CHAR(4),
    IN p_lkhno VARCHAR(15),
    IN p_plot VARCHAR(10),
    IN p_bersih DECIMAL(10,2),
    IN p_segar DECIMAL(10,2),
    IN p_manis DECIMAL(10,2),
    IN p_average DECIMAL(10,2),
    IN p_grade VARCHAR(5),
    IN p_kodetebang VARCHAR(20),
    IN p_suratjalanno VARCHAR(50)
    )
BEGIN
-- Declare Variables
    DECLARE v_count INT;
    DECLARE v_batchno VARCHAR(50);
    DECLARE v_lkhhdrid VARCHAR(50);
    
    -- count lkh detail bsm ada atau tidak
    SET v_count  = (SELECT COUNT(*) FROM `tebu`.`lkhdetailbsm` 
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND suratjalanno = p_suratjalanno COLLATE utf8mb4_unicode_ci
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci);
    
    
    -- jika v_count = 0, maka lkh belum dibuat
    IF v_count > 0 THEN 
        -- update lkh BSM
        UPDATE 
            `tebu`.`lkhdetailbsm`
        SET
	    `kodetebang` = p_kodetebang,
            `nilaibersih` = p_bersih,
            `nilaisegar` = p_segar,
            `nilaimanis` = p_manis,
            `averagescore` = p_average,
            `grade` = p_grade,
            `updatedat` = NOW(),
            `updateby` = 'Mobile Update'
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
            AND suratjalanno = p_suratjalanno COLLATE utf8mb4_unicode_ci
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
        
        SET @status = '1';
        SET @statusDesc = 'LKH Detail BSM Berhasil Diperbarui';
        SELECT @status, @statusDesc;
        
    ELSE    
    
    SELECT batchno INTO v_batchno
    FROM `tebu`.`batch`
    WHERE plot = p_plot COLLATE utf8mb4_unicode_ci 
    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
    AND isactive = '1'
    LIMIT 1;
    
    SELECT id INTO v_lkhhdrid
    FROM `tebu`.`lkhhdr` 
    WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
    
    INSERT INTO `tebu`.`lkhdetailbsm` (
            `companycode`,
            `lkhno`,
            `lkhhdrid`,
            `suratjalanno`,
            `plot`,
            `kodetebang`,
            `batchno`,
            `nilaibersih`,
            `nilaisegar`,
            `nilaimanis`,
            `averagescore`,
            `grade`,
            `inputby`,
            `createdat`
        )
        VALUES (
            p_companycode,
            p_lkhno,
            v_lkhhdrid,
            p_suratjalanno,
            p_plot,
            p_kodetebang,
            v_batchno,
            p_bersih,
            p_segar,
            p_manis,
            p_average,
            p_grade,
            'Mobile Insert',
            NOW()
        );
        SET @status = '1';
        SET @statusDesc = 'LKH Detail BSM Berhasil Diinput';
        SELECT @status, @statusDesc;
    END IF;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHDetailBSM_lama` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHDetailBSM_lama` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHDetailBSM_lama`(
    IN p_companycode CHAR(4),
    IN p_lkhno VARCHAR(15),
    IN p_plot VARCHAR(10),
    IN p_bersih DECIMAL(10,2),
    IN p_segar DECIMAL(10,2),
    IN p_manis DECIMAL(10,2),
    IN p_average DECIMAL(10,2),
    IN p_grade VARCHAR(5))
BEGIN
-- Declare Variables
    DECLARE v_count INT;
    
    -- count lkh detail bsm ada atau tidak
    SET v_count  = (SELECT COUNT(*) FROM `tebu`.`lkhdetailbsm` 
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND plot = p_plot COLLATE utf8mb4_unicode_ci
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci);
    
    
    -- jika v_count = 0, maka lkh belum dibuat
    IF v_count > 0 THEN 
        -- update lkh BSM
        UPDATE 
            `tebu`.`lkhdetailbsm`
        SET
            `nilaibersih` = p_bersih,
            `nilaisegar` = p_segar,
            `nilaimanis` = p_manis,
            `averagescore` = p_average,
            `grade` = p_grade,
            `updatedat` = NOW(),
            `updateby` = 'mobile upload'
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
            AND plot = p_plot COLLATE utf8mb4_unicode_ci
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
        
        SET @status = '1';
        SET @statusDesc = 'LKH Detail BSM Berhasil Diinput';
        SELECT @status, @statusDesc;
    ELSE
        SET @status = '0';
        SET @statusDesc = 'LKH Detail BSM Tidak Ditemukan';
        SELECT @status, @statusDesc;
    END IF;
    END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHMaterial` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHMaterial` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHMaterial`(
    IN p_companycode CHAR(4),
    IN p_lkhno VARCHAR(15),
    IN p_plot VARCHAR(10),
    IN p_itemcode VARCHAR(30),
    IN p_qtyditerima DECIMAL(10,3),
    IN p_qtysisa DECIMAL(10,3),
    IN p_qtydigunakan DECIMAL(10,3)
)
BEGIN
    -- Declare Variables
    DECLARE v_count INT;
    DECLARE v_lkhhdrid VARCHAR(50);
    
     -- Error handler untuk rollback jika terjadi exception
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    -- Mulai transaksi
    START TRANSACTION;
    
    -- count lkh detail material ada atau tidak
    SET v_count  = (SELECT COUNT(*) FROM `tebu`.`lkhdetailmaterial` 
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND plot = p_plot COLLATE utf8mb4_unicode_ci
        AND itemcode = p_itemcode COLLATE utf8mb4_unicode_ci
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci);
        
    -- update qty use material list
    UPDATE `tebu`.`usemateriallst`
    SET
        `qtydigunakan` = p_qtydigunakan,
        `qtyretur` = p_qtysisa,
        `mobiledate` = NOW()
    WHERE companycode = p_companycode COLLATE utf8mb4_unicode_ci
        AND lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND plot = p_plot COLLATE utf8mb4_unicode_ci
        AND itemcode = p_itemcode COLLATE utf8mb4_unicode_ci;
    
    -- jika v_count = 0, maka lkh belum dibuat
    IF v_count > 0 THEN
        UPDATE
            `tebu`.`lkhdetailmaterial`
        SET
            `qtyditerima` = p_qtyditerima,
            `qtysisa` = p_qtysisa,
            `qtydigunakan` = p_qtydigunakan,
            `updatedat` = NOW()
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
            AND plot = p_plot COLLATE utf8mb4_unicode_ci
            AND itemcode = p_itemcode COLLATE utf8mb4_unicode_ci
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
    
        SET @status = '1';
        SET @statusDesc = 'LKH Detail Material Berhasil Diupdate';
        SELECT @status, @statusDesc;
    ELSE
    
    SELECT id INTO v_lkhhdrid
    FROM `tebu`.`lkhhdr` 
    WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
    
        INSERT INTO `tebu`.`lkhdetailmaterial` (
            `companycode`,
            `lkhno`,
            `lkhhdrid`,
            `plot`,
            `itemcode`,
            `qtyditerima`,
            `qtysisa`,
            `qtydigunakan`,
            `createdat`
        )
        VALUES (
            p_companycode,
            p_lkhno,
            v_lkhhdrid,
            p_plot,
            p_itemcode,
            p_qtyditerima,
            p_qtysisa,
            p_qtydigunakan,
            NOW()
        );
    
        SET @status = '1';
        SET @statusDesc = 'LKH Detail Material Berhasil Diinput';
        SELECT @status, @statusDesc;
    END IF;
    
    -- Komit transaksi jika semua operasi berhasil
    COMMIT;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHPlot` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHPlot` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHPlot`(
    in p_companycode char(4),
    in p_lkhno varchar(15),
    in p_plot varchar(10),
    in p_luashasil DECIMAL(10,2),
    IN p_luassisa DECIMAL(10,2),
--     IN p_subkontraktorid VARCHAR(10),
    IN p_fieldbalancerit DECIMAL(10,2),
    IN p_fieldbalanceton DECIMAL(10,2)
)
BEGIN
    -- Declare Variables
    declare v_count int;
    
    -- count lkh detail plot ada atau tidak
    set v_count  = (select count(*) from `tebu`.`lkhdetailplot` 
        where lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        and plot = p_plot COLLATE utf8mb4_unicode_ci
        and companycode = p_companycode COLLATE utf8mb4_unicode_ci);
    
    
    -- jika v_count = 0, maka lkh belum dibuat
    if v_count > 0 then 
        -- update lkh plot
        UPDATE 
            `tebu`.`lkhdetailplot`
        set
            `luashasil` = p_luashasil,
            `luassisa` = p_luassisa,
            `updatedat` = NOW(),
--             `subkontraktorid` = p_subkontraktorid,
            `fieldbalancerit` = p_fieldbalancerit,
            `fieldbalanceton` = p_fieldbalanceton
        where lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
            and plot = p_plot COLLATE utf8mb4_unicode_ci
            and companycode = p_companycode COLLATE utf8mb4_unicode_ci;
        
        set @status = '1';
        set @statusDesc = 'LKH Detail Plot Berhasil Diinput';
        SELECT @status, @statusDesc;
    else
        set @status = '0';
        set @statusDesc = 'LKH Detail Plot Tidak Ditemukan';
        SELECT @status, @statusDesc;
    end if;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHPlot2` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHPlot2` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHPlot2`(
    IN p_companycode CHAR(4),
    IN p_lkhno VARCHAR(15),
    IN p_blok VARCHAR(10),
    IN p_plot VARCHAR(10),
    IN p_luashasil DECIMAL(10,2),
    IN p_luassisa DECIMAL(10,2),
    IN p_fieldbalancerit DECIMAL(10,2),
    IN p_fieldbalanceton DECIMAL(10,2),
    IN p_keterangan TEXT
)
BEGIN
    -- Declare Variables
    DECLARE v_count INT;
    DECLARE v_luasrkh DECIMAL(10,2);
    
    -- count lkh detail plot ada atau tidak
    -- set v_count  = (select count(*) from `tebu`.`lkhdetailplot` 
--         where lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
--         and plot = p_plot COLLATE utf8mb4_unicode_ci
--         and companycode = p_companycode COLLATE utf8mb4_unicode_ci);
	SET v_count  = (SELECT COUNT(*) FROM `tebu`.`lkhdetailplot` 
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND blok = p_blok COLLATE utf8mb4_unicode_ci
        AND (
            (p_plot = '' AND plot IS NULL) OR 
            (p_plot != '' AND plot = p_plot COLLATE utf8mb4_unicode_ci)
        )
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci);
    
    
    -- jika v_count = 0, maka lkh belum dibuat
    IF v_count > 0 THEN 
        -- update lkh plot
        -- UPDATE 
--             `tebu`.`lkhdetailplot`
--         set
--             `luashasil` = p_luashasil,
--             `luassisa` = p_luassisa,
--             `updatedat` = NOW(),
--             `fieldbalancerit` = p_fieldbalancerit,
--             `fieldbalanceton` = p_fieldbalanceton
--         where lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
--             and plot = p_plot COLLATE utf8mb4_unicode_ci
--             and companycode = p_companycode COLLATE utf8mb4_unicode_ci;
	SET v_luasrkh = (SELECT luasrkh FROM `tebu`.`lkhdetailplot`
            WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
            AND blok = p_blok COLLATE utf8mb4_unicode_ci
            AND (
                (p_plot = '' AND plot IS NULL) OR 
                (p_plot != '' AND plot = p_plot COLLATE utf8mb4_unicode_ci)
            )
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
            LIMIT 1);
           
        IF v_luasrkh IS NULL THEN
        
		UPDATE 
		    `tebu`.`lkhdetailplot`
		SET
		    `luashasil` = NULL,
		    `luassisa` = NULL,
		    `updatedat` = NOW(),
		    `fieldbalancerit` = p_fieldbalancerit,
		    `fieldbalanceton` = p_fieldbalanceton,
		    `keterangan` = p_keterangan
		WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
		    AND blok = p_blok COLLATE utf8mb4_unicode_ci
		    AND (
			(p_plot = '' AND plot IS NULL) OR 
			(p_plot != '' AND plot = p_plot COLLATE utf8mb4_unicode_ci)
		    )
		    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
	ELSE
		UPDATE 
		    `tebu`.`lkhdetailplot`
		SET
		    `luashasil` = p_luashasil,
		    `luassisa` = p_luassisa,
		    `updatedat` = NOW(),
		    `fieldbalancerit` = p_fieldbalancerit,
		    `fieldbalanceton` = p_fieldbalanceton,
		    `keterangan` = p_keterangan
		WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
		    AND blok = p_blok COLLATE utf8mb4_unicode_ci
		    AND (
			(p_plot = '' AND plot IS NULL) OR 
			(p_plot != '' AND plot = p_plot COLLATE utf8mb4_unicode_ci)
		    )
		    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
	END IF;
        
        SET @status = '1';
        SET @statusDesc = 'LKH Detail Plot Berhasil Diinput';
        SELECT @status, @statusDesc;
    ELSE
        SET @status = '0';
        SET @statusDesc = 'LKH Detail Plot Tidak Ditemukan';
        SELECT @status, @statusDesc;
    END IF;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHVehicle` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHVehicle` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHVehicle`(
	IN p_companycode CHAR(4),
	IN p_lkhno VARCHAR(15),
	IN p_mandorid VARCHAR(15),
	IN p_plot VARCHAR(10),
	IN p_nokendaraan VARCHAR(15),
	IN p_operatorid VARCHAR(15),
	IN p_jammulai TIME,
	IN p_jamselesai TIME
    )
BEGIN
	DECLARE v_count INT;
	
	-- Error handler untuk rollback jika terjadi exception
	DECLARE EXIT HANDLER FOR SQLEXCEPTION
	BEGIN
		ROLLBACK;
		RESIGNAL;
	END;
	
	-- Mulai transaksi
	START TRANSACTION;
	
	-- SET v_count  = (SELECT COUNT(*) FROM `tebu`.`kendaraanbbm` 
--         WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
--         AND plot = p_plot COLLATE utf8mb4_unicode_ci
--         AND nokendaraan = p_nokendaraan COLLATE utf8mb4_unicode_ci
--         AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
--         AND mandorid = p_mandorid COLLATE utf8mb4_unicode_ci);
        SET v_count  = (SELECT COUNT(*) FROM `tebu`.`lkhdetailkendaraan` 
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND nokendaraan = p_nokendaraan COLLATE utf8mb4_unicode_ci
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci);
        
        -- jika v_count = 0, maka lkh belum dibuat
    IF v_count > 0 THEN
        -- UPDATE
--             `tebu`.`kendaraanbbm`
--         SET
--             `jammulai` = p_jammulai,
--             `jamselesai` = p_jamselesai,
--             `inputby` = p_mandorid,
--             `createdat` = NOW()
--         WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
--             AND plot = p_plot COLLATE utf8mb4_unicode_ci
--             AND nokendaraan = p_nokendaraan COLLATE utf8mb4_unicode_ci
--             AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
	UPDATE
            `tebu`.`lkhdetailkendaraan`
        SET
            `jammulai` = p_jammulai,
            `jamselesai` = p_jamselesai
            -- `inputby` = p_mandorid,
--             `createdat` = NOW()
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
            AND nokendaraan = p_nokendaraan COLLATE utf8mb4_unicode_ci
            AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
    
        SET @status = '1';
        SET @statusDesc = 'Kendaraan Berhasil Diupdate';
        SELECT @status, @statusDesc;
    ELSE
        -- INSERT INTO `tebu`.`kendaraanbbm` (
--             `companycode`,
--             `lkhno`,
--             `plot`,
--             `nokendaraan`,
--             `mandorid`,
--             `operatorid`,
--             `jammulai`,
--             `jamselesai`,
--             `inputby`,
--             `createdat`
--         )
--         VALUES (
--             p_companycode,
--             p_lkhno,
--             p_plot,
--             p_nokendaraan,
--             p_mandorid,
--             p_operatorid,
--             p_jammulai,
--             p_jamselesai,
--             p_mandorid,
--             NOW()
--         );
--     
--         SET @status = '1';
--         SET @statusDesc = 'Kendaraan Berhasil Diinput';
--         SELECT @status, @statusDesc;
	SET @status = '0';
        SET @statusDesc = 'LKH Kendaraan Tidak Ada';
        SELECT @status, @statusDesc;
    END IF;
    
    -- Komit transaksi jika semua operasi berhasil
    COMMIT;
    
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_mobileUploadLKHWorker` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_mobileUploadLKHWorker` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_mobileUploadLKHWorker`(
    IN p_companycode CHAR(4),
    IN p_lkhno VARCHAR(15),
    IN p_tenagakerjaid VARCHAR(11),
    IN p_tenagakerjaurutan INT,
    IN p_jammasuk TIME,
    IN p_jamselesai TIME,
    IN p_overtimehours DECIMAL(4,2)
)
BEGIN
    -- Declare Variables
    DECLARE v_count INT;
    DECLARE v_lkhhdrid VARCHAR(50);
    
    -- count lkh detail worker ada atau tidak
    SET v_count  = (SELECT COUNT(*) FROM `tebu`.`lkhdetailworker` 
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
        AND tenagakerjaid = p_tenagakerjaid COLLATE utf8mb4_unicode_ci);
        
    IF v_count = 0 THEN
    
    SELECT id INTO v_lkhhdrid
    FROM `tebu`.`lkhhdr` 
    WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
    AND companycode = p_companycode COLLATE utf8mb4_unicode_ci;
    
        INSERT INTO `tebu`.`lkhdetailworker` (
            `companycode`,
            `lkhno`,
            `lkhhdrid`,
            `tenagakerjaid`,
            `tenagakerjaurutan`,
            `jammasuk`,
            `jamselesai`,
            `overtimehours`,
            `createdat`
        )
        VALUES (
            p_companycode,
            p_lkhno,
            v_lkhhdrid,
            p_tenagakerjaid,
            p_tenagakerjaurutan,
            p_jammasuk,
            p_jamselesai,
            p_overtimehours,
            NOW()
        );
        SET @status = '1';
        SET @statusDesc = 'LKH Detail Worker Berhasil Diinput';
        SELECT @status, @statusDesc;
    ELSE
        UPDATE `tebu`.`lkhdetailworker`
        SET
            `tenagakerjaurutan` = p_tenagakerjaurutan,
            `jammasuk` = p_jammasuk,
            `jamselesai` = p_jamselesai,
            `overtimehours` = p_overtimehours,
            `updatedat` = NOW()
        WHERE lkhno = p_lkhno COLLATE utf8mb4_unicode_ci
        AND companycode = p_companycode COLLATE utf8mb4_unicode_ci
        AND tenagakerjaid = p_tenagakerjaid COLLATE utf8mb4_unicode_ci;
        
        SET @status = '1';
        SET @statusDesc = 'LKH Detail Worker Berhasil Diupdate';
        SELECT @status, @statusDesc;
    END IF;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_web_delete_transaksi_RKH` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_web_delete_transaksi_RKH` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`nvm`@`114.6.243.82` PROCEDURE `sp_web_delete_transaksi_RKH`(
    IN p_companycode VARCHAR(10),
    IN p_rkhno       VARCHAR(20),
    IN p_islive      TINYINT      -- 0 = preview, 1 = delete
)
BEGIN
    DECLARE v_lkh_pattern VARCHAR(50);
    -- Auto generate LKH pattern: RKHxxxx -> LKHxxxx-%
    SET v_lkh_pattern = CONCAT(REPLACE(p_rkhno, 'RKH', 'LKH'), '-%');
    
    -- ================================
    -- 1. PREVIEW BEFORE
    -- ================================
    SELECT 'BEFORE DELETE' AS tahap, p.*
    FROM (
        SELECT 
            'RKH HDR' AS tabel,
            (SELECT COUNT(*) FROM rkhhdr WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'RKH LST' AS tabel,
            (SELECT COUNT(*) FROM rkhlst WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'RKH LST WORKER' AS tabel,
            (SELECT COUNT(*) FROM rkhlstworker WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'RKH LST KENDARAAN' AS tabel,
            (SELECT COUNT(*) FROM rkhlstkendaraan WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH HDR' AS tabel,
            (SELECT COUNT(*) FROM lkhhdr WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL PLOT' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailplot ldp 
             INNER JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL WORKER' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailworker ldw 
             INNER JOIN lkhhdr lh ON ldw.lkhno = lh.lkhno AND ldw.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL KENDARAAN' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailkendaraan ldk 
             INNER JOIN lkhhdr lh ON ldk.lkhno = lh.lkhno AND ldk.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL MATERIAL' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailmaterial ldm 
             INNER JOIN lkhhdr lh ON ldm.lkhno = lh.lkhno AND ldm.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL BSM' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailbsm ldb 
             INNER JOIN lkhhdr lh ON ldb.lkhno = lh.lkhno AND ldb.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'USE MATERIAL HDR' AS tabel,
            (SELECT COUNT(*) FROM usematerialhdr WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'USE MATERIAL LST' AS tabel,
            (SELECT COUNT(*) FROM usemateriallst WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'SURAT JALAN POS' AS tabel,
            (SELECT COUNT(*) FROM suratjalanpos WHERE companycode = p_companycode AND suratjalanno LIKE CONCAT('SJ-%-', v_lkh_pattern)) AS jumlah_record
        UNION ALL
        SELECT 
            'TIMBANGAN PAYLOAD' AS tabel,
            (SELECT COUNT(*) FROM timbanganpayload WHERE companycode = p_companycode AND suratjalanno LIKE CONCAT('SJ-%-', v_lkh_pattern)) AS jumlah_record
    ) p;
    
    -- ================================
    -- 2. DELETE EXECUTION (HANYA SAAT p_islive = 1)
    -- ================================
    IF p_islive = 1 THEN
        START TRANSACTION;
        
        -- URUTAN PENGHAPUSAN BERDASARKAN DEPENDENCY FOREIGN KEY
        -- Hapus dari child table paling bawah ke parent table paling atas
        
        -- 1. Delete timbangan payload (child dari suratjalanpos)
        DELETE FROM timbanganpayload 
        WHERE companycode = p_companycode 
          AND suratjalanno LIKE CONCAT('SJ-%-', v_lkh_pattern);
        
        -- 2. Delete surat jalan pos
        DELETE FROM suratjalanpos 
        WHERE companycode = p_companycode 
          AND suratjalanno LIKE CONCAT('SJ-%-', v_lkh_pattern);
        
        -- 3. Delete usemateriallst (PENTING: Hapus berdasarkan lkhno yang terkait dengan rkhno)
        DELETE uml
        FROM usemateriallst uml
        INNER JOIN lkhhdr lh 
            ON uml.lkhno = lh.lkhno
           AND uml.companycode = lh.companycode
        WHERE lh.companycode = p_companycode
          AND lh.rkhno = p_rkhno;
        
        -- 4. Delete usematerialhdr
        DELETE FROM usematerialhdr 
        WHERE companycode = p_companycode 
          AND rkhno = p_rkhno;
        
        -- 5. Delete lkhdetailbsm (child dari lkhhdr)
        DELETE ldb FROM lkhdetailbsm ldb
        INNER JOIN lkhhdr lh ON ldb.lkhno = lh.lkhno AND ldb.companycode = lh.companycode
        WHERE lh.companycode = p_companycode 
          AND lh.rkhno = p_rkhno;
        
        -- 6. Delete lkhdetailmaterial (child dari lkhhdr)
        DELETE ldm FROM lkhdetailmaterial ldm
        INNER JOIN lkhhdr lh ON ldm.lkhno = lh.lkhno AND ldm.companycode = lh.companycode
        WHERE lh.companycode = p_companycode 
          AND lh.rkhno = p_rkhno;
        
        -- 7. Delete lkhdetailkendaraan (child dari lkhhdr)
        DELETE ldk FROM lkhdetailkendaraan ldk
        INNER JOIN lkhhdr lh ON ldk.lkhno = lh.lkhno AND ldk.companycode = lh.companycode
        WHERE lh.companycode = p_companycode 
          AND lh.rkhno = p_rkhno;
        
        -- 8. Delete lkhdetailworker (child dari lkhhdr)
        DELETE ldw FROM lkhdetailworker ldw
        INNER JOIN lkhhdr lh ON ldw.lkhno = lh.lkhno AND ldw.companycode = lh.companycode
        WHERE lh.companycode = p_companycode 
          AND lh.rkhno = p_rkhno;
        
        -- 9. Delete lkhdetailplot (child dari lkhhdr)
        DELETE ldp FROM lkhdetailplot ldp
        INNER JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
        WHERE lh.companycode = p_companycode 
          AND lh.rkhno = p_rkhno;
        
        -- 10. Delete lkhhdr (sekarang aman karena semua child sudah dihapus)
        DELETE FROM lkhhdr 
        WHERE companycode = p_companycode 
          AND rkhno = p_rkhno;
        
        -- 11. Delete rkhlstkendaraan (child dari rkhhdr)
        DELETE FROM rkhlstkendaraan 
        WHERE companycode = p_companycode 
          AND rkhno = p_rkhno;
        
        -- 12. Delete rkhlstworker (child dari rkhhdr)
        DELETE FROM rkhlstworker 
        WHERE companycode = p_companycode 
          AND rkhno = p_rkhno;
        
        -- 13. Delete rkhlst (child dari rkhhdr)
        DELETE FROM rkhlst 
        WHERE companycode = p_companycode 
          AND rkhno = p_rkhno;
        
        -- 14. Delete rkhhdr (parent paling atas, dihapus terakhir)
        DELETE FROM rkhhdr 
        WHERE companycode = p_companycode 
          AND rkhno = p_rkhno;
        
        COMMIT;
    END IF;
    
    -- ================================
    -- 3. PREVIEW AFTER
    -- ================================
    SELECT 'AFTER DELETE' AS tahap, p.*
    FROM (
        SELECT 
            'RKH HDR' AS tabel,
            (SELECT COUNT(*) FROM rkhhdr WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'RKH LST' AS tabel,
            (SELECT COUNT(*) FROM rkhlst WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'RKH LST WORKER' AS tabel,
            (SELECT COUNT(*) FROM rkhlstworker WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'RKH LST KENDARAAN' AS tabel,
            (SELECT COUNT(*) FROM rkhlstkendaraan WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH HDR' AS tabel,
            (SELECT COUNT(*) FROM lkhhdr WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL PLOT' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailplot ldp 
             INNER JOIN lkhhdr lh ON ldp.lkhno = lh.lkhno AND ldp.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL WORKER' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailworker ldw 
             INNER JOIN lkhhdr lh ON ldw.lkhno = lh.lkhno AND ldw.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL KENDARAAN' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailkendaraan ldk 
             INNER JOIN lkhhdr lh ON ldk.lkhno = lh.lkhno AND ldk.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL MATERIAL' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailmaterial ldm 
             INNER JOIN lkhhdr lh ON ldm.lkhno = lh.lkhno AND ldm.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'LKH DETAIL BSM' AS tabel,
            (SELECT COUNT(*) FROM lkhdetailbsm ldb 
             INNER JOIN lkhhdr lh ON ldb.lkhno = lh.lkhno AND ldb.companycode = lh.companycode
             WHERE lh.companycode = p_companycode AND lh.rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'USE MATERIAL HDR' AS tabel,
            (SELECT COUNT(*) FROM usematerialhdr WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'USE MATERIAL LST' AS tabel,
            (SELECT COUNT(*) FROM usemateriallst WHERE companycode = p_companycode AND rkhno = p_rkhno) AS jumlah_record
        UNION ALL
        SELECT 
            'SURAT JALAN POS' AS tabel,
            (SELECT COUNT(*) FROM suratjalanpos WHERE companycode = p_companycode AND suratjalanno LIKE CONCAT('SJ-%-', v_lkh_pattern)) AS jumlah_record
        UNION ALL
        SELECT 
            'TIMBANGAN PAYLOAD' AS tabel,
            (SELECT COUNT(*) FROM timbanganpayload WHERE companycode = p_companycode AND suratjalanno LIKE CONCAT('SJ-%-', v_lkh_pattern)) AS jumlah_record
    ) p;
    
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
