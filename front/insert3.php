<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<title>POPUP</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="../css/main.css">

</head>

<body>
	<?php


	include_once "../../../inc/includes.php";
	Session::checkRight('user', UPDATE);

	$plugin = new Plugin();
	if (!$plugin->isActivated('popup')) {
		header("Location: ../../../index.php");
		die();
	}

	global $DB;

	$num = $_GET['num'];

	$num2 = $_GET['num2']; // num en +33 ou autre si �tranger pour le retour a la page dacceuil apr�s enregistrement

	$utilisateur = $_GET['utilisateur'];

	$numamaj = $_GET['numamaj'];

	// prepare enregistrement du num�ro utilisateur dans le numero choisi dans le menu deroulant
	if ($numamaj == 'phone') {
		$request = $DB->prepare('UPDATE glpi_users
											SET phone = \'' . $num . '\'
											WHERE name = \'' . $utilisateur . '\'
											');
	} else if ($numamaj == 'phone2') {
		$request = $DB->prepare('UPDATE glpi_users
											SET phone2 = \'' . $num . '\'
											WHERE name = \'' . $utilisateur . '\'
											');
	} else if ($numamaj == 'mobile') {
		$request = $DB->prepare('UPDATE glpi_users
											SET mobile = \'' . $num . '\'
											WHERE name = \'' . $utilisateur . '\'
											');
	}
	// l'execute
	$request->execute();

	?>

	<div class="container-contact100">
		<div class="wrap-contact100">
			<span class="contact100-form-title"> Enregistrement effectué ! </span>
			<a href="page.php?num=<?php echo $num2 ?>" style="display: block; width: 100%; height: 100%;">Voir les tickets en cours</a>
		</div>
	</div>

</body>


</html>