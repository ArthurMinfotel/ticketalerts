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

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$alert = new PluginTicketalertsAlert();

if (isset($_POST["add"])) {
   $alert->check(-1, CREATE, $_POST);
   $newID = $alert->add($_POST);
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($alert->getFormURL() . "?id=" . $newID);
   }
   Html::back();
} else if (isset($_POST["delete"])) {
   $alert->check($_POST['id'], DELETE);
   $alert->delete($_POST);
   $alert->redirectToList();

} else if (isset($_POST["restore"])) {
   $alert->check($_POST['id'], PURGE);
   $alert->restore($_POST);
   $alert->redirectToList();

} else if (isset($_POST["purge"])) {
   $alert->check($_POST['id'], PURGE);
   $alert->delete($_POST, 1);
   $alert->redirectToList();

} else if (isset($_POST["update"])) {
   $alert->check($_POST['id'], UPDATE);
   $alert->update($_POST);
   Html::back();

} else if (isset($_POST["done"])) {
   $alert->check($_POST['id'], UPDATE);
   if ($alert->getFromDB($_POST['id'])) {
      if ($alert->fields['users_id'] > 0) {
         Session::addMessageAfterRedirect(__('The alert is already taken into account', 'ticketalerts'), false, ERROR);
         Html::back();
      } else {
         $options['id']       = $_POST['id'];
         $options['state']    = 2;
         $options['taking_into_account_date']    = $_SESSION['glpi_currenttime'];
         $options['users_id'] = $_SESSION['glpiID'];
         $alert->update($options);
         //Adding in the history of the ticket
         $changes[0]          = '0';
         $changes[1]          = "";
         $changes[2]          = sprintf(__('Took into account the alert %1$s related to ticket', 'ticketalerts'), $_POST['id']);
         Log::history($_POST['tickets_id'], "Ticket", $changes, 0, 12);

         //add followup
         $followup = new ITILFollowup();
         $input['tickets_id'] = $_POST['tickets_id'];
         $input['content'] = __('Ticket taken into account by Alturing', 'ticketalerts');
         $input['users_id'] = $_SESSION['glpiID'];
         $input['itemtype'] = Ticket::class;
         $input['items_id'] = $_POST['tickets_id'];
         $followup->add($input);

         $ticket              = new Ticket();
         Html::redirect($ticket->getFormURL() . "?forcetab=Ticket\$main&id=" . $_POST['tickets_id']);
      }
   }
} else {

   $alert->checkGlobal(READ);

   Html::header(PluginTicketalertsAlert::getTypeName(2), '', "helpdesk", "pluginticketalertsmenu");

   $alert->display($_GET);

   Html::footer();
}