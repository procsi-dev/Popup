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
    <div class="limiter">
        <div class="container-table100">
            <div class="wrap-table100">
                <div class="table100">
                    <table>
                        <caption>Doublons sur des Entitées et Utilisateurs différents</caption>
                        <thead>
                            <tr class="table100-head">
                                <th class="column1">Type</th>
                                <th class="column1.5">Actif</th>
                                <th class="column2">ID</th>
                                <th class="column3">Nom</th>
                                <th class="column4">Numéro</th>
                                <th class="column5">Nombre enregistrement du numero</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            include_once "../../../inc/includes.php";

                            // JD : Verifie que le compte est connecté est a l'autorisation d'acceder à la page
                            Session::checkRight('config', READ);

                            // JD : Verifie que le plugin telephony est active
                            $plugin = new Plugin();
                            if (!$plugin->isActivated('popup')) {
                                header("Location: ../../../index.php");
                                die();
                            }

                            $requeteDoublons = PluginPopupConfig::viewDoublonPage();
                            $requeteResult = $requeteDoublons->get_result();

                            // JD : Recupere le nombre de colone obtenue par la requete
                            $number = $requeteResult->num_rows;

                            // declaration des tableaux
                            $table_type = array();
                            $table_actif = array();
                            $table_id = array();
                            $table_identifiant = array();
                            $table_tel = array();
                            $table_nbtel = array();

                            while ($data = $requeteResult->fetch_object()) {
                                $table_type[] = $data->type;
                                $table_actif[] = $data->actif;
                                $table_id[] = $data->id;
                                $table_identifiant[] = $data->identifiant;
                                $table_tel[] = $data->tel1;
                                $table_nbtel[] = $data->nbenregistrement;
                            }

                            for ($i = 0; $i < $number; $i++) {
                                // url redirection vers la page glpi
                                if ($table_type[$i] == 'User') {
                                    $url[$i] = '../../../front/user.form.php?id=';
                                    if ($table_actif[$i] == 0) { // JD Add if/else
                                        $actif[$i] = 'Non';
                                    } else {
                                        $actif[$i] = 'Oui';
                                    }
                                } else {
                                    $url[$i] = '../../../front/entity.form.php?id=';
                                    $actif[$i] = ''; // JD
                                }
                                // si le num�ro est trouv� plusieurs fois on l'affiche dans la partie doublons differents
                                foreach (array_count_values($table_tel) as $valeur => $occurences) {
                                    if ($valeur == $table_tel[$i] && $occurences > 1) { // JD Ajoute td column1.5
                                        $urlTotal = '"' . $url[$i] . $table_id[$i] . '"';
                                        echo '<tr class="table100-head">
                                                <td class="column1">' . $table_type[$i] . '</td>
                                                <td class="column1.5">' . $actif[$i] . '</td>
                                                <td class="column2"><a href="" onclick=redirectPage(' . $urlTotal . ')>' . $table_id[$i] . '</a></td>
                                                <td class="column3">' . $table_identifiant[$i] . '</td>
                                                <td class="column4">' . $table_tel[$i] . '</td>
                                                <td class="column5">' . $table_nbtel[$i] . '</td>       
                                            </tr>';
                                        if (count($table_tel) != $i + 1 && $table_tel[$i] != $table_tel[$i + 1]) { // JD Ajoute td column1.5
                                            echo '<tr class="table100-head">
                                                    <td class="column1"></td>
                                                    <td class="column1.5"></td>
                                                    <td class="column2"></td>
                                                    <td class="column3"></td>
                                                    <td class="column4"></td>
                                                    <td class="column5"></td>
                                                </tr>';
                                        }
                                    }
                                }
                            }
                            ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="container-table100">
            <div class="wrap-table100">
                <div class="table100">
                    <table>
                        <caption>Doublons sur le m&ecirc;me Utilisateur</caption>
                        <thead>
                            <tr class="table100-head">
                                <th class="column1">Type</th>
                                <th class="column1.5">Actif</th>
                                <th class="column2">ID</th>
                                <th class="column3">Nom</th>
                                <th class="column4">Numéro</th>
                                <th class="column5">Nombre enregistrement du numero</th>
                            </tr>
                        </thead>
                        <?php
                        for ($i = 0; $i < $number; $i++) {
                            // url de redirection sur la page glpi
                            if ($table_type[$i] == 'User') {
                                $url[$i] = '../../../front/user.form.php?id=';
                                if ($table_actif[$i] == 0) { // JD Add if/else
                                    $actif[$i] = 'Non';
                                } else {
                                    $actif[$i] = 'Oui';
                                }
                            } else {
                                $url[$i] = '../../../front/entity.form.php?id=';
                                $actif[$i] = ''; // JD
                            }
                            // si le num�ro appararait qu'une fois on l'affiche dans la partie doublons sur un meme utilisateur
                            foreach (array_count_values($table_tel) as $valeur => $occurences) {
                                if ($valeur == $table_tel[$i] && $occurences < 2) { // JD Ajoute column1.5
                                    echo '<tr class="table100-head">
                                            <td class="column1">' . $table_type[$i] . '</td>
                                            <td class="column1.5">' . $actif[$i] . '</td>
                                            <td class="column2"><a href="' . $url[$i] . $table_id[$i] . '">' . $table_id[$i] . '</a></td>
                                            <td class="column3">' . $table_identifiant[$i] . '</td>
                                            <td class="column4">' . $table_tel[$i] . '</td>
                                            <td class="column5">' . $table_nbtel[$i] . '</td>
                                          </tr>';
                                }
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>