<?php
/*
 -------------------------------------------------------------------------
 Stockview plugin for GLPI
 Copyright (C) 2013 by the Stockview Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Stockview.

 Stockview is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Stockview is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Stockview. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include("../../../inc/includes.php");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$alertgroup          = new PluginTicketalertsAlertGroup();
$alertgroup_criteria = new PluginTicketalertsAlertGroupCriteria();
global $CFG_GLPI;

if (isset($_POST["add"])) {
   $alertgroup->check(-1, CREATE, $_POST);
   $newID = $alertgroup->add($_POST);
   Html::redirect($CFG_GLPI["root_doc"] . "/plugins/ticketalerts/front/alertgroup.form.php?id=$newID");

} else if (isset($_POST["add_criteria_filter"])) {
   $alertgroup_criteria->check(-1, CREATE, $_POST);
   $alertgroup_criteria->add($_POST);
   Html::redirect($CFG_GLPI["root_doc"] . "/plugins/ticketalerts/front/alertgroup.form.php?id=" . $_POST['plugin_ticketalerts_alertgroups_id']);

} else if (isset($_POST["delete"])) {
   $alertgroup->check($_POST['id'], DELETE);
   $ok = $alertgroup->delete($_POST);
   Html::redirect($CFG_GLPI["root_doc"] . "/plugins/ticketalerts/front/alertgroup.php");

} else if (isset($_REQUEST["purge"])) {
   $alertgroup->check($_REQUEST['id'], PURGE);
   $alertgroup->delete($_REQUEST, 1);
   Html::redirect($CFG_GLPI["root_doc"] . "/plugins/ticketalerts/front/alertgroup.php");

} else if (isset($_POST["update"])) {
   $alertgroup->check($_POST['id'], UPDATE);
   $alertgroup->update($_POST);
   Html::back();
} else {

   Html::header(PluginTicketalertsAlertGroup::getTypeName(2), '',
            "admin", "pluginticketalertsalertgroup", "alertgroup");

   $alertgroup->display($_GET);
   Html::footer();
}
