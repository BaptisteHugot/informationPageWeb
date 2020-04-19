<?php

/**
* @file basicFunctions.php
* @brief Fonctions utilisées pour récupérer l'ensemble des informations liées à une page Internet
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

/**
* Insertion dans la base de données lorsqu'un utilisateur entre un domaine
* @param $arrayResultsIPv6 Le tableau comprenant les informations sur les adresses IPv6
* @param $hostWithoutSubdomain Le nom de l'hôte sans le sous-domaine
*/
function manualCheckInsertion(array $arrayResultsIPv6, string $hostWithoutSubdomain){
	date_default_timezone_set('UTC');
	$dateManualEntry = date("Y-m-d H:i:s");

	global $connexion;

	$hostWithoutSubdomain = $connexion->real_escape_string($hostWithoutSubdomain);// Pour éviter une injection SQL
	$arrayResultsIPv6["hasDomainIPv6"] = intval($arrayResultsIPv6["hasDomainIPv6"]);
	$arrayResultsIPv6["hasWWWIPv6"] = intval($arrayResultsIPv6["hasWWWIPv6"]);
	$arrayResultsIPv6["hasMXServersIPv6"] = intval($arrayResultsIPv6["hasMXServersIPv6"]);
	$arrayResultsIPv6["hasNSServersIPv6"] = intval($arrayResultsIPv6["hasNSServersIPv6"]);
	$arrayResultsIPv6["resultIPv6"] = intval($arrayResultsIPv6["resultIPv6"]);

	$stmt = $connexion->prepare("SELECT domain FROM websitesIPv6 WHERE domain = ? LIMIT 1");
	$stmt->bind_param("s", $hostWithoutSubdomain);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows === 0){ // Pas de résultat trouvé, on insère
		$stmtInsertion = $connexion->prepare("INSERT INTO websitesIPv6 (domain, hasDomainIPv6, hasWWWIPv6, hasMXServersIPv6, hasNSServersIPv6, resultIPv6, dateLastManualCheck, dateLastAutomaticCheck) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$stmtInsertion->bind_param("siiiiiss", $hostWithoutSubdomain, $arrayResultsIPv6["hasDomainIPv6"], $arrayResultsIPv6["hasWWWIPv6"], $arrayResultsIPv6["hasMXServersIPv6"], $arrayResultsIPv6["hasNSServersIPv6"], $arrayResultsIPv6["resultIPv6"], $dateManualEntry, $dateManualEntry);
		$stmtInsertion->execute();
	}else{ // On modifie le résulat existant
		$stmtDelete = $connexion->prepare("DELETE FROM websitesIPv6 WHERE domain = ?");
		$stmtDelete->bind_param("s", $hostWithoutSubdomain);
		$stmtDelete->execute();

		$stmtInsertion = $connexion->prepare("INSERT INTO websitesIPv6 (domain, hasDomainIPv6, hasWWWIPv6, hasMXServersIPv6, hasNSServersIPv6, resultIPv6, dateLastManualCheck, dateLastAutomaticCheck) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$stmtInsertion->bind_param("siiiiiss", $hostWithoutSubdomain, $arrayResultsIPv6["hasDomainIPv6"], $arrayResultsIPv6["hasWWWIPv6"], $arrayResultsIPv6["hasMXServersIPv6"], $arrayResultsIPv6["hasNSServersIPv6"], $arrayResultsIPv6["resultIPv6"], $dateManualEntry, $dateManualEntry);
		$stmtInsertion->execute();
	}

	mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
}

/**
* Incrémente la table numberOfTests à chaque test effectué par un utilisateur
*/
function manualDateInsertion(){
	date_default_timezone_set('UTC');
	$dateManualEntry = date("Y-m-d");

	global $connexion;

	$dateManualEntry = $connexion->real_escape_string($dateManualEntry);// Pour éviter une injection SQL
	$stmt = $connexion->prepare("SELECT dateTests FROM numberOfTests WHERE dateTests = ? LIMIT 1");
	$stmt->bind_param("s", $dateManualEntry);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows === 0){ // Pas de résultat trouvé, on insère
		$stmtInsertion = $connexion->prepare("INSERT INTO numberOfTests (dateTests, numberTests) VALUES (?, 1)");
		$stmtInsertion->bind_param("s", $dateManualEntry);
		$stmtInsertion->execute();
	}else{ // On modifie le résulat existant
		$stmtUpdate = $connexion->prepare("UPDATE numberOfTests SET numberTests = numberTests + 1 WHERE dateTests = ?");
		$stmtUpdate->bind_param("s", $dateManualEntry);
		$stmtUpdate->execute();
	}

	mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
}

?>
