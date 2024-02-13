<?php

include_once "Config.php";

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginPopupUtils
{

    public static function createPopupFolder(): bool {
        $parent_dir = GLPI_PLUGIN_DOC_DIR;

        // Folder exist, stop here
        if (is_dir("$parent_dir/popup")) {
            return true;
        }

        // Try to create the folder
        if (!mkdir("$parent_dir/popup")) {
            Toolbox::logError("Popup: $parent_dir is not writtable");
            return false;
        }

        return true;
    }

    public static function uploadedFileExist($file) {
        return file_exists(GLPI_TMP_DIR . "/$file");
    }

}