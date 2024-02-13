<?php
class PluginPopupConfig extends Config
{

   static protected $notable = true;

    static function getTypeName($nb = 0) {
        return __('Popup');
    }

    function prepareInputForUpdate($input) {
        return $input;
    }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      if ($item->getType() == 'Config') {
         return __('Popup');
      }
      return '';
   }

   static function configUpdate($input)
   {
      $input['configuration'] = 1 - $input['configuration'];
      return $input;
   }

    static function getConfig($name = null) {
        $conf = Config::getConfigurationValues('plugin:Popup');

        // Get field value if specified
        if ($name) {
            if (!isset($conf[$name])) {
                return null;
            }

            return $conf[$name];
        }

        return $conf;
    }

    static function displayTabContentForItem(
        CommonGLPI $item,
                   $tabnum = 1,
                   $withtemplate = 0) {
        switch ($item->getType()) {
            case "Config":
                return self::showForConfig();
        }

        return true;
    }

    // Affiche la page debug de popup dans configuration -> General
   static function showForConfig()
   {
       if (!self::canView()) {
           return false;
       }

       $config = self::recupConfigToObject();

      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>" . __('Popup') . "</th></tr>";
      echo "<td >" . __('Test si le numero de téléphone est attribuer :') . "</td>";
      echo "<td colspan='3'>";
      echo "<input id=testnumeroattribuer type='text' name='config_class' value=''>";
      echo "<td colspan='4' class='center'>";
      echo "<button id=testnumeroattribuerBouton class='btn btn-primary' onclick=numeroExiste() disabled >Rechercher</button>";
      echo "</td>";

      echo "<tr><td >" . __('Affiche tous les numeros de téléphones doublons et les utilisateurs qui ont un numero de téléphone attribuer deux fois:') . "</td>";
      echo "<td colspan='3'></td>";
      echo "<td colspan='4' class='center'>";
      echo "<button class='btn btn-primary' onclick=numeroDoublon() >Afficher Les Numéros Doublons</button>";
      echo "</td></tr>";

       echo "<tr><td >" . __('Configuration de link de redirection du button ajouter ticket : ( Default : ../../../front/ticket.form.php )') . "</td>";
       echo "<td colspan='3'></td>";
       echo "<td colspan='4'><input id=popupLink type='text' name='config_class'  value='". $config->link_redirect_add_ticket ."'>";
       echo "</tr>";

       echo "<tr><td >" . __('Configuration de la taille de la popup :') . "</td>";
       echo "<td colspan='3'>Largeur : <input id=popupWidth type='number' name='config_class'  value='". $config->width ."'></td>";
       echo "<td colspan='4'>Hauteur : <input id=popupHeight type='number' name='config_class' value='". $config->height ."'></td>";
       echo "</tr>";

       echo "<tr><td ></td>";
       echo "<td colspan='3'></td>";
       echo "<td colspan='4' class='center'>";
       echo "<button class='btn btn-primary' onclick=saveConfigPopup() >Sauvegarder la config</button>";
       echo "</td></tr>";

      echo "</table></div>";
   }

   // Retour la request qui affiche les doublons après avoir actualiser la table "glpi_plugin_popup_doublons"
    // et la vue glpi_view_plugin_popup_doublons
   static function viewDoublonPage()
   {
       global $DB;
       // supression table si elle existe
       $request = $DB->prepare("TRUNCATE table glpi_plugin_popup_doublons");
       $request->execute();

       // creation table avec les numeros entitees et les numeros utilisateurs
       $request = $DB->prepare('INSERT INTO glpi_plugin_popup_doublons SELECT * FROM (
        SELECT DISTINCT id as entitiesid, name as entitiesname, phonenumber, null as usersid, null as useractive, null as usersentitiesid, null as firstname, null as realname, null as phone, null as phone2, null as mobile
        FROM glpi_entities
        UNION ALL 
        SELECT null as entitiesid, null as entitiesname, null as phonenumber, u.id as usersid, u.is_active as useractive, e.name as usersentities, u.firstname, u.realname, u.phone, u.phone2, u.mobile 
        FROM glpi_users u, glpi_entities e
        WHERE u.entities_id = e.id) AS glpi_plugin_popup_doublons'); // JD request réparrer (mettre l'union des selects dans un select et créer la table en non temporêre)

       $request->execute(); //or die("error insert glpi_plugin_popup_doublons");
       // Update de la table pour metre les numeros tous sous le meme format

       // mettre les +33 en 0
       $request = $DB->prepare('UPDATE glpi_plugin_popup_doublons SET
         phonenumber = REPLACE(phonenumber,"+33","0"),
         phone = REPLACE(phone,"+33","0"), 
         phone2 = REPLACE(phone2,"+33","0"), 
         mobile = REPLACE(mobile,"+33","0")');

       $request->execute(); //or die("error remplace +33 en 0 dans glpi_plugin_popup_doublons");

       // enlever les espaces
       $request = $DB->prepare('UPDATE glpi_plugin_popup_doublons SET 
         phonenumber = REPLACE(phonenumber," ",""), 
         phone = REPLACE(phone," ",""), 
         phone2 = REPLACE(phone2," ",""), 
         mobile = REPLACE(mobile," ","")');

       $request->execute();// or die("error enlevement les espaces dans glpi_plugin_popup_doublons");


       // enlever les 0000000000
       $request = $DB->prepare('UPDATE glpi_plugin_popup_doublons SET
         phonenumber = REPLACE(phonenumber," ",""),
         phone = REPLACE(phone," ",""), 
         phone2 = REPLACE(phone2," ",""), 
         mobile = REPLACE(mobile," ","")');

       $request->execute();// or die("error enlevement les 0000000000 dans glpi_plugin_popup_doublons");

       // creer vue
       $request = $DB->query("ALTER view glpi_view_plugin_popup_doublons as(SELECT * FROM (
        select 'Entitie' as type, entitiesid as id, useractive as actif, entitiesname as identifiant, phonenumber as tel1 from glpi_plugin_popup_doublons where (phonenumber is not null)
        union all
        select 'User' as type, usersid as id, useractive as actif, concat(firstname,' ',realname) as identifiant, phone as tel1 from glpi_plugin_popup_doublons where (phone is not null) and (length(phone)>0) 
        union all
        select 'User' as type, usersid as id, useractive as actif, concat(firstname,' ',realname) as identifiant, phone2 as tel1 from glpi_plugin_popup_doublons where (phone2 is not null) and (length(phone2)>0) 
        union all
        select 'User' as type, usersid as id, useractive as actif, concat(firstname,' ',realname) as identifiant, mobile as tel1 from glpi_plugin_popup_doublons where (mobile is not null) and (length(mobile)>0)
        ) AS glpi_view_plugin_popup_doublons)");
       // or die("error alter de glpi_view_plugin_popup_doublons");

       $request = $DB->prepare('SELECT glpi_view_plugin_popup_doublons.*, count(type) as nbenregistrement
                                      FROM glpi_view_plugin_popup_doublons
                                      inner join(
                                      SELECT min(identifiant), tel1, count(id)
                                      FROM glpi_view_plugin_popup_doublons
                                      Where tel1 not like "0000000000"
                                      group by tel1
                                      having count(id)>1
                                      ) as regroupement
                                      on regroupement.tel1 = glpi_view_plugin_popup_doublons.tel1 
                                      group by type,id,identifiant,tel1
                                      order by tel1');
       $request->execute();

       return $request;// or die("error select de glpi_view_plugin_popup_doublons");
   }

   // Recupper la configuration dans "glpi_plugin_popup_config" et la retourne dans une list
    static function recupConfigToObject(){
        global $DB;

        $request = $DB->prepare('SELECT * FROM glpi_plugin_popup_config');

        $request->execute();
        return $request->get_result()->fetch_object();
    }

    static function updateConfig(){
        global $DB;

        $doc = new DOMDocument;
        $doc->validateOnParse = true;
        $doc->Load('../../../front/config.form.php');

        $width = $doc->getElementById('popupWidth')->nodeValue??1200;
        $height = $doc->getElementById('popupHeight')->nodeValue??800;
        $link_redirect_add_ticket = $doc->getElementById('link_redirect_ticket_popup')->nodeValue??'../../../front/ticket.form.php';
        $complete_unknownuser_option = $doc->getElementById('button_complete_option_popup')??false;

        $request = $DB->prepare("UPDATE glpi_plugin_popup_config SET width = ".$width.", 
                                                            height = ".$height.",
                                                            link_redirect_add_ticket = '".$link_redirect_add_ticket."',
                                                            complete_unknownuser_option = ".$complete_unknownuser_option);

        $request->execute();
    }

    // Convertie le numero appeler en les 4 formas potentiellement utiliser
    static function convertionNum($num){
        if (strcmp($num[1] . $num[2], "33") != 0) { // verification si c'est pas un +33 dans ce cas tous les cas de numero sont les memes
            $num4 = str_replace(" ", "", "+" . $num);
            $num3 = str_replace(" ", "", "+" . $num); // enlever les espaces mis apr�s le +
            $num2 = str_replace(" ", "", "+" . $num);
            $num = str_replace(" ", "", "+" . $num);
        }
        else {

            // Conversion num : 3368954... -> 068954....
            $j = 3;
            $num2 = $num;
            $num[0] = '0';

            for ($i = 1; $i < 10; $i++) {
                $num[$i] = $num2[$j];
                $j++;
            }

            $num = substr($num, 0, -2);

            // Conversion num : 3368954... -> 06 89 54....

            $t = 0;
            $j = 0;
            $num3 = $num;

            for ($i = 0; $i < 14; $i++) {
                if ($t == 2) {
                    $num3[$i] = ' ';
                    $t = 0;
                } else {
                    $num3[$i] = $num[$j];
                    $t++;
                    $j++;
                }
            }

            $num2 = '+' . $num2;

            for ($i = 1; $i < 12; $i++) {
                $num2[$i] = $num2[$i + 1];
            }

            $num2 = substr($num2, 0, -1);

            // Conversion +33447... -> +33 4 50 14 ...

            $num4 = $num;

            $t = 0;
            $j = 0;
            $i = 0;

            for ($i = 4; $i < 18; $i++) {
                if ($t == 2) {
                    $num4[$i] = ' ';
                    $t = 0;
                } else {
                    $num4[$i] = $num[$j];
                    $t++;
                    $j++;
                }
            }

            $num4 = substr_replace($num4, '+33', 0, 5);

            $num4  = substr_replace($num4, ' ', 3, 0);;
        }

        return ['num' => $num,
                'num2' => $num2,
                'num3' => $num3,
                'num4' => $num4];
    }
}
