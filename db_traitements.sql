-- @file db_traitements.sql
-- @brief Effectue les traitements sur la base de donnée utilisée pour stocker les requêtes effectuées

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- On créé les tables si elles n'existent pas déjà
CREATE TABLE IF NOT EXISTS `websitesIPv6`(
  `id` int COLLATE latin1_bin NOT NULL AUTO_INCREMENT,
  `domain` text COLLATE latin1_bin NOT NULL,
  `hasDomainIPv6` int COLLATE latin1_bin NOT NULL,
  `hasWWWIPv6` int COLLATE latin1_bin NOT NULL,
  `hasMXServersIPv6` int COLLATE latin1_bin NOT NULL,
  `hasNSServersIPv6` int COLLATE latin1_bin NOT NULL,
  `resultIPv6` int COLLATE latin1_bin NOT NULL,
  `dateLastManualCheck` datetime COLLATE latin1_bin NOT NULL,
  `dateLastAutomaticCheck` datetime COLLATE latin1_bin NOT NULL,
  CONSTRAINT primaryKey PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

CREATE TABLE IF NOT EXISTS `numberOfTests`(
  `id` int COLLATE latin1_bin NOT NULL AUTO_INCREMENT,
  `dateTests` date COLLATE latin1_bin NOT NULL,
  `numberTests` int COLLATE latin1_bin NOT NULL,
  CONSTRAINT primaryKey PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

CREATE TABLE IF NOT EXISTS `numberOfTestsPerDay`(
  `id` int COLLATE latin1_bin NOT NULL AUTO_INCREMENT,
  `dateTestsPerDay` date COLLATE latin1_bin NOT NULL,
  `numberTestsPerDay` int COLLATE latin1_bin NOT NULL,
  CONSTRAINT primaryKey PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

CREATE TABLE IF NOT EXISTS `calendar`(
  `datefield` date COLLATE latin1_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

DELETE FROM `numberOfTestsPerDay` WHERE 1;
DELETE FROM `calendar` WHERE 1;

-- On supprime les doublons éventuels (lorsqu'un utilisateur recharge la page sans attendre que le script soit terminé)
DELETE websitesIPv6
FROM websitesIPv6
LEFT OUTER JOIN (
  SELECT MAX(id) AS id, domain, dateLastManualCheck
  FROM websitesIPv6
  GROUP BY domain, dateLastManualCheck
) AS T1
ON websitesIPv6.id = T1.id
WHERE T1.id IS NULL;

-- On créé la procédure pour remplir la table de calendrier
DROP PROCEDURE IF EXISTS fill_calendar;

CREATE PROCEDURE fill_calendar(start_date date, end_date date)
BEGIN
DECLARE crt_date date;
SET crt_date = start_date;
WHILE crt_date < end_date do
INSERT INTO calendar VALUES(crt_date);
SET crt_date = ADDDATE(crt_date, INTERVAL 1 DAY);
END WHILE;
END;

CALL fill_calendar('2019-12-01', DATE(NOW()));

INSERT INTO numberOfTestsPerDay(dateTestsPerDay, numberTestsPerDay)
SELECT calendar.datefield AS dateTestsPerDay, IFNULL(numberTests,0) AS numberTestsPerDay
FROM numberOfTests
RIGHT JOIN calendar ON numberOfTests.dateTests = calendar.datefield
GROUP BY calendar.dateField;
