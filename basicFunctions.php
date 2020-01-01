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
* Vérifie que l'URL entrée existe
* @param $url L'URL d'entrée
* @return $header Le code de l'entête (200 pour succès)
*/
function urlExistence(string $url) : bool{
	$ch = curl_init($url); // On créé un gestionnaire cURL

	curl_setopt($ch, CURLOPT_HEADER, TRUE); // On définit la transmission cURL
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	curl_exec($ch); // On exécute la requête

	if(curl_errno($ch)){
		curl_close($ch);
		return FALSE;
	}

	$header = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	return $header === 200 ? TRUE : FALSE;
}

/**
* Vérifie que l'URL entrée par l'utilisateur correspond bien à un site existant
* @param $searchedWebsite L'URL d'entrée
* @return $urlExists Booléen retournant Vrai si l'URL existe, Faux sinon
*/
function urlExists(string $searchedWebsite) : bool{
	$urlExists = FALSE;
	$patternWithHttp = "#(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~\#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~\#?&//=]*))#";
	$url = strpos($searchedWebsite, "http") !== 0 ? "http://$searchedWebsite" : $searchedWebsite;
	$urlSecured = strpos($searchedWebsite, "https") !== 0 ? "https://$searchedWebsite" : $searchedWebsite;

	if(preg_match($patternWithHttp,$url) && (urlExistence($url) || urlExistence($urlSecured))){
		$urlExists = TRUE;
	}

	return $urlExists;
}

/**
* Indique la dernière URL, après redirections éventuelles, de la page entrée par l'utilisateur
* @param $url L'URL entrée par l'utilisateur
* @return $lastLocation L'URL réelle de l'URL entrée par l'utilisateur
*/
function getLastLocation(string $url) : string{
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_HEADER, TRUE); // On définit la transmission cURL
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	curl_exec($ch);

	if(curl_errno($ch)){
		curl_close($ch);
		return FALSE;
	}

	$lastLocation = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

	curl_close($ch);

	return $lastLocation;
}

/**
* Permet de récupérer l'ensemble des adresses qui seront analysées par le programme afin de connaître leurs adresses IP
* @param $searchedWebsite L'URL entrée par l'utilisateur
* @return $arrayDomains Tableau comprenant l'ensemble des adresses qui seront analysées
*/
function arrayDomains(string $searchedWebsite) : array{
	$url = getLastLocation($searchedWebsite);
	$host = parse_url($url, PHP_URL_HOST);

	$hasSubdomain = FALSE; // Booléen permettant de savoir si une URL a un sous-domaine ou non
	$hasWWW = FALSE; // Booléen permettant de savoir une une URL a le sous-domaine WWW ou non

	if(substr_count($host,".") === 2){ // L'URL a un sous-domaine
		$hostWithSubdomain = $host;
		list($scheme, $domain, $extension) = explode(".",$host); // On récupère le sous-domaine, le domaine et l'extension
		$hostWithoutSubdomain = $domain . "." . $extension;
		$hasSubdomain = TRUE;
		if($scheme === "www"){
			$hasWWW = TRUE;
		}
	}else if(substr_count($host,".") === 1){ // L'URL n'a pas de sous-domaine
		$hostWithSubdomain = "";
		list($domain, $extension) = explode(".",$host); // On récupère le sous-domaine, le domaine et l'extension
		$hostWithoutSubdomain = $domain . "." . $extension;
	}

	$hostWithWWW = "www." . $hostWithoutSubdomain;

	$arrayDomains = array($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW, $extension);

	return $arrayDomains;
}

/**
* Retourne les adresses IP du domaine et les adresses Internet des serveurs MX et NS
* @param $hostWithoutSubdomain Le nom de l'hôte sans aucun sous-domaine
* @param $hostWithWWW Le nom de l'hôte avec le sous-domaine WWW
* @param $hostWithSubdomain Le nom de l'hôte avec un sous-domaine particulier
* @param $hasSubdomain Booléen indiquant Vrai si l'utilisateur souhaite vérifier un sous-domaine particulier, Faux sinon
* @param $hasWWW Booléen indiquant Vrai si l'utilisateur souhaite vérifier le sous-domaine WWW, Faux sinon
* @return $arrayResults Tableau comprenant l'ensemble des adresses IP du domaine ainsi que les adresses Internet des serveurs MX et NS
*/
function manualCheck(string $hostWithoutSubdomain, string $hostWithWWW, string $hostWithSubdomain, bool $hasSubdomain, bool $hasWWW) : array{
	if($hasSubdomain === TRUE && $hasWWW === FALSE){
		$resultWithSubdomain = dns_get_record($hostWithSubdomain, DNS_ALL);
	}

	$resultWithoutSubdomain = dns_get_record($hostWithoutSubdomain, DNS_ALL);

	// On récupère les informations liées au sous-domaine
	if($hasSubdomain === TRUE && $hasWWW === FALSE){
		list($arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain) = getIPWithSubdomain($resultWithSubdomain);
	}else{
		$arrayWebsiteIPv4WithSubdomain = array();
		$arrayWebsiteIPv6WithSubdomain = array();
	}

	$resultWithWWW = dns_get_record($hostWithWWW, DNS_ALL);
	list($arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW) = getIPWithSubdomain($resultWithWWW);

	// On récupère les informations liées au domaine
	list($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayMXServers, $arrayNSServers, $arrayCAAServers, $arraySOAMname, $arraySOARname) = getIPWithoutSubdomain($resultWithoutSubdomain);

	// On récupère les informations liées au serveur MX du domaine
	list($arrayMXServersIPv4, $arrayMXServersIPv6) = getMXServers($arrayMXServers);

	// On récupère les informations liées au serveur NS du domaine
	list($arrayNSServersIPv4, $arrayNSServersIPv6) = getNSServers($arrayNSServers);

	$arrayResultsIPv6 = setResults($arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv6WithWWW, $arrayNSServersIPv6, $arrayMXServersIPv6);
	$arrayResults = array($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain, $arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW, $arrayNSServers, $arrayNSServersIPv4, $arrayNSServersIPv6, $arrayMXServers, $arrayMXServersIPv4, $arrayMXServersIPv6, $arrayCAAServers, $arraySOAMname, $arraySOARname, $hasSubdomain, $hasWWW, $hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $arrayResultsIPv6);

	return $arrayResults;
}

/**
* Effectue des traitements sur l'adresse de la page entrée par l'utilisateur
* @param L'adresse de la page entrée par l'utilisateur
* @return L'adresse de la page entrée par l'utilisateur
*/
function cleanEntry(string $searchedWebsite) : string{
	$searchedWebsite = htmlspecialchars($searchedWebsite, ENT_QUOTES, 'UTF-8'); // Pour éviter une injection XSS
	$searchedWebsite = strtolower($searchedWebsite); // On met la chaîne de caractères en minuscule
	$searchedWebsite = preg_replace('/\s+/', '', $searchedWebsite); // On supprime des éventuels espaces

	return $searchedWebsite;
}

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

/**
* Affiche les sites qui sont 100% en IPv6
*/
function displayHallOfFame(){
	global $connexion;

	$stmt = $connexion->prepare("SELECT domain FROM websitesIPv6 WHERE resultIPv6 = 4");
	$stmt->execute();
	$result = $stmt->get_result();

	$jsonData = array();

	if($result == false){ // On arrête le programme si l'exécution de la requête a rencontré un problème
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
		throw new Exception(mysqli_error($connexion));
	}else if(mysqli_num_rows($result) > 0){ // Si au moins un élément est trouvé
		while($array = mysqli_fetch_assoc($result)){ // On stocke chaque ligne de la base de données dans une ligne d'un tableau PHP
			$jsonData[] = $array;
		}
		if($jsonData[0] == null){ // Dans le cas où l'API retourne null, afin d'éviter d'afficher un tableau vide
			echo "Votre recherche n'a retourné aucune donnée";
		}else{
			$i = 0;
			// On affiche un tableau avec l'ensemble des éléments correspondants à la requête demandée
			echo "<table>";
			echo "<tr><td>Domaines entièrement IPv6</td></tr>";
			foreach($jsonData as $item){ // Pour chaque élément, on ajoute une nouvelle ligne au tableau
				echo "<tr>";
				echo "<td>" . $jsonData[$i]['domain'] . "</td>";
				echo "</tr>";
				$i++;
			}
			echo "</table>";
		}
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
	}else echo "Votre recherche n'a retourné aucune donnée";
}

/**
* Affiche les sites qui sont à 0% en IPv6
*/
function displayHallOfShame(){
	global $connexion;

	$stmt = $connexion->prepare("SELECT domain FROM websitesIPv6 WHERE resultIPv6 = 0");
	$stmt->execute();
	$result = $stmt->get_result();

	$jsonData = array();

	if($result == false){ // On arrête le programme si l'exécution de la requête a rencontré un problème
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
		throw new Exception(mysqli_error($connexion));
	}else if(mysqli_num_rows($result) > 0){ // Si au moins un élément est trouvé
		while($array = mysqli_fetch_assoc($result)){ // On stocke chaque ligne de la base de données dans une ligne d'un tableau PHP
			$jsonData[] = $array;
		}
		if($jsonData[0] == null){ // Dans le cas où l'API retourne null, afin d'éviter d'afficher un tableau vide
			echo "Votre recherche n'a retourné aucune donnée";
		}else{
			$i = 0;
			// On affiche un tableau avec l'ensemble des éléments correspondants à la requête demandée
			echo "<table>";
			echo "<tr><td>Domaines sans une once d'IPv6</td></tr>";
			foreach($jsonData as $item){ // Pour chaque élément, on ajoute une nouvelle ligne au tableau
				echo "<tr>";
				echo "<td>" . $jsonData[$i]['domain'] . "</td>";
				echo "</tr>";
				$i++;
			}
			echo "</table>";
		}
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
	}else echo "Votre recherche n'a retourné aucune donnée";
}

/**
* Affiche les 500 derniers sites testés par les utilisateurs
*/
function lastManualChecks(){
	global $connexion;

	$stmt = $connexion->prepare("SELECT domain, resultIPv6, dateLastManualCheck FROM websitesIPv6 ORDER BY dateLastManualCheck DESC LIMIT 500");
	$stmt->execute();
	$result = $stmt->get_result();

	$jsonData = array();

	if($result == false){ // On arrête le programme si l'exécution de la requête a rencontré un problème
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
		throw new Exception(mysqli_error($connexion));
	}else if(mysqli_num_rows($result) > 0){ // Si au moins un élément est trouvé
		while($array = mysqli_fetch_assoc($result)){ // On stocke chaque ligne de la base de données dans une ligne d'un tableau PHP
			$jsonData[] = $array;
		}
		if($jsonData[0] == null){ // Dans le cas où l'API retourne null, afin d'éviter d'afficher un tableau vide
			echo "Votre recherche n'a retourné aucune donnée";
		}else{
			$i = 0;
			// On affiche un tableau avec l'ensemble des éléments correspondants à la requête demandée
			echo "<table>";
			echo "<tr><td>Derniers domaines testés</td><td>Résultat du test</td><td>Date du test</td><td>Heure du test</td></tr>";
			foreach($jsonData as $item){ // Pour chaque élément, on ajoute une nouvelle ligne au tableau
				echo "<tr>";
				echo "<td>" . $jsonData[$i]['domain'] . "</td>";
				echo "<td>" . str_repeat("★", $jsonData[$i]['resultIPv6']) . str_repeat("☆", 4 - $jsonData[$i]['resultIPv6']) . "</td>";
				$time = strtotime($jsonData[$i]['dateLastManualCheck'].'UTC');
				$dateInLocal = date("d/m/Y", $time); // On convertit la date au format local
				$hourInLocal = date("H:i:s", $time); // On convertir l'heure au format local
				echo "<td>" . $dateInLocal . "</td>";
				echo "<td>" . $hourInLocal . "</td>";
				echo "</tr>";
				$i++;
			}
			echo "</table>";
		}
		mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
	}else echo "Votre recherche n'a retourné aucune donnée";
}

/**
* Retourne le User Agent du navigateur de l'utilisateur
* @return $user User Agent du navigateur de l'utilisateur
*/
function getUserAgent() : string{
	if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] !== ""){
		$user = $_SERVER['HTTP_USER_AGENT'];
	}else{
		$user = "";
	}

	return $user;
}

/**
* Retourne les adresses IP pour un sous-domaine spécifique
* @param $resultWithSubdomain Tableau comprenant l'ensemble des informations du sous-domaine spécifique
* @return $arrayResult Tableau comprenant les adresses IP du sous-domaine recherché
*/
function getIPWithSubdomain(array $resultWithSubdomain) : array{
	$arrayWebsiteIPv4WithSubdomain = array();
	$arrayWebsiteIPv6WithSubdomain = array();
	$i=0;
	foreach($resultWithSubdomain as $item){
		switch($resultWithSubdomain[$i]["type"]){
			case "AAAA": // Adresse IPv6
			$ipv6 = $resultWithSubdomain[$i]["ipv6"];
			if(filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
				$index = array_search($ipv6,$arrayWebsiteIPv6WithSubdomain);
				if($index === FALSE){ // Permet de ne pas avoir de doublons dans le tableau
					array_push($arrayWebsiteIPv6WithSubdomain,$ipv6);
				}
			}
			break;
			case "A": // Adresse IPv4
			$ipv4 = $resultWithSubdomain[$i]["ip"];
			if(filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
				$index = array_search($ipv4,$arrayWebsiteIPv4WithSubdomain);
				if($index === FALSE){
					array_push($arrayWebsiteIPv4WithSubdomain,$ipv4);
				}
			}
			break;
		}
		$i++;
	}

	$arrayResult = array($arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain);
	return $arrayResult;
}

/**
* Retourne les adresses IP utilisées par le nom de domaine (et sans sous-domaine)
* @param $resultWithoutSubdomain Tableau comprenant l'ensemble des informations du nom de domaine
* @return $arrayResult Tableau comprenant les adresses IP liées au nom de domaine
*/
function getIPWithoutSubdomain(array $resultWithoutSubdomain) : array{
	$arrayWebsiteIPv4WithoutSubdomain = array();
	$arrayWebsiteIPv6WithoutSubdomain = array();
	$arrayNSServers = array();
	$arrayMXServers = array();
	$arraySOAMname = array();
	$arraySOARname = array();
	$arrayCAAServers = array();
	$i=0;

	foreach($resultWithoutSubdomain as $item){
		switch($resultWithoutSubdomain[$i]["type"]){
			case "AAAA": // Adresse IPv6
			$ipv6 = $resultWithoutSubdomain[$i]["ipv6"];
			if(filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
				$index = array_search($ipv6,$arrayWebsiteIPv6WithoutSubdomain);
				if($index === FALSE){ // Permet de ne pas avoir de doublons dans le tableau
					array_push($arrayWebsiteIPv6WithoutSubdomain,$ipv6);
				}
			}
			break;
			case "A": // Adresse IPv4
			$ipv4 = $resultWithoutSubdomain[$i]["ip"];
			if(filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
				$index = array_search($ipv4,$arrayWebsiteIPv4WithoutSubdomain);
				if($index === FALSE){
					array_push($arrayWebsiteIPv4WithoutSubdomain,$ipv4);
				}
			}
			break;
			case "MX": // Serveur de courrier électronique
			$mxServer = $resultWithoutSubdomain[$i]["target"];
			$index = array_search($mxServer,$arrayMXServers);
			if($index === FALSE){
				array_push($arrayMXServers,$mxServer);
			}
			break;
			case "NS": // FQDN du nom de serveur responsable du nom de domaine
			$nsServer = $resultWithoutSubdomain[$i]["target"];
			$index = array_search($nsServer,$arrayNSServers);
			if($index === FALSE){
				array_push($arrayNSServers,$nsServer);
			}
			break;
			case "CAA": // Certificats
			$caaServer = $resultWithoutSubdomain[$i]["value"];
			$index = array_search($caaServer,$arrayCAAServers);
			if($index === FALSE){
				array_push($arrayCAAServers,$caaServer);
			}
			break;
			case "SOA": // FQDN de la source de l'enregistrement
			$soaMName = $resultWithoutSubdomain[$i]["mname"];
			$indexMName = array_search($soaMName,$arraySOAMname);
			if($indexMName === FALSE){
				array_push($arraySOAMname,$soaMName);
			}
			$soaRName = $resultWithoutSubdomain[$i]["rname"];
			$indexRName = array_search($soaRName,$arraySOARname);
			if($indexRName === FALSE){
				array_push($arraySOARname,$soaRName);
			}
			break;
		}
		$i++;
	}

	$arrayResult = array($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayMXServers, $arrayNSServers, $arrayCAAServers, $arraySOAMname, $arraySOARname);
	return $arrayResult;
}

/**
* Retourne les adresses IP des serveurs MX
* @param $arrayMXServers Tableau comprenant les adresses Internet des serveurs MX
* @return $arrayResult Tableau comprenant les adresses IP des serveurs MX
*/
function getMXServers(array $arrayMXServers) : array{
	$arrayMXServersIPv4 = array();
	$arrayMXServersIPv6 = array();

	$j = 0;
	foreach($arrayMXServers as $item){
		$ipInfo = dns_get_record($arrayMXServers[$j]);
		$i = 0;
		foreach($ipInfo as $item){
			switch($ipInfo[$i]["type"]){
				case "AAAA":
				$ipv6 = $ipInfo[$i]["ipv6"];
				if(filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
					$index = array_search($ipv6,$arrayMXServersIPv6);
					if($index === FALSE){
						array_push($arrayMXServersIPv6,$ipv6);
					}
				}
				break;
				case "A":
				$ipv4 = $ipInfo[$i]["ip"];
				if(filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
					$index = array_search($ipv4,$arrayMXServersIPv4);
					if($index === FALSE){
						array_push($arrayMXServersIPv4,$ipv4);
					}
				}
				break;
			}
			$i++;
		}
		$j++;
	}
	$arrayResult = array($arrayMXServersIPv4, $arrayMXServersIPv6);
	return $arrayResult;
}

/**
* Retourne les adresses IP des serveurs NS
* @param $arrayNSServers Tableau comprenant les adresses Internet des serveurs NS
* @return $arrayResult Tableau comprenant les adresses IP des serveurs NS
*/
function getNSServers(array $arrayNSServers) : array{
	$arrayNSServersIPv4 = array();
	$arrayNSServersIPv6 = array();

	$j = 0;
	foreach($arrayNSServers as $item){
		$ipInfo = dns_get_record($arrayNSServers[$j]);
		$i = 0;
		foreach($ipInfo as $item){
			switch($ipInfo[$i]["type"]){
				case "AAAA":
				$ipv6 = $ipInfo[$i]["ipv6"];
				if(filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
					$index = array_search($ipv6,$arrayNSServersIPv6);
					if($index === FALSE){
						array_push($arrayNSServersIPv6,$ipv6);
					}
				}
				break;
				case "A":
				$ipv4 = $ipInfo[$i]["ip"];
				if(filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
					$index = array_search($ipv4,$arrayNSServersIPv4);
					if($index === FALSE){
						array_push($arrayNSServersIPv4,$ipv4);
					}
				}
				break;
			}
			$i++;
		}
		$j++;
	}
	$arrayResult = array($arrayNSServersIPv4, $arrayNSServersIPv6);
	return $arrayResult;
}

/**
* Retourne les adresses IP de l'utilisateur
* @return $array Tableau comprenant les adresses IP de l'utilisateur
*/
function getUserIP() : array{
	$ipv4 = file_get_contents('https://api.ipify.org');
	$ipv6 = file_get_contents('https://api6.ipify.org');

	if(!filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
		$ipv4 = "";
	}

	if(!filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
		$ipv6 = "";
	}

	$array = array(
		"IPv4" => $ipv4,
		"IPv6" => $ipv6,
	);

	return $array;
}

/**
* Retourne le Whois d'un domaine spécifique
* @param $hostWithoutSubdomain Le nom de domaine
* @param $extension L'extension associée au nom de domaine
* @return $whois Tableau comprenant le Whois
*/
function getWhois(string $hostWithoutSubdomain, string $extension) : array{
	$contents = file_get_contents("./whois.servers.json"); // On parse le fichier Json comprenant les registrars de chaque extension
	$contents = utf8_encode($contents);
	$results = json_decode($contents);

	foreach($results->$extension as $item){
		$registrar = $results->$extension[0]; // On récupère le registrar en fonction de l'extension du domaine
	}

	$whois = array();
	$whoisSocket = fsockopen($registrar, 43); // On ouvre une socket sur le serveur Whois
	fwrite($whoisSocket, $hostWithoutSubdomain . "\r\n"); // On envoie la requête

	while (!feof($whoisSocket)) { // On récupère les informations
		$whois[] = fgets($whoisSocket, 128);
	}

	return $whois;
}

/**
* Retourne les balises meta d'une page spécifique
* @param $searchedWebsite L'URL de la page recherchée
* @return $arrayResult Tableau comprenant les balises meta
*/
function getMeta(string $searchedWebsite) : array{
	$metaTags = array();
	$metaTags = get_meta_tags($searchedWebsite);

	$keys = array_keys($metaTags);
	$values = array_values($metaTags);

	$arrayResult = array($metaTags, $keys, $values);
	return $arrayResult;
}

/**
* Retourne les entêtes d'une page spécifique
* @param $searchedWebsite L'URL de la page recherchée
* @return $arrayResult Tableau comprenant les entêtes
*/
function getHeaders(string $searchedWebsite) : array{
	$httpHeaders = array();
	$httpHeaders = get_headers($searchedWebsite);

	$keys = array_keys($httpHeaders);
	$values = array_values($httpHeaders);

	$arrayResult = array($httpHeaders, $keys, $values);
	return $arrayResult;
}

/**
* Crée un tableau récapitulant le nombre de champs en IPv6 en vue d'introduire ces informations dans la base de données
* @param $arrayWebsiteIPv6WithoutSubdomain Tableau des adresses IPv6 du domaine sans sous-domaine
* @param $arrayWebsiteIPv6WithWWW Tableau des adresses IPv6 du domaine avec le sous-domaine WWW
* @param $arrayNSServersIPv6 Tableau des adresses IPv6 des serveurs NS
* @param $arrayMXServersIPv6 Tableau des adresses IPv6 des serveurs MX
* @return $array Tableau comprenant 4 booléens récapitulant si les éléments en entrée sont en IPv6, le nombre de champs en IPv4 ainsi que la date courante
*/
function setResults(array $arrayWebsiteIPv6WithoutSubdomain, array $arrayWebsiteIPv6WithWWW, array $arrayNSServersIPv6, array $arrayMXServersIPv6) : array{
	$hasDomainIPv6 = FALSE;
	$hasWWWIPv6 = FALSE;
	$hasMXServersIPv6 = FALSE;
	$hasNSServersIPv6 = FALSE;
	$resultIPv6 = 0;

	date_default_timezone_set('UTC');
	$dateSubmission = date("Y-m-d H:i:s");

	if(!empty($arrayWebsiteIPv6WithoutSubdomain)){
		$hasDomainIPv6 = TRUE;
		$resultIPv6++;
	}

	if(!empty($arrayWebsiteIPv6WithWWW)){
		$hasWWWIPv6 = TRUE;
		$resultIPv6++;
	}

	if(!empty($arrayNSServersIPv6)){
		$hasNSServersIPv6 = TRUE;
		$resultIPv6++;
	}

	if(!empty($arrayMXServersIPv6)){
		$hasMXServersIPv6 = TRUE;
		$resultIPv6++;
	}

	$array = array(
		"hasDomainIPv6" => $hasDomainIPv6,
		"hasWWWIPv6" => $hasWWWIPv6,
		"hasMXServersIPv6" => $hasMXServersIPv6,
		"hasNSServersIPv6" => $hasNSServersIPv6,
		"resultIPv6" => $resultIPv6,
		"dateLastAutomaticCheck" => $dateSubmission
	);

	return $array;
}

/**
* Affiche les adresses IP d'un site Internet
* @param $arrayWebsiteIPv4WithoutSubdomain Adresses IPv4 du site sans sous-domaine
* @param $arrayWebsiteIPv6WithoutSubdomain Adresses IPv6 du site sans sous-domaine
* @param $arrayWebsiteIPv4WithSubdomain Adresses IPv4 du site avec sous-domaine
* @param $arrayWebsiteIPv6WithSubdomain Adresses IPv6 du site avec sous-domaine
* @param $arrayWebsiteIPv4WithWWW Adresses IPv4 du site sur le sous-domaine WWW
* @param $arrayWebsiteIPv6WithWWW Adresses IPv6 du site sur le sous-domaine WWW
* @param $arrayNSServers URL des serveurs NS
* @param $arrayNSServersIPv4 Adresses IPv4 des serveurs NS
* @param $arrayNSServersIPv6 Adresses IPv6 des serveurs NS
* @param $arrayMXServers URL des serveurs MX
* @param $arrayMXServersIPv4 Adresses IPv4 des serveurs MX
* @param $arrayMXServersIPv6 Adresses IPv6 des serveurs MX
* @param $arrayCAAServers URL des serveurs CAA
* @param $arraySOAMname URL des serveurs SOA (Mname)
* @param $arraySOARname URL des serveurs SOA (Rname)
* @param $hasSubdomain Booléen pour connaître si le site a un sous-domaine
* @param $hasWWW Booléen pour connaître si le site a un sous-domaine WWW
* @param $hostWithoutSubdomain Nom de l'hôte sans le sous-domaine
* @param $hostWithWWW Nom de l'hôte avec WWW
* @param $hostWithSubdomain Nom de l'hôte avec le sous-domaine
*/
function displayDomainServers(array $arrayWebsiteIPv4WithoutSubdomain, array $arrayWebsiteIPv6WithoutSubdomain, array $arrayWebsiteIPv4WithSubdomain, array $arrayWebsiteIPv6WithSubdomain, array $arrayWebsiteIPv4WithWWW, array $arrayWebsiteIPv6WithWWW, array $arrayNSServers, array $arrayNSServersIPv4, array $arrayNSServersIPv6, array $arrayMXServers, array $arrayMXServersIPv4, array $arrayMXServersIPv6, array $arrayCAAServers, array $arraySOAMname, array $arraySOARname, bool $hasSubdomain, bool $hasWWW, string $hostWithoutSubdomain, string $hostWithWWW, string $hostWithSubdomain){
	echo "<table>";
	echo "<tr><td></td><td><b>Informations</b></td><td><b>IPv4</b></td><td><b>IPv6</b></td></tr>";

	if(empty($arrayWebsiteIPv6WithoutSubdomain) && empty($arrayWebsiteIPv4WithoutSubdomain)){
		echo "<tr><td>Domaine</td><td>" . $hostWithoutSubdomain . "</td><td>" . implode("<br />",$arrayWebsiteIPv4WithoutSubdomain) . "</td><td>" . implode("<br />",$arrayWebsiteIPv6WithoutSubdomain) . "</td></tr>";
	}else{
		if(empty($arrayWebsiteIPv6WithoutSubdomain)){
			echo "<tr><td style='background-color:red'>Domaine</td><td style='background-color:red'>" . $hostWithoutSubdomain . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv4WithoutSubdomain) . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv6WithoutSubdomain) . "</td></tr>";
		}else{
			echo "<tr><td style='background-color:palegreen'>Domaine</td><td style='background-color:palegreen'>" . $hostWithoutSubdomain . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv4WithoutSubdomain) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv6WithoutSubdomain) . "</td></tr>";
		}
	}

	if($hasSubdomain === TRUE && $hasWWW === FALSE){
		if(empty($arrayWebsiteIPv6WithSubdomain) && empty($arrayWebsiteIPv4WithSubdomain)){
			echo "<tr><td>Sous-domaine</td><td>" . $hostWithSubdomain . "</td><td>" . implode("<br />",$arrayWebsiteIPv4WithSubdomain) . "</td><td>" . implode("<br />",$arrayWebsiteIPv6WithSubdomain) . "</td></tr>";
		}else{
			if(empty($arrayWebsiteIPv6WithSubdomain)){
				echo "<tr><td style='background-color:red'>Sous-domaine</td><td style='background-color:red'>" . $hostWithSubdomain . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv4WithSubdomain) . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv6WithSubdomain) . "</td></tr>";
			}else{
				echo "<tr><td style='background-color:palegreen'>Sous-domaine</td><td style='background-color:palegreen'>" . $hostWithSubdomain . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv4WithSubdomain) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv6WithSubdomain) . "</td></tr>";
			}
		}
	}

	if(empty($arrayWebsiteIPv6WithWWW) && empty($arrayWebsiteIPv4WithWWW)){
		echo "<tr><td>Sous-domaine WWW</td><td>" . $hostWithWWW . "</td><td>" . implode("<br />",$arrayWebsiteIPv4WithWWW) . "</td><td>" . implode("<br />",$arrayWebsiteIPv6WithWWW) . "</td></tr>";
	}else{
		if(empty($arrayWebsiteIPv6WithWWW)){
			echo "<tr><td style='background-color:red'>Sous-domaine WWW</td><td style='background-color:red'>" . $hostWithWWW . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv4WithWWW) . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv6WithWWW) . "</td></tr>";
		}else{
			echo "<tr><td style='background-color:palegreen'>Sous-domaine WWW</td><td style='background-color:palegreen'>" . $hostWithWWW . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv4WithWWW) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv6WithWWW) . "</td></tr>";
		}
	}

	if(empty($arrayNSServersIPv6) && empty($arrayNSServersIPv4)){
		echo "<tr><td>Serveur NS</td><td>" . implode("<br />",$arrayNSServers) . "</td><td>" . implode("<br />",$arrayNSServersIPv4) . "</td><td>" . implode("<br />",$arrayNSServersIPv6) . "</td></tr>";
	}else{
		if(empty($arrayNSServersIPv6)){
			echo "<tr><td style='background-color:red'>Serveur NS</td><td style='background-color:red'>" . implode("<br />",$arrayNSServers) . "</td><td style='background-color:red'>" . implode("<br />",$arrayNSServersIPv4) . "</td><td style='background-color:red'>" . implode("<br />",$arrayNSServersIPv6) . "</td></tr>";
		}else{
			echo "<tr><td style='background-color:palegreen'>Serveur NS</td><td style='background-color:palegreen'>" . implode("<br />",$arrayNSServers) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayNSServersIPv4) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayNSServersIPv6) . "</td></tr>";
		}
	}

	if(empty($arrayMXServersIPv6) && empty($arrayMXServersIPv4)){
		echo "<tr><td>Serveur MX</td><td>" . implode("<br />",$arrayMXServers) . "</td><td>" . implode("<br />",$arrayMXServersIPv4) . "</td><td>" . implode("<br />",$arrayMXServersIPv6) . "</td></tr>";
	}else{
		if(empty($arrayMXServersIPv6)){
			echo "<tr><td style='background-color:red'>Serveur MX</td><td style='background-color:red'>" . implode("<br />",$arrayMXServers) . "</td><td style='background-color:red'>" . implode("<br />",$arrayMXServersIPv4) . "</td><td style='background-color:red'>" . implode("<br />",$arrayMXServersIPv6) . "</td></tr>";
		}else{
			echo "<tr><td style='background-color:palegreen'>Serveur MX</td><td style='background-color:palegreen'>" . implode("<br />",$arrayMXServers) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayMXServersIPv4) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayMXServersIPv6) . "</td></tr>";
		}
	}
	echo "<tr><td>Serveur CAA</td><td>" . implode("<br />",$arrayCAAServers) . "</td></tr>";
	echo "<tr><td>Serveur Mname</td><td>" . implode("<br />",$arraySOAMname) . "</td></tr>";
	echo "<tr><td>Serveur Rname</td><td>" . implode("<br />",$arraySOARname) . "</td></tr>";
	echo "</table>";
	echo "<br /><br />";
}

/**
* Affiche le whois d'un site Internet
* @param $whois Information du whois
*/
function displayWhois(array $whois){
	if($whois === NULL){
		echo "Pas de Whois disponible. <br />";
	}else{
		echo "<table>";
		$i=0;
		echo "<tr><td><b>Valeur</b></td></tr>";
		foreach($whois as $item){
			if($whois[$i]=== "\r\n" || $whois[$i] === "\n" || $whois[$i] === ""){
				echo "<tr><td style='background-color: blue'></td>";
				$i++;
			}else{
				echo "<tr><td>" . $whois[$i] . "</td></tr>";
				$i++;
			}
		}
		echo "</table>";
	}
}

/**
* Affiche les entêtes HTTP d'une page Internet
* @param $httpHeaders Entêtes HTTP
* @param $keys Clés du tableau des entêtes HTTP
* @param $values Valeurs du tableau des entêtes HTTP
*/
function displayHeaders(array $httpHeaders, array $keys, array $values){
	if($httpHeaders === NULL){
		echo "Pas d'entêtes disponibles. <br />";
	}else{
		echo "<table>";
		$i=0;
		echo "<tr><td><b>Entête</b></td><td><b>Valeur</b></td></tr>";
		foreach($httpHeaders as $item){
			echo "<tr><td>" . strtok($values[$i],":") . "</td><td>" . strtok(":") . "</td></tr>";
			$i++;
		}
		echo "</table>";
	}
}

/**
* Affiche les balises meta d'une page Internet
* @param $metaTags Balises meta
* @param $keys Clés du tableau des balises meta
* @param $values Valeurs du tableau des balises meta
*/
function displayMeta(array $metaTags, array $keys, array $values){
	if($metaTags === NULL){
		echo "Pas de balises meta disponibles. <br />";
	}else{
		echo "<table>";
		$i=0;
		echo "<tr><td><b>Balise</b></td><td><b>Valeur</b></td></tr>";
		foreach($metaTags as $item){
			echo "<tr><td>" . $keys[$i] . "</td><td>" . $values[$i] . "</td></tr>";
			$i++;
		}
		echo "</table>";
	}
}

/**
* Affiche les adresses IP de l'utilisateur
* @param $usersIP Adresses IP de l'utilisateur
*/
function displayUsersIP(array $usersIP){
	echo "<table>";
	if(!empty($usersIP["IPv6"])){
		echo "<tr><td><b>IPv4</b></td><td>" . $usersIP["IPv4"] . "</td></tr>";
		echo "<tr><td style='background-color:palegreen'><b>IPv6</b></td><td style='background-color:palegreen'>" . $usersIP["IPv6"] . "</td></tr>";
	}else{
		echo "<tr><td style='background-color:red'><b>IPv4</b></td><td style='background-color:red'>" . $usersIP["IPv4"] . "</td></tr>";
		echo "<tr><td><b>IPv6</b></td><td>" . $usersIP["IPv6"] . "</td></tr>";
	}
	echo "</table>";
}

/**
* Affiche le User Agent du navigateur
* @param $user Le User Agent du navigateur
*/
function displayUserAgent(string $user){
	echo "<table>";
	if($user === ""){
		echo "<tr><td><b>User Agent</b></td><td>Pas d'user agent défini.</td></tr>";
	}else{
		echo "<tr><td><b>User Agent</b></td><td>" . $user . "</td></tr>";
	}
}

/**
* Affiche le nombre d'étoiles correspondant au nombre de champs avec une IPv6
* @param $arrayResultsIPv6 Le tableau comprenant les informations sur les adresses en IPv6
*/
function displayStars(array $arrayResultsIPv6){
	echo "Score IPv6 : " . str_repeat("★", $arrayResultsIPv6["resultIPv6"]) . str_repeat("☆", 4 - $arrayResultsIPv6["resultIPv6"]);
}

/**
* Affiche l'adresse de la page recherchée
* @param $searchedWebsite L'adresse de la page recherchée entrée par l'utilisateur
*/
function displayWebsite(string $searchedWebsite){
	echo "<br />Page recherchée : <b>" . $searchedWebsite . "</b><br /><br />";
}

/**
* Affiche le temps d'exécution total
* @param $start Début du chronomètre
* @param $end Fin du chronomètre
*/
function displayExecutionTime(float $start, float $end){
	echo "<br /><i>Page exécutée en " . number_format($end-$start,2) . " secondes.";
}

/**
* Affiche un message d'erreur lorsqu'un utilisateur entre une adresse au mauvais format
*/
function displayErrorFormat(){
	echo "Vous n'avez pas entré un site Internet au bon format.";
}
?>
