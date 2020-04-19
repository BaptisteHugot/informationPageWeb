<?php

/**
* @file cronFunctions.php
* @brief Fonctions utilisées pour revérifier les sites dont les données sont vieilles de plus d'une semaine
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

include("./../basicFunctions.php");
include("./../db.php");
include("./../db_traitements.php");

/**
* Insertion dans la base de données lorsque cela est fait automatiquement par un CRON
* @param $arrayResultsIPv6 Le tableau comprenant les informations sur les adresses IPv6
* @param $hostWithoutSubdomain Le nom de l'hôte sans le sous-domaine
*/
function automaticCheckInsertion(array $arrayResultsIPv6, string $hostWithoutSubdomain){
	date_default_timezone_set('UTC');
	$dateAutomaticEntry = date("Y-m-d H:i:s");

	global $connexion;

	$hostWithoutSubdomain = $connexion->real_escape_string($hostWithoutSubdomain);// Pour éviter une injection SQL
	$arrayResultsIPv6["hasDomainIPv6"] = intval($connexion->real_escape_string($arrayResultsIPv6["hasDomainIPv6"]));
	$arrayResultsIPv6["hasWWWIPv6"] = intval($connexion->real_escape_string($arrayResultsIPv6["hasWWWIPv6"]));
	$arrayResultsIPv6["hasMXServersIPv6"] = intval($connexion->real_escape_string($arrayResultsIPv6["hasMXServersIPv6"]));
	$arrayResultsIPv6["hasNSServersIPv6"] = intval($connexion->real_escape_string($arrayResultsIPv6["hasNSServersIPv6"]));
	$arrayResultsIPv6["resultIPv6"] = intval($connexion->real_escape_string($arrayResultsIPv6["resultIPv6"]));

	$stmtUpdate = $connexion->prepare("UPDATE websitesIPv6 SET hasDomainIPv6 = ?, hasWWWIPv6 = ?, hasMXServersIPv6 = ?, hasNSServersIPv6 = ?, resultIPv6 = ?, dateLastAutomaticCheck = ? WHERE domain = ?");
	$stmtUpdate->bind_param("iiiiiss", $arrayResultsIPv6["hasDomainIPv6"], $arrayResultsIPv6["hasWWWIPv6"], $arrayResultsIPv6["hasMXServersIPv6"], $arrayResultsIPv6["hasNSServersIPv6"], $arrayResultsIPv6["resultIPv6"], $dateAutomaticEntry, $hostWithoutSubdomain);
	$stmtUpdate->execute();
}

/**
* Vérification automatique des données présentes dans la base de données ayant plus de 7 jours et mises à jour de celles-ci
*/
function automaticCheck(){
	global $connexion;

	$stmt = $connexion->prepare("SELECT domain FROM websitesIPv6 WHERE dateLastAutomaticCheck < NOW() - INTERVAL 7 DAY");
	$stmt->execute();
	$result = $stmt->get_result();
	$jsonData = array();

	if($result === false){ // On arrête le programme si l'exécution de la requête a rencontré un problème
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
		mysqli_close($connexion); // On ferme la connexion à la base de données
		throw new Exception(mysqli_error($connexion));
	}else if(mysqli_num_rows($result) > 0){ // Si au moins un élément est trouvé
		while($array = mysqli_fetch_assoc($result)){ // On stocke chaque ligne de la base de données dans une ligne d'un tableau PHP
			$jsonData[] = $array;
		}
		if($jsonData[0] == null){ // Dans le cas où l'API retourne null, afin d'éviter d'afficher un tableau vide
			echo "Aucune donnée trouvée";
		}else{
			$i = 0;
			// On affiche un tableau avec l'ensemble des éléments correspondants à la requête demandée
			foreach($jsonData as $item){ // Pour chaque élément, on ajoute une nouvelle ligne au tableau
				$hostWithoutSubdomain = $jsonData[$i]['domain'];
				$hostWithWWW = "www." . $hostWithoutSubdomain;

				$resultWithoutSubdomain = dns_get_record($hostWithoutSubdomain, DNS_ALL);
				$resultWithWWW = dns_get_record($hostWithWWW, DNS_ALL);

				list($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayMXServers, $arrayNSServers, $arrayCAAServers, $arraySOAMname, $arraySOARname) = getIPWithoutSubdomain($resultWithoutSubdomain);
				list($arrayMXServersIPv4, $arrayMXServersIPv6) = getMXServers($arrayMXServers);
				list($arrayNSServersIPv4, $arrayNSServersIPv6) = getNSServers($arrayNSServers);
				list($arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW) = getIPWithSubdomain($resultWithWWW);

				$arrayResultsIPv6 = setResults($arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv6WithWWW, $arrayNSServersIPv6, $arrayMXServersIPv6);
				automaticCheckInsertion($arrayResultsIPv6, $hostWithoutSubdomain);
				$i++;
			}
		}
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
	}else echo "Aucune donnée trouvée";
}

?>
