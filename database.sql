-- MariaDB database structure for nombres_db
-- Sole table: dictionary (unique words unaccented → accented)

CREATE DATABASE IF NOT EXISTS `nombres_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nombres_db`;

DROP TABLE IF EXISTS `dictionary`;
CREATE TABLE `dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word_no_accent` varchar(100) NOT NULL,
  `word_accented` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_word_no_accent` (`word_no_accent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
