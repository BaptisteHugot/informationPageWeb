<?php

/**
* @file data.php
* @brief Fichier qui récupère les données à afficher dans les graphiques
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

header('Content-Type: application/json');

include "./../db.php";

global $connexion;

if(isset($_POST["action"]) && $_POST["action"] != ""){
	$actionValue = $_POST["action"];

	if ($actionValue === "nbSitesParEtoile"){ // Nombre de domaines par "étoile" IPv6
		$stmt = $connexion->prepare("SELECT resultIPv6 AS resultIPv6, COUNT(domain) AS nbDomain FROM websitesIPv6 GROUP BY resultIPv6 ORDER BY resultIPv6 DESC"); // Requête SQL à exécuter
		$stmt->execute();

		$result = $stmt->get_result();

		if($result === false){ // On arrête le programme si l'exécution de la requête a rencontré un problème
			mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
			mysqli_close($connexion); // On ferme la connexion à la base de données
			throw new Exception(mysqli_error($connexion));
		}else if(mysqli_num_rows($result) > 0){ // Si au moins un élément est trouvé
			while($array = mysqli_fetch_assoc($result)){ // On stocke chaque ligne de la base de données dans une ligne d'un tableau PHP
				$data[] = $array;
			}

			echo stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));

			mysqli_free_result($result);
			mysqli_close($connexion);
		}
		else { // On retourne null si aucun élément n'est trouvé
			$jsonData[] = null;
			echo json_encode($jsonData);
			mysqli_free_result($result); // On libère la variable utilisée pour récupérer le résultat de la requête SQL
			mysqli_close($connexion); // On ferme la connexion à la base de données
		}
	}else if($actionValue === "nbSitesTestes30Jours"){ // Nombre de sites testés lors des 30 derniers jours
		$stmt = $connexion->prepare("SELECT DATE_FORMAT(dateTestsPerDay,'%d/%m/%Y') AS dateFormat, SUM(numberTestsPerDay) AS nbTests FROM numberOfTestsPerDay WHERE dateTestsPerDay > DATE_SUB(now(), INTERVAL 31 DAY) GROUP BY DATE_FORMAT(dateTestsPerDay,'%d/%m/%Y') ORDER BY DATE_FORMAT(dateTestsPerDay,'%d/%m/%Y') ASC LIMIT 30");
		$stmt->execute();

		$result = $stmt->get_result();

		if($result === false){
			mysqli_free_result($result);
			mysqli_close($connexion);
			throw new Exception(mysqli_error($connexion));
		}else if(mysqli_num_rows($result) > 0){
			while($array = mysqli_fetch_assoc($result)){
				$data[] = $array;
			}

			echo stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));

			mysqli_free_result($result);
			mysqli_close($connexion);
		}
		else {
			$jsonData[] = null;
			echo json_encode($jsonData);
			mysqli_free_result($result);
			mysqli_close($connexion);
		}
	}else if($actionValue === "nbSitesTestesMois"){ // Nombre de sites testés par mois

		$stmt = $connexion->prepare("SELECT DATE_FORMAT(dateTestsPerDay, '%m/%Y') AS dateFormat, SUM(numberTestsPerDay) AS nbTests FROM numberOfTestsPerDay GROUP BY DATE_FORMAT(dateTestsPerDay, '%m/%Y') ORDER BY DATE_FORMAT(dateTestsPerDay, '%m/%Y') ASC");
		$stmt->execute();

		$result = $stmt->get_result();

		if($result === false){
			mysqli_free_result($result);
			mysqli_close($connexion);
			throw new Exception(mysqli_error($connexion));
		}else if(mysqli_num_rows($result) > 0){
			while($array = mysqli_fetch_assoc($result)){
				$data[] = $array;
			}

			echo stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));

			mysqli_free_result($result);
			mysqli_close($connexion);
		}
		else {
			$jsonData[] = null;
			echo json_encode($jsonData);
			mysqli_free_result($result);
			mysqli_close($connexion);
		}
	}else if($actionValue === "nbSitesTestesAn"){ // Nombre de sites testés par an

		$stmt = $connexion->prepare("SELECT DATE_FORMAT(dateTestsPerDay, '%Y') AS dateFormat, SUM(numberTestsPerDay) AS nbTests FROM numberOfTestsPerDay GROUP BY DATE_FORMAT(dateTestsPerDay, '%Y') ORDER BY DATE_FORMAT(dateTestsPerDay, '%Y') ASC");
		$stmt->execute();

		$result = $stmt->get_result();

		if($result === false){
			mysqli_free_result($result);
			mysqli_close($connexion);
			throw new Exception(mysqli_error($connexion));
		}else if(mysqli_num_rows($result) > 0){
			while($array = mysqli_fetch_assoc($result)){
				$data[] = $array;
			}

			echo stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));

			mysqli_free_result($result);
			mysqli_close($connexion);
		}
		else {
			$jsonData[] = null;
			echo json_encode($jsonData);
			mysqli_free_result($result);
			mysqli_close($connexion);
		}
	}else if($actionValue === "nbSitesTestesMoisCumul"){ // Nombre de sites testés par mois

		$stmt = $connexion->prepare("WITH data AS (SELECT DATE_FORMAT(dateTestsPerDay, '%m/%Y') AS dateFormat, SUM(numberTestsPerDay) AS nbTestsTemp FROM numberOfTestsPerDay GROUP BY DATE_FORMAT(dateTestsPerDay, '%m/%Y')) SELECT dateFormat, SUM(nbTestsTemp) OVER (ORDER BY dateFormat ASC) AS nbTests FROM data");
		$stmt->execute();

		$result = $stmt->get_result();

		if($result === false){
			mysqli_free_result($result);
			mysqli_close($connexion);
			throw new Exception(mysqli_error($connexion));
		}else if(mysqli_num_rows($result) > 0){
			while($array = mysqli_fetch_assoc($result)){
				$data[] = $array;
			}

			echo stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));

			mysqli_free_result($result);
			mysqli_close($connexion);
		}
		else {
			$jsonData[] = null;
			echo json_encode($jsonData);
			mysqli_free_result($result);
			mysqli_close($connexion);
		}
	}else if($actionValue === "nbSitesTestesAnCumul"){ // Nombre de sites testés par an

		$stmt = $connexion->prepare("WITH data AS (SELECT DATE_FORMAT(dateTestsPerDay, '%Y') AS dateFormat, SUM(numberTestsPerDay) AS nbTestsTemp FROM numberOfTestsPerDay GROUP BY DATE_FORMAT(dateTestsPerDay, '%Y')) SELECT dateFormat, SUM(nbTestsTemp) OVER (ORDER BY dateFormat ASC) AS nbTests FROM data");
		$stmt->execute();

		$result = $stmt->get_result();

		if($result === false){
			mysqli_free_result($result);
			mysqli_close($connexion);
			throw new Exception(mysqli_error($connexion));
		}else if(mysqli_num_rows($result) > 0){
			while($array = mysqli_fetch_assoc($result)){
				$data[] = $array;
			}

			echo stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));

			mysqli_free_result($result);
			mysqli_close($connexion);
		}
		else {
			$jsonData[] = null;
			echo json_encode($jsonData);
			mysqli_free_result($result);
			mysqli_close($connexion);
		}
	}
}
?>
