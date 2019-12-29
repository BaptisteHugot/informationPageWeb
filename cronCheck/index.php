<?php

/**
* @file index.php
* @brief Fonctions utilisées pour revérifier les sites dont les données sont vieilles de plus d'une semaine
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */

?>

<style>
<?php include './../style.css'; ?>
</style>

<?php

include("./cronFunctions.php");

/*
* Permet au cronjob d'exécuter la fonction "automaticCheck" si l'utilisateur appelle l'élément "automaticCheck" en argument de la ligne de commande
*/
if(!empty($argv[1])){
	switch($argv[1]){
		case "automaticCheck":
		$start = microtime(true);
		automaticCheck();
		mysqli_close($connexion); // On ferme la connexion à la base de données
		$end = microtime(true);
		displayExecutionTime($start, $end);
		break;
	}
}
?>
