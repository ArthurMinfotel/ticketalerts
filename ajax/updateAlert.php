<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();


$alert = new PluginTicketalertsAlert();
if (isset($_POST['id'])) {
   $alert->check($_POST['id'], UPDATE);
   if ($alert->getFromDB($_POST['id'])) {
      if ($alert->fields['users_id'] > 0) {
          Session::addMessageAfterRedirect(__('The alert is already taken into account', 'ticketalerts'), false, ERROR);
          echo 0;

//         Html::back();
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
         $input['content'] = __('Ticket taken into account by Alturing', 'ticketalerts');
         $input['users_id'] = $_SESSION['glpiID'];
         $input['itemtype'] = Ticket::class;
         $input['items_id'] = $_POST['tickets_id'];
         $followup->add($input);

         $ticket              = new Ticket();
        echo $ticket->getFormURL() . "?forcetab=Ticket\$main&id=" . $_POST['tickets_id'];
      }
   }
}
