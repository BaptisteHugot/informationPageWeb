<?php
/**
* @file db.php
* @brief Connexion à la base de donnée
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débogage, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débogage, à supprimer en production */

$config = parse_ini_file("db.ini");
$connexion = mysqli_connect($config["host"],$config["username"],$config["password"],$config["database"]);

if(mysqli_connect_errno()){
	echo "Impossible de se connecter à MySQL : " . mysqli_connect_error();
	die();
} else mysqli_set_charset($connexion, "utf8"); // On force le typage des données en UTF-8 pour le résultat JSON futur

?>
