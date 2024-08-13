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

include ('../../../inc/includes.php');

$plugin = new Plugin();


if (!isset($_GET["id"])) {
   $_GET["id"] = "1";
}

if (!isset($_POST["id"])) {
   $_POST["id"] = "1";
}

if ($plugin->isActivated("ticketalerts")) {

   Session::checkRight("config", UPDATE);
   $config = new PluginTicketalertsConfig();
   if (isset($_POST["update"])) {
      if ($config->getFromDB(1)) {
         $config->update(['id' => 1, 'email' => $_POST['email']]);
      } else {
         $config->add($_POST);
      }
      Html::back();
   } else {
      Html::header(PluginTicketalertsConfig::getTypeName(2), '', "helpdesk", "pluginticketalertsmenu");
      $config->display($_GET);
      Html::footer();
   }

} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div align='center'><br><br>";
   echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>".__('Please activate the plugin', 'ticketalerts')."</b></div>";
}
