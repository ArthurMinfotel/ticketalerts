<?php

define('PLUGIN_TICKETALERTS_VERSION', '2.1.0');

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Ticketalerts plugin for GLPI
 Copyright (C) 2003-2016 by the Ticketalerts Development Team.

 https://github.com/InfotelGLPI/ticketalerts
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Ticketalerts.

 ticketalerts is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 ticketalerts is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Ticketalerts. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_ticketalerts() {
   global $PLUGIN_HOOKS,$CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['ticketalerts'] = true;
   $PLUGIN_HOOKS['change_profile']['ticketalerts'] = ['PluginTicketalertsProfile','initProfile'];

   // on central page
   if (strpos($_SERVER['REQUEST_URI'], "central.php") !== false) {
      //history and climb feature
       $PLUGIN_HOOKS['add_javascript']['ticketalerts'][] = 'scripts/alert.js';
       $PLUGIN_HOOKS['add_css']['ticketalerts'][] = "css/ticketalerts.css";
   }

    if (strpos($_SERVER['REQUEST_URI'], "common.tabs.php")) {
        if ($_REQUEST['_glpi_tab'] === 'Central$1') { // tab "Vue personnelle" sur central
            if (Session::getCurrentInterface() == "central"
                && Session::haveRight("plugin_ticketalerts", READ)) {
                $PLUGIN_HOOKS['display_central']['ticketalerts'] = 'plugin_ticketalerts_display_central';
            }
        }
    }

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginTicketalertsAlert', [
         'linkuser_types' => true,
         'notificationtemplates_types' => true
      ]);

      Plugin::registerClass('PluginTicketalertsProfile',
                         ['addtabon' => 'Profile']);


      $PLUGIN_HOOKS['menu_toadd']['ticketalerts']['admin'] = 'PluginTicketalertsAlertGroup';

      $PLUGIN_HOOKS['menu_toadd']['ticketalerts']['helpdesk'] = 'PluginTicketalertsMenu';

      $PLUGIN_HOOKS['config_page']['ticketalerts'] = 'front/config.form.php';

      if (Session::haveRight("plugin_ticketalerts", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['ticketalerts'] = 1;
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_ticketalerts() {

   return  [
      'name' => _n('Ticket alert', 'Tickets alerts', 2, 'ticketalerts'),
      'version' => PLUGIN_TICKETALERTS_VERSION,
      'license' => 'GPLv2+',
      'author'  => "<a href='http://infotel.com/services/expertise-technique/glpi/'>Infotel</a>",
      'homepage'=>'https://github.com/InfotelGLPI/ticketalerts',
      'minGlpiVersion' => "10.0", // For compatibility / no install in version < 0.85
      'requirements' => [
         'glpi' => [
            'min' => '10.0',
            'dev' => false
         ]
      ]
   ];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_ticketalerts_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '10.0', 'lt') || version_compare(GLPI_VERSION, '10.1', 'ge')) {
      echo __('This plugin requires GLPI >= 10.0 and < 10.1', 'ticketalerts');
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_ticketalerts_check_config() {
   return true;
}
