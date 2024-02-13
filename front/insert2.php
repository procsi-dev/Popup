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

	// cherche les numeros enregistr�s de l'utilisateur
	$request = $DB->prepare('SELECT phone, phone2, mobile FROM glpi_users WHERE name = \'' . $utilisateur . '\' ');


	// l'execute
	$request->execute();
	$data = $request->get_result()->fetch_object();

	// stoke les valeurs des diff�rents t�l�phones
	$phone = $data->phone;
	$phone2 = $data->phone2;
	$mobile = $data->mobile;

	// Affiche les num�ros enregistrer de l'utilisateur et demande le num�ro � mettre a jour
	?>

	<div class="container-contact100">
		<div class="wrap-contact100">

			<form class="contact100-form validate-form" action="confirm.php" method="get">
				<input type="hidden" name="utilisateur" value="<?php echo $utilisateur ?>">
				<!-- 				garder le num en +33 ou autre si etranger pour pouvoir revenir a la page d'acceuil apres enregistrement -->
				<input type="hidden" name="num2" value="<?php echo $num2 ?>"> <span class="contact100-form-title">
					<p><?php echo $num; ?> </p>
					Numéros disponibles pour <?php echo $utilisateur; ?>
				</span>



				<div class="wrap-input100 validate-input">

					<p>Téléphone 1 : <?php echo $phone; ?> </p>
					<p>Téléphone 2 :<?php echo $phone2; ?> </p>
					<p>Mobile : <?php echo $mobile; ?> </p>
					<span class="label-input100">choix du numéro &agrave; mettre
						&agrave; jour :</span> <SELECT name="numamaj" size="1">
						<OPTION value="0">--Choisir Numéro--</OPTION>
						<OPTION value="phone">Téléphone 1</OPTION>
						<OPTION value="phone2">Téléphone 2</OPTION>
						<OPTION value="mobile">Mobile</OPTION>
					</SELECT> <span class="focus-input100"></span>
				</div>

				<div class="container-contact100-form-btn">
					<div class="wrap-contact100-form-btn">
						<div class="contact100-form-bgbtn"></div>
						<button class="contact100-form-btn" type="text" name="num" value="<?php echo $num; ?>">
							<span> Ajouter le numéro! <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
							</span>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>

</body>


</html>