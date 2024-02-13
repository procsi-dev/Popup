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
