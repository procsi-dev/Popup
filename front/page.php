<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>POPUP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="../css/main.css">
    <script src="../js/newWindow.js"></script>
    <script>
        window.resizeBy(-1,-1);
        window.addEventListener("resize", (event) => {
            if(window.innerWidth < 750){
                if (window.innerWidth < 730){
                    window.resizeBy(10,0);
                }else {
                    window.resizeBy(1,0);
                }

            }
            else if(window.innerHeight < 550){
                if (window.innerHeight < 530){
                    window.resizeBy(0,10);
                }else {
                    window.resizeBy(0,1);
                }
            }
        });
        window.resizeBy(1,1);
    </script>
</head>

<body>

    <?php
    include_once "../../../inc/includes.php";
    Session::checkCentralAccess();

    $plugin = new Plugin();
    if (!$plugin->isActivated('popup')) {
        header("Location: ../../../index.php");
        die();
    }

    $num = $_GET['num'];

    if (str_ends_with($num, '?error=3')){
        $num = rtrim($num,"?error=3");
    }

    // conversion des numeros pour trouv� plusieurs format enregistrer sur GLPI
    // cas num�ro etranger on garde identifiant du pays en +31 +58 ect

    $numConvertir = PluginPopupConfig::convertionNum($num);
    $num = $numConvertir['num'];
    $num2 = $numConvertir['num2'];
    $num3 = $numConvertir['num3'];
    $num4 =$numConvertir['num4'];
    // Fin conversion des num�ros


    // initialise un type pour le numero entrer
    $type = 'users';

    // page de connection a la BDD

    global $DB;
    // recherche le num�ro dans les utilisateurs

    // JD Correction recherche
    $request = $DB->prepare('select u.firstname, u.realname, e.name entities
                                            from glpi_users u, glpi_entities e
                                            where e.id = u.entities_id and (u.phone =  \'' . $num . '\' 
                                            OR u.phone2 = \'' . $num . '\' OR u.mobile = \'' . $num . '\' 
                                            OR u.phone = \'' . $num2 . '\' OR u.phone2 = \'' . $num2 . '\'
                                            OR u.mobile = \'' . $num2 . '\'OR u.phone = \'' . $num3 . '\' 
                                            OR u.phone2 = \'' . $num3 . '\' OR u.mobile = \'' . $num3 . '\'
                                            OR u.phone = \'' . $num4 . '\' OR u.phone2 = \'' . $num4 . '\' 
                                            OR u.mobile = \'' . $num4 . '\')');
    $request->execute();
    $dataResult = $request->get_result();
    // verifier requete n'est pas vide
    $numrow = $dataResult->num_rows;

    // si le numeros est pas trouv� on cherche dans les entit�s
    if ($numrow == 0) {

        $type = 'entities';

        // regarde le numero dans les entites
        $request = $DB->prepare('select name entities 
                                   from glpi_entities 
                                   where phonenumber = \'' . $num . '\' OR phonenumber = \'' . $num2 . '\' 
                                   OR phonenumber = \'' . $num3 . '\' OR phonenumber = \'' . $num4 . '\'');

        $request->execute();
        $dataResult = $request->get_result();
        $numrow = $dataResult->num_rows;
    }

    // si num�ro de tel pas trouve demande pour l'enregistrer

    if ($numrow == 0) {

        // R�cup�re les donn�es de la table entites

        // le prepare et l'execute
        $resultat = $DB->prepare('SELECT * FROM glpi_entities order by name');
        $resultat->execute();

        if (!$resultat) {
            echo "Probl�me de requete";
        } else {

            // affiche le formulaire d'enregistrement du num�ro


    ?>
            <div class="container-contact100">
                <div class="wrap-contact100">
                    <form class="contact100-form validate-form" method="get" id="formPagePopup"> <!-- action="insert.php" method="get" -->
                        <!-- 				garder le num en +33 ou autre si etranger pour pouvoir revenir a la page d'acceuil apres enregistrement -->
                        <input type="hidden" name="num2" value="<?php echo $num2 ?>"> <span id="numInconnu" class="contact100-form-title"> Numéro <?php echo $num ?> Inconnu </span>
                        <div class="wrap-input100 validate-input">
                            <span class="label-input100">Entitée</span>
                            <SELECT name="entities" size="1">
                                <OPTION>--Choisir entitée --
                                    <?php
                                    $ligneresult = $resultat->get_result();
                                    while ($ligne = $ligneresult->fetch_object()) {
                                        echo "<option value='" . $ligne->name . "'>" . $ligne->name . "</option>";
                                    }

                                    ?>
                            </SELECT>

                        <?php
                    } // fin du else
                    //$ligneresult->close(); // lib�re le r�sultat
                        ?>
                        <br> <br> <span class="label-input100">Type</span> <SELECT id="typeEntity" name="type" size="1">

                            <OPTION>--Choisir Type --

                            <OPTION value="standard">Standard</OPTION>

                            <OPTION value="utilisateur">Utilisateur</OPTION>

                        </SELECT> <span class="focus-input100"></span>
                        </div>
                        <div class="container-contact100-form-btn">
                            <div class="wrap-contact100-form-btn">
                                <div class="contact100-form-bgbtn"></div>
                                <button class="contact100-form-btn" type="text" onclick="submitFormPage()" name="num" value="<?php echo $num ?>">
                                    <span> Ajouter ! <i class="fa fa-long-arrow-right m-l-7" aria-hidden="true"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php
    }

    // si le num�ro est trouv� on affiche le nom, prenom et entite

    //prepare les tableaux
    $table_realname = array();
    $table_firstname = array();

    $table_entities_id = array();


    $data = $dataResult->fetch_object();

    if ($type == 'users') {
        // si type est un user affiche pr�nom et nom
        $table_realname[] = $data->realname;
        $table_firstname[] = $data->firstname;
    } else {
        // si type est entities on affiche rien dans nom prenom
        $table_realname[0] = ' ';
        $table_firstname[0] = ' ';
    }

    // on affiche l'entitie dans tous les cas
    $table_entities_id[] = $data->entities??null;


    // Affichage utilisateur et entit�e

    /*echo '<div class="limiter">
            <div class="container-table100">
                <div class="wrap-table100">
                    <div class="table100">
                        <table>
                                        <tr>
                                            <th class="column1">Nom</th>
                                            <th class="column2">Prenom</th>
                                            <th class="column3"></th>
                                            <th class="column4"></th>
                                            <th class="column5"></th>
                                            <th class="column6">Entitée</th>
                                        </tr>
                           
                        </table>
                    </div>
                </div>';*/
    if ($numrow != 0) {
        echo '<div class="limiter">
            <div class="container-table100">
                <div class="wrap-table100">
                    <div class="table100">
                        <table>
                            <thead>
                                        <tr class="table100-head">
                                            <th class="column1">' . $table_realname[0] . '</th>
                                            <th class="column2">' . $table_firstname[0] . '</th>
                                            <th class="column3"></th>
                                            <th class="column4"></th>
                                            <th class="column5"></th>
                                            <th class="column6">' . $table_entities_id[0] . '</th>
                                        </tr>
                            </thead>
                        </table>
                    </div>
                </div>';
    }





    // recherche les tickets de l'entitée du bénéficier
    $requestEntiteTicket = $DB->prepare('SELECT DISTINCT t.id, t.date_creation, t.name detail, t.date_mod, t.status, e.name entities, u.firstname prenom, u.realname nom
                                        FROM glpi_tickets t, glpi_entities e, glpi_users u, glpi_tickets_users tu
                                        WHERE e.name LIKE \''. $table_entities_id[0] . '\'
                                        AND tu.tickets_id = t.id 
                                        AND tu.users_id = u.id 
                                        AND e.id = t.entities_id
                                        AND t.status != 6 
                                        AND t.status != 5 
                                        AND t.is_deleted != 1
                                        AND tu.type = 1');
    $requestEntiteTicket->execute();

    $requestEntiteTicketResult = $requestEntiteTicket->get_result();
    $requestEntiteTicketResultNumRow = $requestEntiteTicketResult->num_rows;

    $table_id_entity = array();
    $table_date_creation_entity = array();
    $table_entities_id_entity = array();
    $table_name_entity = array();
    $table_date_mod_entity = array();
    $table_status_entity = array();
    $table_beneficiaire_entity = array();

    while ($dataEntity = $requestEntiteTicketResult->fetch_object()) {
        $table_id_entity[] = $dataEntity->id;
        $table_date_creation_entity[] = $dataEntity->date_creation;
        $table_entities_id_entity[] = $dataEntity->entities;
        $table_name_entity[] = $dataEntity->detail;
        $table_date_mod_entity[] = $dataEntity->date_mod;
        $table_status_entity[] = $dataEntity->status;
        $table_beneficiaire_entity[] = $dataEntity->prenom . ' ' . $dataEntity->nom;
    }


    if ($requestEntiteTicketResultNumRow != 0) {

        // Conversion des valeurs status
        for ($i = 0; $i < $requestEntiteTicketResultNumRow; $i++) {
            switch ($table_status_entity[$i]) {
                case 1:
                    $table_status_entity[$i] = 'Nouveau';
                    break;
                case 2:
                    $table_status_entity[$i] = 'En cours (attribué)';
                    break;
                case 3:
                    $table_status_entity[$i] = 'En cours (planifié)';
                    break;
                case 4:
                    $table_status_entity[$i] = 'En attente';
                    break;
            }
        }

        // affichage des valeurs des tickets
        echo '<div class="wrap-table100">
        <div class="table100">
        <table>
        <caption>Tickets de '. $table_entities_id_entity[0] .'</caption>
        <thead>
        <tr class="table100-head">
        <th class="column1">ID</th>
        <th class="column2">Date Création</th>
        <th class="column3">Description</th>
        <th class="column4">Entitée</th>
        <th class="column5">Dernière Modification</th>
        <th class="column6">Status</th>
        <th class="column7">Bénéficiaire</th>
        </tr>
        </thead>
        <tbody>';
        for ($i = 0; $i < $requestEntiteTicketResultNumRow; $i++) {
            echo '
            <tr>
            <td class="column1"><a href="" onclick=redirectPage("../../../front/ticket.form.php?id=' . $table_id_entity[$i] . '") style="display:block;width:100%;height:100%;">' . $table_id_entity[$i] . '</a></td>
            <td class="column2">' . $table_date_creation_entity[$i] . '</td>
            <td class="column3">' . $table_name_entity[$i] . '</td>
            <td class="column4">' . $table_entities_id_entity[$i] . '</td>
            <td class="column5">' . $table_date_mod_entity[$i] . '</td>
            <td class="column6">' . $table_status_entity[$i] . '</td>
            <td class="column7">' . $table_beneficiaire_entity[$i] . '</td>
            </tr>';
        }
        echo '<!--<tr >
                                            <th class="column1"><a href="" onclick=redirectPage("../../../front/ticket.form.php") style="display:block;width:100%;height:100%;">Ajouter un nouveau ticket</a></th>
                                            <th class="column2"></th>
                                            <th class="column3"></th>
                                            <th class="column4"></th>
                                            <th class="column5"></th>
                                            <th class="column6"></th>
                                        </tr>-->
                </tbody>
                </table>
                </div>
               </div>
                ';
    } // si aucun tickets en cours affiche que aucun n'est trouver et renvoie sur le formulaire de cr�ation
    else {
        echo '<!--<tr >
                                            <th class="column1">Aucun tickets en cours</th>
                                            <th class="column2"><a href="../../front/ticket.form.php" style="display:block;width:100%;height:100%;">Ajouter un ticket</a></th>
                                            <th class="column3"></th>
                                            <th class="column4"></th>
                                            <th class="column5"></th>
                                            <th class="column6"></th>
                                        </tr>-->
               </tbody>
               </table>
               <!--</div>
               </div>-->
               ';
    }






    // chercher tickets en cours pour l'user ou entitie courante
    /*if ($type == 'entities') {

        // requete pour chercher les tickets dans les entit�s   
        $requeteTickets = $DB->prepare('select t.id, t.date_creation, e.name entities,t.name detail, t.date_mod, t.status, u.firstname prenom, u.realname nom
                                                                         from glpi_tickets t, glpi_entities e, glpi_tickets_users tu, glpi_users u
                                                                         where t.entities_id = e.id and tu.tickets_id = t.id and tu.users_id = u.id 
                                                                         and t.status != 6 AND t.status != 5 AND t.is_deleted != 1
                                                                       and e.name = \'' . $table_entities_id[0] . '\'
                                                                         group by id
                                                                        order by t.id desc');
    } else */if ($type == 'users') {
        // requete pour chercher les tickets dans les utilisateurs
        $requeteTickets = $DB->prepare('select t.id, t.date_creation, e.name entities,t.name detail, t.date_mod, t.status, u.firstname prenom, u.realname nom
                                                                         from glpi_tickets t, glpi_entities e, glpi_tickets_users tu, glpi_users u
                                                                         where t.entities_id = e.id and tu.tickets_id = t.id and tu.users_id = u.id 
                                                                         and t.status != 6 AND t.status != 5 AND t.is_deleted != 1
                                                                       and u.realname = \'' . $table_realname[0] . '\'
                                                                         group by id
                                                                        order by t.id desc');
        /*}*/

        $requeteTickets->execute();

        // initialisation des tableaux d'affichage

        $table_id = array();
        $table_date_creation = array();
        $table_entities_id = array();
        $table_name = array();
        $table_date_mod = array();
        $table_status = array();
        $table_beneficiaire = array();

        // remplissage des tableaux avec les donnees de la requete
        $resultTicket = $requeteTickets->get_result();
        while ($data = $resultTicket->fetch_object()) {

            $table_id[] = $data->id;
            $table_date_creation[] = $data->date_creation;
            $table_entities_id[] = $data->entities;
            $table_name[] = $data->detail;
            $table_date_mod[] = $data->date_mod;
            $table_status[] = $data->status;
            $table_beneficiaire[] = $data->prenom . ' ' . $data->nom;
        }

        $number = $resultTicket->num_rows;

        // si des tickets sont trouve
        if ($number != 0) {

            // Conversion des valeurs status
            for ($i = 0; $i < $number; $i++) {
                switch ($table_status[$i]) {
                    case 1:
                        $table_status[$i] = 'Nouveau';
                        break;
                    case 2:
                        $table_status[$i] = 'En cours (attribué)';
                        break;
                    case 3:
                        $table_status[$i] = 'En cours (planifié)';
                        break;
                    case 4:
                        $table_status[$i] = 'En attente';
                        break;
                }
            }

            // affichage des valeurs des tickets
            echo '<div class="wrap-table100">
        <div class="table100">
        <table>
        <caption>Tickets Ouverts pour ' . $table_beneficiaire[0] . '</caption>
        <thead>
        <tr class="table100-head">
        <th class="column1">ID</th>
        <th class="column2">Date Création</th>
        <th class="column3">Description</th>
        <th class="column4">Entitée</th>
        <th class="column5">Dernière Modification</th>
        <th class="column6">Status</th>
        <!--<th class="column7">Bénéficiaire</th>--> <!--JD-->
        </tr>
        </thead>
        <tbody>';
            for ($i = 0; $i < $number; $i++) {
                echo '
            <tr>
            <td class="column1"><a href="" onclick=redirectPage("../../../front/ticket.form.php?id=' . $table_id[$i] . '") style="display:block;width:100%;height:100%;">' . $table_id[$i] . '</a></td>
            <td class="column2">' . $table_date_creation[$i] . '</td>
            <td class="column3">' . $table_name[$i] . '</td>
            <td class="column4">' . $table_entities_id[$i] . '</td>
            <td class="column5">' . $table_date_mod[$i] . '</td>
            <td class="column6">' . $table_status[$i] . '</td>
            <!--<td class="column7">' . $table_beneficiaire[$i] . '</td>--> <!--JD-->
            </tr>';
            }
            echo '</tbody>
                </table>
                </div>
                    </div>
                ';
        } // si aucun tickets en cours affiche que aucun n'est trouver et renvoie sur le formulaire de cr�ation
    }
        if ($numrow != 0) {
            $config = PluginPopupConfig::recupConfigToObject();
            $textLink = "'$config->link_redirect_add_ticket'";
            echo '
                        <div class="wrap-table100">
                            <div class="table100">
                                <table>
                                    <caption><a href="" onclick="href='.$textLink.'">Ajouter un ticket</a></caption>
                                </table>
                            </div>                
                        </div>
                    </div>
                </div>';
        }
        //<caption><a href="" onclick=redirectPage("'.$config->link_redirect_add_ticket.'")>Ajouter un ticket</a></caption>
    ?>
</body>

</html>