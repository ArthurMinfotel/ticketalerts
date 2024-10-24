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

if (isset($_REQUEST['id']) && isset($_REQUEST['value']) && isset($_REQUEST['groups'])) {
    $group = new PluginTicketalertsAlertGroup();
    $group->getFromDB($_REQUEST['id']);
    $criteria = new PluginTicketalertsAlertGroupCriteria();
    $criterias = $criteria->find(
        ['plugin_ticketalerts_alertgroups_id' => $group->getID()],
        ['`group_number` ASC, `rank` ASC, `criteria` ASC, `rule_criterion` ASC']
    );
    $groupsOperators = $group->getGroupsOperatorsArray($criterias);
    if (isset($groupsOperators[$_REQUEST['groups']])) {
        $groupsOperators[$_REQUEST['groups']] = $_REQUEST['value'];
        if ($group->update([
            'id' => $group->getID(),
            'groups_operators' => json_encode($groupsOperators)
        ])) {
            Session::addMessageAfterRedirect(
                sprintf(__('Operator for groups %s updated', 'ticketalerts'), $_REQUEST['groups'])
            );
        } else {
            Session::addMessageAfterRedirect(
                sprintf(__('Error when updating operator for groups %s', 'ticketalerts'), $_REQUEST['groups']),
                ERROR
            );
        }
    }
}
