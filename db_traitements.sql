-- @file db_traitements.sql
-- @brief Effectue les traitements sur la base de donnée utilisée pour stocker les requêtes effectuées

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- On créé la table si elle n'existe pas déjà
CREATE TABLE IF NOT EXISTS `websitesIPv6`(
  `domain` text COLLATE latin1_bin NOT NULL,
  `hasDomainIPv6` int COLLATE latin1_bin NOT NULL,
  `hasWWWIPv6` int COLLATE latin1_bin NOT NULL,
  `hasMXServersIPv6` int COLLATE latin1_bin NOT NULL,
  `hasNSServersIPv6` int COLLATE latin1_bin NOT NULL,
  `resultIPv6` int COLLATE latin1_bin NOT NULL,
  `dateLastManualCheck` datetime COLLATE latin1_bin NOT NULL,
  `dateLastAutomaticCheck` datetime COLLATE latin1_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
