<?php

/**
* @file index.php
* @brief Page permettant de récupérer les informations sur les adresses IP utilisées par un domaine
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débogage, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débogage, à supprimer en production */

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Récupération d'informations sur une page Internet</title>
	<link rel="StyleSheet" type="text/css" href="style.css">
	<script
	src="https://code.jquery.com/jquery-3.4.1.min.js"
	integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
	crossorigin="anonymous"></script>
	<script src="./style.js"></script>
</head>

<body>
	<!-- Le formulaire qui sera utilisé -->
	<form name="form" method="post" action="index.php" id="form">
		<input type="radio" id="serveur" name="choix" value="serveur" class="radioSelect" required>Serveurs du domaine
		<input type="radio" id="lastCheck" name="choix" value="lastCheck" class="radioSelect" required> Derniers sites testés
		<input type="radio" id="hallOfFame" name="choix" value="hallOfFame" class="radioSelect" required> Hall of Fame
		<input type="radio" id="hallOfShame" name="choix" value="hallOfShame" class="radioSelect" required> Hall of Shame
		<br />
		<input type="radio" id="whois" name="choix" value="whois" class="radioSelect" required>Whois du domaine
		<input type="radio" id="http" name="choix" value="http" class="radioSelect" required>Entête HTTP de la page
		<input type="radio" id="meta" name="choix" value="meta" class="radioSelect" required> Balises meta de la page
		<br />
		<input type="radio" id="usersIP" name="choix" value="usersIP" class="radioSelect" required> Adresses IP de l'utilisateur
		<input type="radio" id="userAgent" name="choix" value="userAgent" class="radioSelect" required> Navigateur de l'utilisateur

		<br />

		<input type="text" class="specificField serveur whois http meta" id="site" name="site" placeholder="URL de la page Internet : " />

		<br />

		<input type="submit" name="submit"></input>
	</form>

</body>
</html>

<?php
include("./../db.php");
include("displayFunctions.php");

if(isset($_POST["choix"]) && $_POST["choix"] != ""){
	$radioValue = $_POST["choix"]; // On récupère la valeur du radio bouton

	if($radioValue === "usersIP"){
		$usersIP = getUserIP();
		displayUsersIP($usersIP);
	}else if($radioValue === "userAgent"){
		$user = getUserAgent();
		displayUserAgent($user);
	}else if($radioValue === "lastCheck"){
		lastManualChecks();
	}else if($radioValue === "hallOfFame"){
		displayHallOfFame();
	}else if($radioValue === "hallOfShame"){
		displayHallOfShame();
	}else{
		if(isset($_POST["site"]) && $_POST["site"] != ""){
			$start = microtime(true); // Début du chronomètre

			$searchedWebsite = $_POST["site"]; // On récupère la valeur du champ texte
			$searchedWebsite = cleanEntry($searchedWebsite);

			if(urlExists($searchedWebsite)){ // On vérifie que le site entré est au bon format
				list($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW, $extension) = arrayDomains($searchedWebsite);

				displayWebsite($searchedWebsite);

				if($radioValue === "serveur"){ // On récupère les informations sur les adresses IP
					list($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain, $arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW, $arrayNSServers, $arrayNSServersIPv4, $arrayNSServersIPv6, $arrayMXServers, $arrayMXServersIPv4, $arrayMXServersIPv6, $arrayCAAServers, $arraySOAMname, $arraySOARname, $hasSubdomain, $hasWWW, $hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $arrayResultsIPv6) = manualCheck($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW);
					displayStars($arrayResultsIPv6);

					displayDomainServers($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain, $arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW, $arrayNSServers, $arrayNSServersIPv4, $arrayNSServersIPv6, $arrayMXServers, $arrayMXServersIPv4, $arrayMXServersIPv6, $arrayCAAServers, $arraySOAMname, $arraySOARname, $hasSubdomain, $hasWWW, $hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain);

					manualCheckInsertion($arrayResultsIPv6, $hostWithoutSubdomain);

					manualDateInsertion();
				} else if($radioValue === "whois"){ // On récupère les informations du Whois
					$whois = array();
					$whois = getWhois($hostWithoutSubdomain,$extension);

					displayWhois($whois);
				}else if($radioValue === "http"){ // On récupère les entêtes HTTP
					list($httpHeaders, $keys, $values) = getHeaders(getLastLocation($searchedWebsite));

					displayHeaders($httpHeaders, $keys, $values);
				}else if($radioValue === "meta"){ // On récupère l'ensemble des balises meta
					list($metaTags, $keys, $values) = getMeta(getLastLocation($searchedWebsite));

					displayMeta($metaTags, $keys,$values);
				}
				$end = microtime(true); // Fin du chronomètre
				displayExecutionTime($start, $end);
				mysqli_close($connexion); // On ferme la connexion à la base de données
			}else{
				displayErrorFormat();
			}
		}
	}
}
?>
