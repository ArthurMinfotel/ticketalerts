<?php

/*
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


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginTicketalertsConfig extends CommonDBTM {

   static $rightname = 'plugin_ticketalerts';

   static function getTypeName($nb = 0) {
      return __('Setup', 'ticketalerts');
   }

   static function canView() {

      return (Session::haveRight(self::$rightname, UPDATE));
   }


   static function canCreate() {

      return (Session::haveRight(self::$rightname, CREATE));
   }

   /**
    * Show form
   *
   * @global type $CFG_GLPI
   * @return boolean
   */
   function showForm ($ID, $options = []) {
      global $CFG_GLPI;

      if (!$this->canView() || !$this->canCreate()) {
         return false;
      }

      if (!$this->getFromDB(1)) {
         $this->getEmpty();
      }

      echo "<form name='form' method='post' action='".Toolbox::getItemTypeFormURL('PluginTicketalertsConfig')."'>";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Intranet Setup', 'ticketalerts')."</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Email')."</td>";
      echo "<td>";
      echo Html::input('email', ['value' => $this->fields['email']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><td class='tab_bg_2 center' colspan='6'><input type=\"submit\" name=\"update\" class=\"submit\"
         value=\""._sx('button', 'Save')."\" ></td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }

   /**
    * @see CommonGLPI::defineTabs()
    */
   function defineTabs($options = []) {

      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }

   /**
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$withtemplate) {
         switch ($item->getType()) {
            case __CLASS__ :
               $tab[1] = __('Intranet setup', 'ticketalerts');
               return $tab;
         }

      }
      return '';
   }

   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {

            case 1 :
               $item->showForm($item->getID());
               break;

            case 2 :
               $item->showAddFilterForm();
               break;
         }
      }
      return true;
   }


}
