<?php

/**
* @file api.php
* @brief Génère les résultats au format Json pour être réutilisés par une API dans d'autres programmes
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débogage, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débogage, à supprimer en production */

include("basicFunctions.php");

header("Content-Type:application/json");

/**
* Retourne les adresses IP du domaine et les adresses Internet des serveurs MX et NS au format Json
* @param $searchedWebsite L'URL de la page recherchée
*/
function jsonManualCheck(string $searchedWebsite){
	$searchedWebsite = cleanEntry($searchedWebsite);
	$jsonData = array();

	if(urlExists($searchedWebsite)){
		list($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW, $extension) = arrayDomains($searchedWebsite);
		list($arrayWebsiteIPv4WithoutSubdomain, $arrayWebsiteIPv6WithoutSubdomain, $arrayWebsiteIPv4WithSubdomain, $arrayWebsiteIPv6WithSubdomain, $arrayWebsiteIPv4WithWWW, $arrayWebsiteIPv6WithWWW, $arrayNSServers, $arrayNSServersIPv4, $arrayNSServersIPv6, $arrayMXServers, $arrayMXServersIPv4, $arrayMXServersIPv6, $arrayCAAServers, $arraySOAMname, $arraySOARname, $hasSubdomain, $hasWWW, $hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $arrayResultsIPv6) = manualCheck($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW);

		$jsonData["WithoutSubdomain_IPv4"] = $arrayWebsiteIPv4WithoutSubdomain;
		$jsonData["WithoutSubdomain_IPv6"] = $arrayWebsiteIPv6WithoutSubdomain;
		$jsonData["WithSubdomain_IPv4"] = $arrayWebsiteIPv4WithSubdomain;
		$jsonData["WithSubdomain_IPv6"] = $arrayWebsiteIPv6WithSubdomain;
		$jsonData["WithWWW_IPv4"] = $arrayWebsiteIPv4WithWWW;
		$jsonData["WithWWW_IPv6"] = $arrayWebsiteIPv6WithWWW;
		$jsonData["NSServers"] = $arrayNSServers;
		$jsonData["NSServers_IPv4"] = $arrayNSServersIPv4;
		$jsonData["NSServers_IPv6"] = $arrayNSServersIPv6;
		$jsonData["MXServers"] = $arrayMXServers;
		$jsonData["MXServers_IPv4"] = $arrayMXServersIPv4;
		$jsonData["MXServers_IPv6"] = $arrayMXServersIPv6;
		$jsonData["CAAServers"] = $arrayCAAServers;
		$jsonData["MnameServers"] = $arraySOAMname;
		$jsonData["RnameServers"] = $arraySOARname;
	}else{
		$jsonData[] = null;
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne les entêtes d'une page spécifique au format Json
* @param $searchedWebsite L'URL de la page recherchée
*/
function jsonHeader(string $searchedWebsite){
	$searchedWebsite = cleanEntry($searchedWebsite);
	$jsonData = array();

	if(urlExists($searchedWebsite)){
		list($httpHeaders, $keys, $values) = getHeaders(getLastLocation($searchedWebsite));
		if($httpHeaders === null){
			$jsonData[] = null;
		}else{
			$i=0;
			foreach($httpHeaders as $item){
				$jsonData[strtok($values[$i],":")] =  strstr($values[$i],":");
				$i++;
			}
		}
	}else{
		$jsonData[] = null;
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne les balises meta d'une page spécifique au format Json
* @param $searchedWebsite L'URL de la page recherchée
*/
function jsonMeta(string $searchedWebsite){
	$searchedWebsite = cleanEntry($searchedWebsite);
	$jsonData = array();

	if(urlExists($searchedWebsite)){
		list($metaTags, $keys, $values) = getMeta(getLastLocation($searchedWebsite));

		if($metaTags === null){
			$jsonData[] = null;
		}else{
			$i=0;
			foreach($metaTags as $item){
				$jsonData[$keys[$i]] = $values[$i];
				$i++;
			}
		}
	}else{
		$jsonData[] = null;
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne les adresses IP de l'utilisateur au format Json
*/
function jsonUserIP(){
	$jsonData = array();

	$arrayUserIP = getUserIP();

	if($arrayUserIP === null){
		$jsonData[][] = null;
	}else{
		$arrayInfoIPv4 = getInfoFromIP($arrayUserIP["IPv4"]);
		$arrayInfoIPv6 = getInfoFromIP($arrayUserIP["IPv6"]);

		// On teste que l'adresse IPv4 existe
		if($arrayUserIP["IPv4"] === ""){
			$jsonData["IPv4"][] = null;
		}else{
			$jsonData["IPv4"]["IP"] = $arrayInfoIPv4["query"];
			$jsonData["IPv4"]["Statut"] = $arrayInfoIPv4["status"];
			$jsonData["IPv4"]["Message"] = $arrayInfoIPv4["message"];
			$jsonData["IPv4"]["Continent"] = $arrayInfoIPv4["continent"];
			$jsonData["IPv4"]["Code Continent"] = $arrayInfoIPv4["continentCode"];
			$jsonData["IPv4"]["Pays"] = $arrayInfoIPv4["country"];
			$jsonData["IPv4"]["Code Pays"] = $arrayInfoIPv4["countryCode"];
			$jsonData["IPv4"]["Code Région"] = $arrayInfoIPv4["region"];
			$jsonData["IPv4"]["Région"] = $arrayInfoIPv4["regionName"];
			$jsonData["IPv4"]["Ville"] = $arrayInfoIPv4["city"];
			$jsonData["IPv4"]["District"] = $arrayInfoIPv4["district"];
			$jsonData["IPv4"]["Code postal"] = $arrayInfoIPv4["zip"];
			$jsonData["IPv4"]["Latitude"] = $arrayInfoIPv4["lat"];
			$jsonData["IPv4"]["Longitude"] = $arrayInfoIPv4["lon"];
			$jsonData["IPv4"]["Fuseau horaire"] = $arrayInfoIPv4["timezone"];
			$jsonData["IPv4"]["Monnaie"] = $arrayInfoIPv4["currency"];
			$jsonData["IPv4"]["FAI"] = $arrayInfoIPv4["isp"];
			$jsonData["IPv4"]["Organisation"] = $arrayInfoIPv4["org"];
			$jsonData["IPv4"]["AS"] = $arrayInfoIPv4["as"];
			$jsonData["IPv4"]["Nom AS"] = $arrayInfoIPv4["asname"];
			$jsonData["IPv4"]["DNS Inverse"] = $arrayInfoIPv4["reverse"];
			$jsonData["IPv4"]["Connexion Mobile"] = $arrayInfoIPv4["mobile"];
			$jsonData["IPv4"]["Proxy"] = $arrayInfoIPv4["proxy"];
			$jsonData["IPv4"]["Hébergement"] = $arrayInfoIPv4["hosting"];
		}

		// On teste que l'adresse IPv6 existe
		if($arrayUserIP["IPv6"] === ""){
			$jsonData["IPv6"][] = null;
		}else{
			$jsonData["IPv6"]["IP"] = $arrayInfoIPv6["query"];
			$jsonData["IPv6"]["Statut"] = $arrayInfoIPv6["status"];
			$jsonData["IPv6"]["Message"] = $arrayInfoIPv6["message"];
			$jsonData["IPv6"]["Continent"] = $arrayInfoIPv6["continent"];
			$jsonData["IPv6"]["Code Continent"] = $arrayInfoIPv6["continentCode"];
			$jsonData["IPv6"]["Pays"] = $arrayInfoIPv6["country"];
			$jsonData["IPv6"]["Code Pays"] = $arrayInfoIPv6["countryCode"];
			$jsonData["IPv6"]["Code Région"] = $arrayInfoIPv6["region"];
			$jsonData["IPv6"]["Région"] = $arrayInfoIPv6["regionName"];
			$jsonData["IPv6"]["Ville"] = $arrayInfoIPv6["city"];
			$jsonData["IPv6"]["District"] = $arrayInfoIPv6["district"];
			$jsonData["IPv6"]["Code postal"] = $arrayInfoIPv6["zip"];
			$jsonData["IPv6"]["Latitude"] = $arrayInfoIPv6["lat"];
			$jsonData["IPv6"]["Longitude"] = $arrayInfoIPv6["lon"];
			$jsonData["IPv6"]["Fuseau horaire"] = $arrayInfoIPv6["timezone"];
			$jsonData["IPv6"]["Monnaie"] = $arrayInfoIPv6["currency"];
			$jsonData["IPv6"]["FAI"] = $arrayInfoIPv6["isp"];
			$jsonData["IPv6"]["Organisation"] = $arrayInfoIPv6["org"];
			$jsonData["IPv6"]["AS"] = $arrayInfoIPv6["as"];
			$jsonData["IPv6"]["Nom AS"] = $arrayInfoIPv6["asname"];
			$jsonData["IPv6"]["DNS Inverse"] = $arrayInfoIPv6["reverse"];
			$jsonData["IPv6"]["Connexion Mobile"] = $arrayInfoIPv6["mobile"];
			$jsonData["IPv6"]["Proxy"] = $arrayInfoIPv6["proxy"];
			$jsonData["IPv6"]["Hébergement"] = $arrayInfoIPv6["hosting"];
		}
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne le User Agent du navigateur de l'utilisateur au format Json
*/
function jsonUserAgent(){
	$jsonData = array();

	$jsonData["User Agent"] = getUserAgent();

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne le Whois du nom de domaine
*/
function jsonWhois(string $searchedWebsite){
	$jsonData = array();
	list($hostWithoutSubdomain, $hostWithWWW, $hostWithSubdomain, $hasSubdomain, $hasWWW, $extension) = arrayDomains($searchedWebsite);

	$jsonData["Whois"] = getWhois($hostWithoutSubdomain, $extension);

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne le nom de domaine associé à une adresse IP au format Json
*/
function jsonHostIP(string $ip){
	$jsonData = array();

	if(filter_var($ip, FILTER_VALIDATE_IP)){
		list($ip, $arrayHosts) = getHosts($ip);

		if($arrayHosts === null){
			$jsonData[] = null;
		}else{
			$jsonData["IP"] = $ip;
			$i=0;
			foreach($arrayHosts as $item){
				$jsonData["Host " .$i] = $arrayHosts[$i];
				$i++;
			}
		}
	}else{
		$jsonData[] = null;
	}
	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne le nom d'hôte, le port et la réussite ou non du ping
*/
function jsonPingWithPort(string $host, string $port){
	$jsonData = array();
	$port = intval($port);

	if(filter_var($host, FILTER_VALIDATE_IP) || urlExists(cleanEntry($host))){
		$arrayPing = pingWithPort($host, $port);

		if($arrayPing === null){
			$jsonData[] = null;
		}else{
			$jsonData["host"] = $arrayPing["host"];
			$jsonData["port"] = $arrayPing["port"];
			$jsonData["ping"] = $arrayPing["ping"];
		}
	}else{
		$jsonData[] = null;
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne le nom d'hôte et la réussite ou non du ping
*/
function jsonPingWithoutPort(string $host){
	$jsonData = array();

	if(filter_var($host, FILTER_VALIDATE_IP) || urlExists(cleanEntry($host))){
		$arrayPing = pingWithoutPort($host);

		if($arrayPing === null){
			$jsonData[] = null;
		}else{
			$jsonData["host"] = $arrayPing["host"];
			$jsonData["ping"] = $arrayPing["ping"];
		}
	}else{
		$jsonData[] = null;
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Retourne les informations sur une adresse IP
*/
function jsonInfoIP(string $ip){
	$jsonData = array();
	$arrayInfoIP = getInfoFromIP($ip);

	if(filter_var($ip, FILTER_VALIDATE_IP)){
		$jsonData["IP"] = $arrayInfoIP["query"];
		$jsonData["Statut"] = $arrayInfoIP["status"];
		$jsonData["Message"] = $arrayInfoIP["message"];
		$jsonData["Continent"] = $arrayInfoIP["continent"];
		$jsonData["Code Continent"] = $arrayInfoIP["continentCode"];
		$jsonData["Pays"] = $arrayInfoIP["country"];
		$jsonData["Code Pays"] = $arrayInfoIP["countryCode"];
		$jsonData["Code Région"] = $arrayInfoIP["region"];
		$jsonData["Région"] = $arrayInfoIP["regionName"];
		$jsonData["Ville"] = $arrayInfoIP["city"];
		$jsonData["District"] = $arrayInfoIP["district"];
		$jsonData["Code postal"] = $arrayInfoIP["zip"];
		$jsonData["Latitude"] = $arrayInfoIP["lat"];
		$jsonData["Longitude"] = $arrayInfoIP["lon"];
		$jsonData["Fuseau horaire"] = $arrayInfoIP["timezone"];
		$jsonData["Monnaie"] = $arrayInfoIP["currency"];
		$jsonData["FAI"] = $arrayInfoIP["isp"];
		$jsonData["Organisation"] = $arrayInfoIP["org"];
		$jsonData["AS"] = $arrayInfoIP["as"];
		$jsonData["Nom AS"] = $arrayInfoIP["asname"];
		$jsonData["DNS Inverse"] = $arrayInfoIP["reverse"];
		$jsonData["Connexion Mobile"] = $arrayInfoIP["mobile"];
		$jsonData["Proxy"] = $arrayInfoIP["proxy"];
		$jsonData["Hébergement"] = $arrayInfoIP["hosting"];
	}else{
		$jsonData[] = null;
	}

	echo json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
* Fonction qui sert à traiter les différents cas d'appel de l'API
*/
function appelAPI(){
	if(isset($_GET['SERVER']) && $_GET['SERVER'] != ""){
		$website = $_GET['SERVER'];
		jsonManualCheck($website);
	}else if(isset($_GET['HEADER']) && $_GET['HEADER'] != ""){
		$website = $_GET['HEADER'];
		jsonHeader($website);
	}else if(isset($_GET['META']) && $_GET['META'] != ""){
		$website = $_GET['META'];
		jsonMeta($website);
	}else if(isset($_GET['USERIP']) && $_GET['USERIP'] == ""){
		jsonUserIP();
	}else if(isset($_GET['USERAGENT']) && $_GET['USERAGENT'] == ""){
		jsonUserAgent();
	}else if(isset($_GET['IPINFO']) && $_GET['IPINFO'] != ""){
		$ip = $_GET["IPINFO"];
		jsonInfoIP($ip);
	}else if(isset($_GET['WHOIS']) && $_GET['WHOIS'] != ""){
		$website = $_GET['WHOIS'];
		jsonWhois($website);
	}else if(isset($_GET['HOSTIP']) && $_GET['HOSTIP'] != ""){
		$ip = $_GET['HOSTIP'];
		jsonHostIP($ip);
	}else if(isset($_GET['PINGHOST']) && $_GET['PINGHOST'] != "" && isset($_GET['PINGPORT']) && $_GET['PINGPORT'] != ""){
		$host = $_GET['PINGHOST'];
		$port = $_GET['PINGPORT'];
		jsonPingWithPort($host, $port);
	}else if(isset($_GET['PINGHOST']) && $_GET['PINGHOST'] != ""){
		$host = $_GET['PINGHOST'];
		jsonPingWithoutPort($host);
	}
}

appelAPI();

?>
