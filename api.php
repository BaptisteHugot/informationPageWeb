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
		if($httpHeaders === NULL){
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

		if($metaTags === NULL){
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

	$jsonData["IPv4"] = $arrayUserIP["IPv4"];
	$jsonData["IPv6"] = $arrayUserIP["IPv6"];

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
	}
}

appelAPI();

?>