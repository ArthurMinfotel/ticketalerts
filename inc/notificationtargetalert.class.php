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

// Class NotificationTarget
class PluginTicketalertsNotificationTargetAlert extends NotificationTarget {

   const ADDRESS_CONFIG                     = 4300;

   function getEvents() {
      return  ['importerror' => __('Error of importing alert from API', 'ticketalerts')];
   }

   /**
    * Get additionnals targets for holiday
    */
   function addNotificationTargets($event = '') {
      $this->addTarget(PluginTicketalertsNotificationTargetAlert::ADDRESS_CONFIG, __('Address of ticketalerts setup', 'ticketalerts'));
   }

   function addSpecificTargets($data, $options) {
      switch ($data['items_id']) {
         case self::ADDRESS_CONFIG :
            return $this->getUserAddress();
      }
   }

   //Get recipient
   function getUserAddress() {
      global $DB;

       $query = " SELECT DISTINCT `email`
                  FROM `glpi_plugin_ticketalerts_configs`
                  WHERE `id` = 1";

       $result = $DB->query($query);
       $res['email'] = $DB->result($result, 0, 'email');

       $this->addToRecipientsList($res);
   }

   function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI,$DB;

      $this->data['##lang.ticketalert.title##'] = __('Error import alert', 'ticketalerts');
      $this->data['##lang.ticketalert.description##'] = __('An error when importing the alert occurred', 'ticketalerts');
      $this->data['##lang.ticketalert.error##'] = __('Error');

      if (isset($options['error'])) {
         $this->data['##ticketalert.error##'] = $options['typeerror'] . " : " . $options['error'];
      } else if (isset($options['typeerror'])) {
         $this->data['##ticketalert.error##'] = $options['typeerror'];
      }

   }

   function getTags() {

      $tags = ['ticketalert.title'            => __('Error import alert', 'ticketalerts'),
                    'ticketalert.description'  => __('An error when importing the alert occurred', 'ticketalerts'),
                    'ticketalert.error'              => __('Error')];

      foreach ($tags as $tag => $label) {
         $this->addTagToList(['tag'   => $tag,
                                   'label' => $label,
                                   'value' => true]);
      }

      asort($this->tag_descriptions);
   }
}
