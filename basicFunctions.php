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
* Retourne des informations sur une adresse IP donnée
* @param $ip Adresse IP
* @return $result Tableau comprenant les informations sur l'adresse IP
*/
function getInfoFromIP(string $ip): array{
	$infoIP = file_get_contents("http://ip-api.com/json/".$ip."?fields=status,message,continent,continentCode,country,countryCode,region,regionName,city,district,zip,lat,lon,timezone,currency,isp,org,as,asname,reverse,mobile,proxy,hosting,query&lang=fr");
	$jsonInfoIP = json_decode($infoIP);

	// Le champ message n'existe que lorsque le statut de la requête a pour valeur "fail"
	if($jsonInfoIP->status === "fail"){
		$jsonInfoIPMessage = $jsonInfoIP->message;
	}else{
		$jsonInfoIPMessage = "";
	}

	$result = array(
		"status" => $jsonInfoIP->status,
		"message" => $jsonInfoIPMessage,
		"continent" => $jsonInfoIP->continent,
		"continentCode" => $jsonInfoIP->continentCode,
		"country" => $jsonInfoIP->country,
		"countryCode" => $jsonInfoIP->countryCode,
		"region" => $jsonInfoIP->region,
		"regionName" => $jsonInfoIP->regionName,
		"city" => $jsonInfoIP->city,
		"district" => $jsonInfoIP->district,
		"zip" => $jsonInfoIP->zip,
		"lat" => $jsonInfoIP->lat,
		"lon" => $jsonInfoIP->lon,
		"timezone" => $jsonInfoIP->timezone,
		"currency" => $jsonInfoIP->currency,
		"isp" => $jsonInfoIP->isp,
		"org" => $jsonInfoIP->org,
		"as" => $jsonInfoIP->as,
		"asname" => $jsonInfoIP->asname,
		"reverse" => $jsonInfoIP->reverse,
		"mobile" => $jsonInfoIP->mobile,
		"proxy" => $jsonInfoIP->proxy,
		"hosting" => $jsonInfoIP->hosting,
		"query" => $jsonInfoIP->query
	);

	return $result;
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
* Fonction qui inverse l'adresse IPv4 et ajoute le bon suffixe pour la recherche inversée
* @param $ip L'adresse IPv4 d'origine
* @return $ip L'adresse IPv4 retraitée
*/
function revertIPv4(string $ip) : string{
	$arrayIP = array();
	$arrayIP = explode(".", $ip); // On stocke chaque octet dans une case d'un tableau
	$arrayIP = array_reverse($arrayIP); // On inverse les cases du tableau

	$ipTreatment = implode(".", $arrayIP); // On concatène les cases du tableau en ajoutant un .
	$ipTreatment = $ipTreatment . ".in-addr.arpa"; // On ajoute le suffixe in-addr.arpa

	return $ipTreatment;
}

/**
* Fonction qui inverse l'adresse IPv6 et ajoute le bon suffixe pour la recherche inversée
* @param $ip L'adresse IPv6 d'origine
* @return $ip L'adresse IPv6 retraitée
*/
function revertIPv6(string $ip) : string{
	$ip = expand($ip);

	$ipTreatment = str_replace(":","",$ip); // On supprime les :
	$ipTreatment = strrev($ipTreatment); // On inverse la chaîne de caractères
	$ipTreatment = implode('.',str_split($ipTreatment)); // On ajoute un point entre chaque caractère
	$ipTreatment = $ipTreatment . ".ip6.arpa"; // On ajoute le suffixe ip6.arpa

	return $ipTreatment;
}

/**
* Retourne une adresse IPv6 comprenant tous les éléments qui ne sont pas obligatoire
* @param $ip L'adresse IPv6 en format compact
* @return $ip L'adresse IPv6 en format long
*/
function expand(string $ip) : string{
	$hex = unpack("H*hex", inet_pton($ip));
	$ip = substr(preg_replace("/([A-f0-9]{4})/", "$1:", $hex['hex']), 0, -1);

	return $ip;
}

/**
* Fonction qui retourne l'ensemble des noms de domaine associés à une adresse IP donnée
* @param $ip L'adresse IP recherchée
* @return $arrayHosts Tableau comprenant l'ensemble des noms de domaine
*/
function getHosts(string $ip) : array{
	$arrayIP = array();
	$arrayHosts = array();

	if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
		$cleanIP = revertIPv6($ip);
	}else if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
		$cleanIP = revertIPv4($ip);
	}

	$arrayIP = dns_get_record($cleanIP);

	$i = 0;
	foreach($arrayIP as $item){
		switch($arrayIP[$i]["type"]){
			case "PTR":
			$host = $arrayIP[$i]["target"];
			$index = array_search($host,$arrayHosts);
			if($index === FALSE){
				array_push($arrayHosts,$host);
			}
			break;
			$i++;
		}
	}

	$arrayHosts = array($ip, $arrayHosts);
	return $arrayHosts;
}

/**
* Indique sur quel système d'exploitation PHP est en train de fonctionner
* @return $runOS Le système d'exploitation (Windows ou Autre)
*/
function phpRunOs() : string{
	if(strtoupper(substr(php_uname('s'),0,3)) === "WIN"){
		$runOS = "Windows";
	}else{
		$runOS = "Other";
	}

	return $runOS;
}

/**
* Fonction qui teste si un ping sur une adresse IP ou un serveur fonctionne
* @param $host L'adresse IP ou le nom du serveur Web
* @return Tableau avec l'hôte testé et si le ping a réussi ou non
*/
function pingWithoutPort($host) : array{
	$arrayPing = array();

	if(phpRunOs() === "Windows"){
		$pingresult = exec("ping -n 1 $host", $outcome, $status);
	}else{
		$pingresult = exec("ping -c1 $host", $outcome, $status);
	}

	if (0 == $status) {
		$ping = TRUE;
	}else {
		$ping = FALSE;
	}

	$arrayPing["host"] = $host;
	$arrayPing["ping"] = $ping;

	return $arrayPing;
}

/**
* Fonction qui teste si un ping sur une adresse IP ou un serveur, avec un port spécifié, fonctionne
* @param $host L'adresse IP ou le nom du serveur Web
* @param $port Le port testé
* @return Tableau avec l'hôte et le port testés et si le ping a réussi ou non
*/
function pingWithPort($host, $port) : array{
	$arrayPing = array();
	$timeout = 9;

	$fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
	if (!$fsock){
		$ping = FALSE;
	}else{
		$ping = TRUE;
	}

	$arrayPing["host"] = $host;
	$arrayPing["port"] = $port;
	$arrayPing["ping"] = $ping;

	return $arrayPing;
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

?>
