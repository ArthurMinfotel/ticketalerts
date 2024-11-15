<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();
global $CFG_GLPI;

if (isset($_POST ['criteria'])) {

    $opt = ['name' => 'filter'];
    switch ($_POST['criteria']) {
        case 'ticket_type':
            Ticket::dropdownType('filter');
            break;
        case 'itilcategory':
            ITILCategory::dropdown($opt);
            break;
        case 'alert_type':
            $opt = array_merge($opt, ['entity' => $_SESSION["glpiactive_entity"]]);
            Dropdown::show('PluginTicketalertsAlertType', $opt);
            break;
        case 'ticket_status':
            Dropdown::showFromArray('filter', Ticket::getAllStatusArray());
            break;
        case 'ticket_impact':
            Ticket::dropdownImpact($opt);
            break;
        case 'ticket_urgency':
            Ticket::dropdownUrgency($opt);
            break;
        case 'ticket_priority':
            Ticket::dropdownPriority($opt);
            break;
        case 'ticket_location':
            $opt = array_merge($opt, ['entity' => $_SESSION["glpiactive_entity"]]);
            Location::dropdown($opt);
            break;
        case 'ticket_creator':
        case 'alert_creator':
            $opt = array_merge($opt, ['entity' => $_SESSION["glpiactiveentities"], 'right' => "all"]);
            User::dropdown($opt);
            break;
        case 'ticket_source':
            $opt = array_merge($opt, ['entity' => $_SESSION["glpiactive_entity"]]);
            RequestType::dropdown($opt);
            break;
        case 'entity':
            $opt = array_merge($opt, ['entity' => $_SESSION["glpiactiveentities"]]);
            Entity::dropdown($opt);
            break;
        default:
            break;
    }
}


