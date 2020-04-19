<?php

/**
* @file displayFunctions.php
* @brief Fonctions utilisées pour afficher l'ensemble des informations liées à une page Internet
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

include("./../basicFunctions.php");

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
	if($whois === null){
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
	if($httpHeaders === null){
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
	if($metaTags === null){
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
* Affiche le serveur correspondant à une adresse IP
* @param $ip L'adresse IP
* @param $arrayHosts Tableau comprenant les adresses des serveurs
*/
function displayHostsIP(string $ip, array $arrayHosts){
	if($arrayHosts === null){
		echo "Pas de serveurs trouvés. <br />";
	}else{
		echo "<table>";
		$i=0;
		echo "<tr><td><b>Adresse IP</b></td><td><b>Hôtes</b></td></tr>";
		foreach($arrayHosts as $item){
			echo "<tr><td>" . $ip . "</td><td>" . $arrayHosts[$i] . "</td></tr>";
			$i++;
		}
		echo "</table>";
	}
}

/**
* Affiche le résultat du ping
* @param $arrayPing Le tableau comprenant l'hôte et le résultat du ping
*/
function displayPing(array $arrayPing){
	echo "<table>";
	if(!($arrayPing["ping"])){
		echo "<tr><td style='background-color:red'><b>Hôte</b></td><td style='background-color:red'>" . $arrayPing["host"] . "</td></tr>";
	}else{
		echo "<tr><td style='background-color:palegreen'><b>Hôte</b></td><td style='background-color:palegreen'>" . $arrayPing["host"] . "</td></tr>";
	}
	echo "</table>";
}

/**
* Affiche le résultat du ping
* @param $arrayPing Le tableau comprenant l'hôte, le port et le résultat du ping
*/
function displayPingPort(array $arrayPing){
	echo "<table>";
	if(!($arrayPing["ping"])){
		echo "<tr><td style='background-color:red'><b>Hôte</b></td><td style='background-color:red'>" . $arrayPing["host"] . "</td></tr>";
		echo "<tr><td style='background-color:red'><b>Port</b></td><td style='background-color:red'>" . $arrayPing["port"] . "</td></tr>";
	}else{
		echo "<tr><td style='background-color:palegreen'><b>Hôte</b></td><td style='background-color:palegreen'>" . $arrayPing["host"] . "</td></tr>";
		echo "<tr><td style='background-color:palegreen'><b>Port</b></td><td style='background-color:palegreen'>" . $arrayPing["port"] . "</td></tr>";
	}
	echo "</table>";
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
