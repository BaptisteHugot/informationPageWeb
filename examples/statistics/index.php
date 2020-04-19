<?php
/**
* @file index.php
* @brief Affichage de statistiques d'utilisation
*/

declare(strict_types = 1); // On définit le mode strict

/* Code utilisé uniquement pour le débug, à supprimer en production */
error_reporting(E_ALL);
ini_set('display_errors','1');
/* Fin du code utilisé uniquement pour le débug, à supprimer en production */
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Statistiques d'utilisation</title>
	<link rel="StyleSheet" type="text/css" href="style.css">
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-zoom/0.6.6/chartjs-plugin-zoom.min.js"></script>
	<script type="text/javascript" src="./graph.js"></script>
</head>

<body>
	<!-- Le formulaire qui sera utilisé -->
	<form name="form" method="post" action="index.php" id="form">
		<input type="radio" id="nbSitesParEtoile" name="choix" value="nbSitesParEtoile" class="radioSelect" required>Nombre de sites par leur nombre d'étoiles
		<input type="radio" id="nbSitesTestes30Jours" name="choix" value="nbSitesTestes30Jours" class="radioSelect" required>Nombre de sites testés les 30 derniers jours
		<input type="radio" id="nbSitesTestesMois" name="choix" value="nbSitesTestesMois" class="radioSelect" required>Nombre de sites testés par mois
		<input type="radio" id="nbSitesTestesAn" name="choix" value="nbSitesTestesAn" class="radioSelect" required>Nombre de sites testés par an
		<input type="radio" id="nbSitesTestesMoisCumul" name="choix" value="nbSitesTestesMoisCumul" class="radioSelect" required> Nombre de sites testés par mois en cumulé
		<input type="radio" id="nbSitesTestesAnCumul" name="choix" value="nbSitesTestesAnCumul" class="radioSelect" required> Nombre de sites testés par an en cumulé
		<br />
		<input type="submit" name="submit"></input>
	</form>

	<?php
	if(isset($_POST["choix"]) && $_POST["choix"] != ""){
		$radioValue = $_POST["choix"]; // On récupère la valeur du bouton radio
		if($radioValue === "nbSitesParEtoile"){
			echo "
			<script>
			showGraphNbSitesParEtoile();
			</script>
			<div id='chart-container'>
			<canvas id='graphCanvasNbSitesParEtoile'></canvas>
			</div>";
		}else if($radioValue === "nbSitesTestes30Jours"){
			echo "
			<script>
			showGraphNbSitesTestes30Jours();
			</script>
			<div id='chart-container'>
			<canvas id='graphCanvasNbSitesTestes30Jours'></canvas>
			</div>";
		}else if($radioValue === "nbSitesTestesMois"){
			echo "
			<script>
			showGraphNbSitesTestesMois();
			</script>
			<div id='chart-container'>
			<canvas id='graphCanvasNbSitesTestesMois'></canvas>
			</div>";
		}else if($radioValue === "nbSitesTestesAn"){
			echo "
			<script>
			showGraphNbSitesTestesAn();
			</script>
			<div id='chart-container'>
			<canvas id='graphCanvasNbSitesTestesAn'></canvas>
			</div>";
		}else if($radioValue === "nbSitesTestesMoisCumul"){
			echo "
			<script>
			showGraphNbSitesTestesMoisCumul();
			</script>
			<div id='chart-container'>
			<canvas id='graphCanvasNbSitesTestesMoisCumul'></canvas>
			</div>";
		}else if($radioValue === "nbSitesTestesAnCumul"){
			echo "
			<script>
			showGraphNbSitesTestesAnCumul();
			</script>
			<div id='chart-container'>
			<canvas id='graphCanvasNbSitesTestesAnCumul'></canvas>
			</div>";
		}
	}
	?>

	<div id = "tableau"></div>

</body>
</html>
