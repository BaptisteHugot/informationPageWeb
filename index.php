<?php

/**
* @file index.php
* @brief Page permettant de récupérer les informations sur les adresses IP utilisées par un domaine
*/

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors',1);
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Récupération d'informations sur une page Internet</title>
	<link rel="StyleSheet" type="text/css" href="style.css">
</head>

<body>
	<!-- Le formulaire qui sera utilisé -->
	<form name="form" method="post" action="index.php" id="form">
		<input type="radio" id="serveur" name="choix" value="serveur" class="radioSelect" required>Serveurs du domaine
		<input type="radio" id="whois" name="choix" value="whois" class="radioSelect" required>Whois du domaine
		<input type="radio" id="http" name="choix" value="http" class="radioSelect" required>Entête HTTP de la page
		<input type="radio" id="meta" name="choix" value="meta" class="radioSelect" required> Balises meta de la page
		<br />

		<input type="url" class="specificField" id="site" name="site" placeholder="URL de la page Internet : " />
		<br />

		<input type="submit" name="submit"></input>
	</form>

</body>
</html>

<?php
if(isset($_POST["choix"]) && $_POST["choix"] != ""){
	$radioValue = $_POST["choix"]; // On récuère la valeur du radio bouton

	if(isset($_POST["site"]) && $_POST["site"] != ""){
		$start = microtime(true); // Début du chronomètre

		$searchedWebsite = $_POST["site"]; // On récupère la valeur du champ texte
		$searchedWebsite = htmlspecialchars($searchedWebsite, ENT_QUOTES, 'UTF-8'); // Pour éviter une injection XSS

		$patternWithHttp = "#(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~\#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~\#?&//=]*))#"; // Pattern utilisé dans le cas où l'utilisateur entre l'URL en commençant par http(s)
		$patternWithoutHttp = "#([-a-zA-Z0-9@:%._\+~\#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~\#?&//=]*))#"; // Pattern utilisé dans le cas où l'utilisateur entre l'URL sans commencer par http(s)

		if(preg_match($patternWithHttp,$searchedWebsite) || preg_match($patternWithoutHttp,$searchedWebsite)){ // On vérifie que le site entré est au bon format

			$host = parse_url($searchedWebsite, PHP_URL_HOST);

			$hasSubdomain = FALSE; // Booléen permettant de savoir si une URL a un sous-domaine ou non

			if(substr_count($host,".") === 2){ // L'URL a un sous-domaine
				$hostWithSubdomain = $host;
				list($scheme, $domain, $extension) = explode(".",$host); // On récupère le sous-domaine, le domaine et l'extension
				$hostWithoutSubdomain = $domain . "." . $extension;
				$hasSubdomain = TRUE;
			}else if(substr_count($host,".") === 1){ // L'URL n'a pas de sous-domaine
				$hostWithSubdomain = "";
				list($domain, $extension) = explode(".",$host); // On récupère le sous-domaine, le domaine et l'extension
				$hostWithoutSubdomain = $domain . "." . $extension;
			}

			echo "<br />Page recherchée : <b>" . $searchedWebsite . "</b><br /><br />";

			if($radioValue === "serveur"){ // On récupère les informations sur les adresses IP
				// On récupère les informations pour le domaine et le sous-domaine
				if($hasSubdomain === TRUE){
					$resultWithSubdomain = dns_get_record($hostWithSubdomain, DNS_ALL);
				}

				$resultWithoutSubdomain = dns_get_record($hostWithoutSubdomain, DNS_ALL);

				// On créé les tableaux où seront stockés les éléments retournés
				$arrayWebsiteIPv4WithoutSubdomain = array();
				$arrayWebsiteIPv6WithoutSubdomain = array();
				$arrayWebsiteIPv4WithSubdomain = array();
				$arrayWebsiteIPv6WithSubdomain = array();
				$arrayMXServers = array();
				$arrayNSServers = array();
				$arraySOAMname = array();
				$arraySOARname = array();
				$arrayCAAServers = array();

				// On récupère les informations liées au sous-domaine
				if($hasSubdomain === TRUE){
					$i=0;
					foreach($resultWithSubdomain as $item){
						switch($resultWithSubdomain[$i]["type"]){
							case "AAAA": // Adresse IPv6
							$ipv6 = $resultWithSubdomain[$i]["ipv6"];
							$index = array_search($ipv6,$arrayWebsiteIPv6WithSubdomain);
							if($index === FALSE){ // Permet de ne pas avoir de doublons dans le tableau
								array_push($arrayWebsiteIPv6WithSubdomain,$ipv6);
							}
							break;
							case "A": // Adresse IPv4
							$ipv4 = $resultWithSubdomain[$i]["ip"];
							$index = array_search($ipv4,$arrayWebsiteIPv4WithSubdomain);
							if($index === FALSE){
								array_push($arrayWebsiteIPv4WithSubdomain,$ipv4);
							}
							break;
						}
						$i++;
					}
				}

				// On récupère les informations liées au domaine
				$i=0;
				foreach($resultWithoutSubdomain as $item){
					switch($resultWithoutSubdomain[$i]["type"]){
						case "AAAA": // Adresse IPv6
						$ipv6 = $resultWithoutSubdomain[$i]["ipv6"];
						$index = array_search($ipv6,$arrayWebsiteIPv6WithoutSubdomain);
						if($index === FALSE){ // Permet de ne pas avoir de doublons dans le tableau
							array_push($arrayWebsiteIPv6WithoutSubdomain,$ipv6);
						}
						break;
						case "A": // Adresse IPv4
						$ipv4 = $resultWithoutSubdomain[$i]["ip"];
						$index = array_search($ipv4,$arrayWebsiteIPv4WithoutSubdomain);
						if($index === FALSE){
							array_push($arrayWebsiteIPv4WithoutSubdomain,$ipv4);
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

				// On récupère les informations liées au serveur MX du domaine
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
							$index = array_search($ipv6,$arrayMXServersIPv6);
							if($index === FALSE){
								array_push($arrayMXServersIPv6,$ipv6);
							}
							break;
							case "A":
							$ipv4 = $ipInfo[$i]["ip"];
							$index = array_search($ipv4,$arrayMXServersIPv4);
							if($index === FALSE){
								array_push($arrayMXServersIPv4,$ipv4);
							}
							break;
						}
						$i++;
					}
					$j++;
				}

				// On récupère les informations liées au serveur NS du domaine
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
							$index = array_search($ipv6,$arrayNSServersIPv6);
							if($index === FALSE){
								array_push($arrayNSServersIPv6,$ipv6);
							}
							break;
							case "A":
							$ipv4 = $ipInfo[$i]["ip"];
							$index = array_search($ipv4,$arrayNSServersIPv4);
							if($index === FALSE){
								array_push($arrayNSServersIPv4,$ipv4);
							}
							break;
						}
						$i++;
					}
					$j++;
				}

				// On affiche l'ensemble des informations
				echo "<table>";
				echo "<tr><td></td><td><b>Informations</b></td><td>IPv4</td><td><b>IPv6</b></td></tr>";

				if(empty($arrayWebsiteIPv6WithoutSubdomain) && empty($arrayWebsiteIPv4WithoutSubdomain)){
					echo "<tr><td>Domaine</td><td>" . $hostWithoutSubdomain . "</td><td>" . implode("<br />",$arrayWebsiteIPv4WithoutSubdomain) . "</td><td>" . implode("<br />",$arrayWebsiteIPv6WithoutSubdomain) . "</td></tr>";
				}else{
					if(empty($arrayWebsiteIPv6WithoutSubdomain)){
						echo "<tr><td style='background-color:red'>Domaine</td><td style='background-color:red'>" . $hostWithoutSubdomain . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv4WithoutSubdomain) . "</td><td style='background-color:red'>" . implode("<br />",$arrayWebsiteIPv6WithoutSubdomain) . "</td></tr>";
					}else{
						echo "<tr><td style='background-color:palegreen'>Domaine</td><td style='background-color:palegreen'>" . $hostWithoutSubdomain . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv4WithoutSubdomain) . "</td><td style='background-color:palegreen'>" . implode("<br />",$arrayWebsiteIPv6WithoutSubdomain) . "</td></tr>";
					}
				}

				if($hasSubdomain === TRUE){
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

				$end = microtime(true); // Fin du chronomètre
			} else if($radioValue === "whois"){ // On récupère les informations du Whois
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

				// On affiche l'ensemble des informations
				echo "<table>";
				$i=0;
				echo "<tr><td><b>Valeur</b></td></tr>";
				foreach($whois as $item){
					if($whois[$i ]=== "\r\n" || $whois[$i] === "\n" || $whois[$i] === ""){
						echo "<tr><td style='background-color: blue'></td>";
						$i++;
					}else{
						echo "<tr><td>" . $whois[$i] . "</td></tr>";
						$i++;
					}
				}
				echo "</table>";

				$end = microtime(true); // Fin du chronomètre
			}else if($radioValue === "http"){ // On récupère les entêtes HTTP
				$httpHeaders = array();
				$httpHeaders = get_headers($searchedWebsite);

				$keys = array_keys($httpHeaders);
				$values = array_values($httpHeaders);

				// On affiche l'ensemble des informations
				echo "<table>";
				$i=0;
				echo "<tr><td><b>Entête</b></td><td><b>Valeur</b></td></tr>";
				foreach($httpHeaders as $item){
					echo "<tr><td>" . strtok($values[$i],":") . "</td><td>" . strtok(":") . "</td></tr>";
					$i++;
				}
				echo "</table>";

				$end = microtime(true); // Fin du chronomètre
			}else if($radioValue === "meta"){ // On récupère l'ensemble des balises meta
				$metaTags = array();
				$metaTags = get_meta_tags($searchedWebsite);

				// On affiche l'ensemble des informations
				$keys = array_keys($metaTags);
				$values = array_values($metaTags);

				echo "<table>";
				$i=0;
				echo "<tr><td><b>Balise</b></td><td><b>Valeur</b></td></tr>";
				foreach($metaTags as $item){
					echo "<tr><td>" . $keys[$i] . "</td><td>" . $values[$i] . "</td></tr>";
					$i++;
				}
				echo "</table>";

				$end = microtime(true); // Fin du chronomètre
			}
			echo "<br /><i>Page exécutée en " . number_format($end-$start,2) . " secondes.";
		}else{
			echo "Vous n'avez pas entré un site Internet au bon format.";
		}
	}
}
?>
