<?php
/**
* @file db_traitements.php
* @brief Fichier effectuant les traitements préliminaires sur la table MySQL
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

include ('db.php');

global $connexion;
$fileSQL = "./db_traitements.sql"; // Le chemin relatif où se situe le script sql à exécuter

// On créé la table si elle n'existe pas
insertionBDD($connexion, $fileSQL);

/**
* Insertion d'un fichier .sql dans la base de données
* @param $connexion La connexion à la base de données
* @param $myfile Le fichier au format .sql qui doit être inséré
*/
function insertionBDD($connexion, $myfile){
	$start = microtime(true);

	$sqlSource = file_get_contents($myfile);
	mysqli_multi_query($connexion, $sqlSource) or die("Impossible d'exécuter le fichier SQL" . nl2br("\n")); // On exécute le fichier au format .sql

	if(mysqli_error($connexion)){
		die(mysqli_error($connexion));
	}

	$end = microtime(true);

	echo "Création de la table réussie en " . number_format($end-$start,2) . " secondes." . nl2br("\n");
}

?>
