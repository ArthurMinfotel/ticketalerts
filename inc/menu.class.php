<?php
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

class PluginTicketalertsMenu extends CommonGLPI {

   static $rightname = 'plugin_ticketalerts';

   static function getMenuName($nb = 1) {
      return PluginTicketalertsAlert::getTypeName(2);
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $menu                                           = [];
      $menu['title']                                  = self::getMenuName(2);
      $menu['page']                                   = PluginTicketalertsAlert::getSearchURL(false);
      $menu['links']['search']                        = PluginTicketalertsAlert::getSearchURL(false);
//      $menu['links']['add']                           = PluginTicketalertsAlert::getFormURL(false);


      if (Session::haveRight("config", UPDATE)) {
         //Entry icon in breadcrumb
         $menu['links']['config']                      = PluginTicketalertsConfig::getFormURL(false);
         //Link to config page in admin plugins list
         $menu['config_page']                          = PluginTicketalertsConfig::getFormURL(false);

         //Add a fourth level in breadcrumb for configuration page
         $menu['options']['config']['title']           = __('Setup');
         $menu['options']['config']['page']            = PluginTicketalertsConfig::getFormURL(false);
         $menu['options']['config']['links']['search'] = PluginTicketalertsConfig::getFormURL(false);
         $menu['options']['config']['links']['add']    = PluginTicketalertsConfig::getFormURL(false);
      }

         $menu['options']['alertgroup']['title']           = PluginTicketalertsAlertGroup::getTypeName(1);
         $menu['options']['alertgroup']['page']            = PluginTicketalertsAlertGroup::getSearchURL(false);
         $menu['options']['alertgroup']['links']['search'] = PluginTicketalertsAlertGroup::getSearchURL(false);
         $menu['options']['alertgroup']['links']['add']    = PluginTicketalertsAlertGroup::getFormURL(false);

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['helpdesk']['types']['PluginTicketalertsMenu'])) {
         unset($_SESSION['glpimenu']['helpdesk']['types']['PluginTicketalertsMenu']);
      }
      if (isset($_SESSION['glpimenu']['helpdesk']['content']['pluginticketalertsmenu'])) {
         unset($_SESSION['glpimenu']['helpdesk']['content']['pluginticketalertsmenu']);
      }
   }
}