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

	$entities = $_GET['entities'];

	$type = $_GET['type'];

	$num2 = $_GET['num2']; // num en +33 ou autre si �tranger pour le retour a la page dacceuil apr�s enregistrement


	// si le numeror est un standard chercher l'id de l'entit� puis mets a jour son num�ro
	if ($type == 'standard') {

		$request = $DB->prepare('SELECT id FROM glpi_entities
											WHERE name = \'' . $entities . '\'
											');

		// l'execute
		$request->execute();
		$data = $request->get_result()->fetch_object();

		$id = $data->id;

		$request = $DB->prepare('UPDATE glpi_entities
											SET phonenumber = \'' . $num . '\'
											WHERE id = \'' . $id . '\'
											');

		// l'execute
		$request->execute();

	?>

		<div class="container-contact100">
			<div class="wrap-contact100">
				<span class="contact100-form-title"> Enregistrement effectué ! </span>
				<a href="page.php?num=<?php echo $num2 ?>" style="display: block; width: 100%; height: 100%;">Voir les tickets
					en cours</a>
			</div>
		</div>



	<?php
		// si le numero est un utilisateur charge la liste des utilisateurs de l'entit� 
	} else if ($type == 'utilisateur') {
	?> 
		<div class="container-contact100">
			<div class="wrap-contact100">
				<!-- 			FIXME: entrer la bonne url dans la href chemin vers insert2.php -->
				<form class="contact100-form validate-form" action="insert2.php" method="get">
					<!-- 				garder le num en +33 ou autre si etranger pour pouvoir revenir a la page d'acceuil apres enregistrement -->
					<input type="hidden" name="num2" value="<?php echo $num2 ?>"> <span class="contact100-form-title"> Sélectionner Utilisateur </span>
					<div class="wrap-input100 validate-input">
						<span class="label-input100">Utilisateur</span> <SELECT name="utilisateur" size="1">
							<OPTION value="0">--Choisir Utilisateur--
								<?php
								$requeteUtilisateur = "SELECT u.* FROM glpi_users u, glpi_entities e WHERE u.entities_id = e.id AND e.name = '{$entities}' order by u.name";

								$resultat2 = $DB->prepare($requeteUtilisateur);
								$resultat2->execute();

								if (!$resultat2) {
									echo "Probl�me de requete";
								} else {
									$ligneresult2 = $resultat2->get_result();
									while ($ligne = $ligneresult2->fetch_object()) {
										echo "<option value='" . $ligne->name . "'>" . $ligne->firstname . ' ' . $ligne->realname . ' ' . $ligne->name . "</option>";
									}
								}

								?>



						</SELECT> <span class="focus-input100"></span>
					</div>

					<div class="container-contact100-form-btn">
						<div class="wrap-contact100-form-btn">
							<div class="contact100-form-bgbtn"></div>
							<button class="contact100-form-btn" type="text" name="num" value="<?php echo $num ?>">
								<span> Voir les numéros! <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
								</span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>


	<?php
	}
	?>


</body>


</html>