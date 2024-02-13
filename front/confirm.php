<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>POPUP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <script src="../js/newWindow.js"></script>
</head>

<body>
    <?php
    include_once "../../../inc/includes.php";
    Session::checkCentralAccess();
    global $DB;

    $plugin = new Plugin();
    if (!$plugin->isActivated('popup')) { 
        header("Location: ../../../index.php");
        die();
    }

    $num = $_GET['num'] ?? "nullNum";
    $num2 = $_GET['num2'] ?? "nullNum2";
    $numExiste = $_GET['numExiste'] ?? "nullNumExiste";
    $entityNum = $_GET['entities'] ?? $_GET['utilisateur'] ?? "nullEntity";
    $type = $_GET['type'] ?? "nullType";
    $numSelect = $_GET['numamaj'] ?? "nullNumSelect";
    $numSelectQuery = "";


    if ($type == "standard"){
        $numSelect = "n° de standard";
        $testTelephoneVide = $DB->prepare('SELECT phonenumber FROM glpi_entities WHERE name LIKE \'' . $entityNum . '\' AND phonenumber IS NOT NULL');
        $testTelephoneVide->execute();

        $resultTest = $testTelephoneVide->get_result();

        if($resultTest->num_rows == 0){
            header("Location: insert.php?num2=".$num2."&entities=".$_GET['entities']."&type=".$type."&num=".$num);
            die();
        }else{
            $entityNum = "L'entité ".$entityNum;
            $numExiste = $resultTest->fetch_array()[0];
        }
    }else{
        $numSelectQuery = NULL;
        if ($numSelect == "mobile"){
            $numSelect = "n° de mobile";
            $numSelectQuery = "mobile";
        }else if ($numSelect == "phone"){
            $numSelect = "n° de téléphone 1";
            $numSelectQuery = "phone";
        }else if ($numSelect == "phone2"){
            $numSelect = "n° de téléphone 2";
            $numSelectQuery = "phone2";
        }
        $testTelephoneVide = $DB->prepare('SELECT '. $numSelectQuery.' FROM glpi_users WHERE name LIKE \'' . $entityNum . '\' AND \''. $numSelectQuery.'\' IS NOT NULL');
        $testTelephoneVide->execute();

        $resultTest = $testTelephoneVide->get_result();
        if($resultTest->num_rows == 0 || $resultTest->$numSelectQuery == ""){
            header("Location: insert3.php?utilisateur=".$_GET['utilisateur']."&num2=".$num2."&numamaj=".$numSelectQuery."&num=".$num);
            die();
        }else{
            $entityNum = "L'utilisateur ".$entityNum;
            $numExiste = $resultTest->fetch_array()[0];
        }
    }
    //

    if (isset($_GET['confirm'])){
        if ($type === 'standard'){
            header('Location: insert.php?num2='.$_GET['num2'].'&entities='.$_GET['entities'].'&type='.$_GET['type'].'&num='.$_GET['num']);
        }else{
            header('Location: insert3.php?utilisateur='.$_GET['utilisateur'].'&num2='.$_GET['num2'].'&numamaj='.$_GET['numamaj'].'&num='.$_GET['num']);
        }
    }

    // affiche le formulaire d'enregistrement du num�ro
    ?>

    <div class="container-contact100">
        <div class="wrap-contact100">
            <form class="contact100-form validate-form" method="get" id="formPagePopupConfirm">
                <!-- 				garder le num en +33 ou autre si etranger pour pouvoir revenir a la page d'acceuil apres enregistrement -->
                <input type="hidden" name="num2" value="<?php echo $_GET['num2']??'null' ?>">
                <input type="hidden" name="utilisateur" value="<?php echo $_GET['utilisateur']??'null'?>">
                <input type="hidden" name="numamaj" value="<?php echo $_GET['numamaj']??'null' ?>">
                <input type="hidden" name="confirm" value="">
                <input type="hidden" name="type" value="<?php echo $_GET['type']??'null' ?>">
                <input type="hidden" name="entities" value="<?php echo $_GET['entities']??'null' ?>">
                <span class="contact100-form-title"><?php echo $entityNum ?> possède déjà comme <?php echo $numSelect ?> le <?php echo $numExiste ?></span>
                <span class="contact100-form-title">Voulez-vous le remplacer par le numéro <?php echo $num ?> ?</span>
                <div class="wrap-input100 validate-input">
                </div>

                <div class="container-contact100-form-btn">
                    <div class="wrap-contact100-form-btn">
                        <div class="contact100-form-bgbtn"></div>
                        <button class="contact100-form-btn" type="text" name="num" value="<?php echo $num ?>" onclick="return inputBoutonConfirm(<?php $type ?>)">
                            <span> Remplacer par <?php echo $num ?><i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>