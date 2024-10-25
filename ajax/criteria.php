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

$AJAX_INCLUDE = 1;
include('../../../inc/includes.php');

switch ($_REQUEST['action']) {
    case "move_criteria":
        if (PluginTicketalertsAlertGroupCriteria::moveCriteria(
            (int) $_POST['criteria_id'],
            (int) $_POST['ref_id'],
            (int) $_POST['criteria_group'],
            $_POST['sort_action']
        )) {
            Session::addMessageAfterRedirect(__('Order updated', 'ticketalerts'));
        } else {
            Session::addMessageAfterRedirect(
                __('Error when updating order', 'ticketalerts'),
                ERROR
            );
        }
        break;
}
