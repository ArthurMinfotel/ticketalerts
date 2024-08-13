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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginTicketalertsProfile extends Profile {

   static $rightname = "profile";

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Profile') {
         return PluginTicketalertsAlert::getTypeName(2);
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID,
                                    ['plugin_ticketalerts'    => 0, 'plugin_ticketalerts_manage_groupalert' => 0]);
         $prof->showForm($ID);
      }
      return true;
   }

   static function createFirstAccess($ID) {
      //85

      $rights = ['plugin_ticketalerts'                   => ALLSTANDARDRIGHT + READNOTE + UPDATENOTE,
                 'plugin_ticketalerts_manage_groupalert' => 0];
      self::addDefaultProfileInfos($ID, $rights, true);
   }

    /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;

      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (countElementsInTable('glpi_profilerights',
                                            ['profiles_id' => $profiles_id, 'name' => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!countElementsInTable('glpi_profilerights',
                                   ['profiles_id' => $profiles_id, 'name' => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   function showForm($ID, $options = []) {
        if (isset($options['openform'])) {
            $openform = $options['openform'];
        } else {
            $openform = true;
        }
       if (isset($options['closeform'])) {
           $closeform = $options['closeform'];
       } else {
           $closeform = true;
       }
      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($ID);
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
         $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                         'default_class' => 'tab_bg_2',
                                                         'title'         => __('General')]);
      }

      echo "<table class='tab_cadre_fixehov'>";

      echo "<tr class='tab_bg_1'><th colspan='4'>" . __('Administration') . "</th></tr>\n";

      $effective_rights = ProfileRight::getProfileRights($ID, ['plugin_ticketalerts_manage_groupalert']);

      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>" . __('Manage group alert', 'ticketalerts') . "</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(['name'    => '_plugin_ticketalerts_manage_groupalert',
                              'checked' => $effective_rights['plugin_ticketalerts_manage_groupalert']]);
      echo "</td></tr>\n";
      echo "</table>";

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights($all = false) {
      $rights = [
          ['itemtype'  => 'PluginTicketalertsAlert',
                          'label'     => PluginTicketalertsAlert::getTypeName(2),
                          'field'     => 'plugin_ticketalerts'
          ],
      ];

      if ($all) {
         $rights[] = array('itemtype' => 'PluginTicketalertsAlertGroup',
                           'label'    => __('Manage group alert', 'ticketalerts'),
                           'field'    => 'plugin_ticketalerts_manage_groupalert'
         );
      }

      return $rights;
   }

   /**
    * Init profiles
    *
    **/

   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }


   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable("glpi_profilerights",
                                  ['name' => $data['field']]) == 0) {
            ProfileRight::addProfileRights([$data['field']]);
         }
      }

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_ticketalerts%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }


   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}
