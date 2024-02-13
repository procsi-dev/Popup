<?php
include_once "../../../inc/includes.php";
Session::checkRight('config', READ);

$plugin = new Plugin();
if (!$plugin->isActivated('popup')) {
    header("Location: ../../../index.php");
    die(); 
}

global $DB;

$width = $_GET['width'];
$height = $_GET['height'];
$link = $_GET['link'];



$request = $DB->prepare('UPDATE glpi_plugin_popup_config 
                                SET width = \''.$width.'\',
                                    height = \''.$height.'\', 
                                    link_redirect_add_ticket = \''.$link.'\'
                                WHERE complete_unknownuser_option = 0');
$request->execute();
?>