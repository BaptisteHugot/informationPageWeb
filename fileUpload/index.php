<?php

/**
* @file index.php
* @brief Fonctions permettant de vérifier manuellement une liste de sites disponibles dans un fichier .csv
* @warning Attention, pour des raisons évidentes de sécurité, ce fichier ne doit pas être accessible à tous les utilisateurs !
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

include("./fileFunctions.php");

// On exécute les fonctions présentes dans le fichier
$start = microtime(true);
$urlFile = "testFile.csv";
$fileWebsites = array();
$fileWebsites = readUserFile($urlFile);
automaticTreatment($fileWebsites);
mysqli_close($connexion); // On ferme la connexion à la base de données
$end = microtime(true);
displayExecutionTime($start, $end);

?>
