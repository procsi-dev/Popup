<?php

/**
 * -------------------------------------------------------------------------
 * Popup plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Credit.
 *
 * Credit is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Credit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Credit. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @author    Julien Dion
 * @copyright Copyright (C) 2023-2024 by the Popup Development Team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/procsi-dev/popup
 * -------------------------------------------------------------------------
 */

define('PLUGIN_POPUP_VERSION', '1.0.4');

// Minimal GLPI version, inclusive
define("PLUGIN_POPUP_MIN_GLPI_VERSION", "10.0.6");
// Maximum GLPI version, exclusive
define("PLUGIN_POPUP_MAX_GLPI_VERSION", "10.0.99");

include_once "src/Utils.php";

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_popup()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['popup'] = true;

    Plugin::registerClass('PluginPopupConfig', ['addtabon' => 'Config']);

    // Config page
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['popup'] = 'front/config.form.php';
    }

    $plugin = new Plugin();
    if ($plugin->isActivated('popup')) {
        $PLUGIN_HOOKS['add_javascript']['popup']='js/newWindow.js';


        PluginPopupUtils::createPopupFolder();
    }
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_popup()
{
    return [
        'name'           => 'Popup',
        'version'        => PLUGIN_POPUP_VERSION,
        'author'         => '<a href="https://www.procsi.com">PROCSI</a>', // JD
        'license'        => '',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_POPUP_MIN_GLPI_VERSION,
                'max' => PLUGIN_POPUP_MAX_GLPI_VERSION,
            ]
        ]
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_popup_check_prerequisites()
{
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_popup_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'popup');
    }
    return false;
}
