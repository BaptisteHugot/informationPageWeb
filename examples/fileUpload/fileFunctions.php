<?php

/**
* @file fileFunctions.php
* @brief Fonctions permettant de vérifier manuellement une liste de sites disponibles dans un fichier .csv
* @warning Attention, pour des raisons évidentes de sécurité, ce fichier ne doit pas être accessible à tous les utilisateurs !
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

include("./../basicFunctions.php");
include("./../db.php");

/**
* Lecture du fichier fourni par l'utilisateur
* @param $urlFile L'URL du fichier entrée par l'utilisateur
* @return $arrayWebsites Tableau comprenant chaque URL à analyser
*/
function readUserFile(string $urlFile) : array{
	$arrayWebsites = array();

	if(!file_exists($urlFile)){
		echo "Le fichier n'existe pas !";
	}else{
		$handle = fopen($urlFile, "r");
		if($handle){
			while(($buffer = fgets($handle, 4096)) !== FALSE){
				array_push($arrayWebsites,$buffer);
			}
			if(!feof($handle)){
				echo "Erreur de fichier !";
			}
			fclose($handle);
		}
	}

	return $arrayWebsites;
}

/**
* Traitement du fichier fourni par l'utilisateur
* @param $arrayWebsites Tableau comprenant chaque URL à analyser
*/
function automaticTreatment(array $arrayWebsites){
	$i=0;
	echo "<table>";
	foreach($arrayWebsites as $item){
		$arrayWebsites[$i] = cleanEntry($arrayWebsites[$i]);

		if(urlExists($arrayWebsites[$i])){
			list($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW) = arrayDomains($arrayWebsites[$i]);

			list($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain, $arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW, $arrayNSServers, $arrayNSServersIPv4, $arrayNSServersIPv6, $arrayMXServers, $arrayMXServersIPv4, $arrayMXServersIPv6, $arrayCAAServers, $arraySOAMname, $arraySOARname, $hasSubdomain, $hasWWW, $hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $arrayResultsIPv6) = manualCheck($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW);

			manualCheckInsertion($arrayResultsIPv6, $hostWithoutSubdomain);

			echo "<tr><td>" . $arrayWebsites[$i] . "</td></tr>";
		}
		$i++;
	}
	echo "</table>";
}

?>
