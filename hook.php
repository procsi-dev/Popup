<?php

/**
 * -------------------------------------------------------------------------
 * Popup plugin for GLPI
 * Copyright (C) 2023 by the Popup Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_popup_install()
{
    $migration = new Migration('1.0.4');

    // Parse inc directory
    foreach (glob(dirname(__FILE__).'/src/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/src.(.+)\.php$/", $filepath, $matches)) {
            $classname = 'PluginPopup' . ucfirst($matches[1]);
            include_once($filepath);
            // If the install method exists, load it
            if (method_exists($classname, 'install')) {
                $classname::install($migration);
            }
        }
    }
    $migration->executeMigration();

    global $DB;

    $config = new Config();
    $config->setConfigurationValues('plugin:Popup', ['configuration' => false]);

    ProfileRight::addProfileRights(['popup:read']);

    if (!$DB->tableExists("glpi_plugin_popup_doublons")) {
        $query = "CREATE TABLE glpi_plugin_popup_doublons AS (SELECT * FROM (
            SELECT DISTINCT id as entitiesid, name as entitiesname, phonenumber, null as usersid, null as useractive, null as usersentitiesid, null as firstname, null as realname, null as phone, null as phone2, null as mobile
            FROM glpi_entities
            UNION ALL 
            SELECT null as entitiesid, null as entitiesname, null as phonenumber, u.id as usersid, u.is_active as useractive, e.name as usersentities, u.firstname, u.realname, u.phone, u.phone2, u.mobile 
            FROM glpi_users u, glpi_entities e
            WHERE u.entities_id = e.id) AS glpi_plugin_popup_doublons)";

        $DB->query($query) or die("error creation de glpi_plugin_popup_doublons");

        $query = 'UPDATE glpi_plugin_popup_doublons SET
         phonenumber = REPLACE(phonenumber,"+33","0"),
         phone = REPLACE(phone,"+33","0"), 
         phone2 = REPLACE(phone2,"+33","0"), 
         mobile = REPLACE(mobile,"+33","0")';

        $DB->query($query) or die("error remplace +33 en 0 dans glpi_plugin_popup_doublons");

        $query = 'UPDATE glpi_plugin_popup_doublons SET 
         phonenumber = REPLACE(phonenumber," ",""), 
         phone = REPLACE(phone," ",""), 
         phone2 = REPLACE(phone2," ",""), 
         mobile = REPLACE(mobile," ","")';

        $DB->query($query) or die("error enlevement les espaces dans glpi_plugin_popup_doublons");

        $query = 'UPDATE glpi_plugin_popup_doublons SET 
                        phonenumber = REPLACE(phonenumber,"0000000000",""), 
                        phone = REPLACE(phone,"0000000000",""), 
                        phone2 = REPLACE(phone2,"0000000000",""), 
                        mobile = REPLACE(mobile,"0000000000","")';

        $DB->query($query) or die("error enlevement les 0000000000 dans glpi_plugin_popup_doublons");

        $query = "CREATE VIEW glpi_view_plugin_popup_doublons as(SELECT * FROM (
            select 'Entitie' as type, entitiesid as id, useractive as actif, entitiesname as identifiant, phonenumber as tel1 from glpi_t_numeros where (phonenumber is not null)
            union all
            select 'User' as type, usersid as id, useractive as actif, concat(firstname,' ',realname) as identifiant, phone as tel1 from glpi_t_numeros where (phone is not null) and (length(phone)>0) 
            union all
            select 'User' as type, usersid as id, useractive as actif, concat(firstname,' ',realname) as identifiant, phone2 as tel1 from glpi_t_numeros where (phone2 is not null) and (length(phone2)>0) 
            union all
            select 'User' as type, usersid as id, useractive as actif, concat(firstname,' ',realname) as identifiant, mobile as tel1 from glpi_t_numeros where (mobile is not null) and (length(mobile)>0)
            ) AS glpi_view_plugin_popup_doublons)";

        $DB->query($query) or die("error creation de glpi_view_plugin_popup_doublons");
    }

    if (!$DB->tableExists("glpi_plugin_popup_config")) {
        $query = "CREATE TABLE glpi_plugin_popup_config(
            width INT,
            height INT,
            link_redirect_add_ticket VARCHAR(200),
            complete_unknownuser_option BOOLEAN
        )";

        $DB->query($query) or die("error creation de glpi_plugin_popup_config");

        $query = "INSERT INTO glpi_plugin_popup_config VALUES (
            800,
            1200,
            '../../../front/ticket.form.php',
            false
        )";

        $DB->query($query) or die("error insertion valeur défaut de glpi_plugin_popup_config");
    }

    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_popup_uninstall()
{
    foreach (glob(dirname(__FILE__).'/src/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/inc.(.+)\.php/", $filepath, $matches)) {
            $classname = 'PluginPopup' . ucfirst($matches[1]);
            include_once($filepath);
            // If the install method exists, load it
            if (method_exists($classname, 'uninstall')) {
                $classname::uninstall();
            }
        }
    }
    global $DB;

    $config = new Config();
    $config->deleteConfigurationValues('plugin:Popup', ['configuration' => false]);

    ProfileRight::deleteProfileRights(['popup:read']);

    $notif = new Notification();
    $options = ['event'    => 'plugin_popup'];
    foreach ($DB->request('glpi_notifications', $options) as $data) {
        $notif->delete($data);
    }

    if ($DB->tableExists("glpi_plugin_popup_doublons")) {
        $query = "DROP TABLE `glpi_plugin_popup_doublons`";
        $DB->query($query) or die("error deleting glpi_plugin_popup_doublons");

        $query = "DROP view glpi_view_plugin_popup_doublons";
        $DB->query($query) or die("error deleting glpi_view_plugin_popup_doublons");
    }
    if ($DB->tableExists("glpi_plugin_popup_config")) {
        $query = "DROP TABLE `glpi_plugin_popup_config`";
        $DB->query($query) or die("error deleting glpi_plugin_popup_config");
    }

    return true;
}
