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

function plugin_ticketalerts_install()
{
    global $DB;

    $install = false;
    include_once(GLPI_ROOT . "/plugins/ticketalerts/inc/profile.class.php");
    include_once(GLPI_ROOT . "/plugins/ticketalerts/inc/alert.class.php");
    if (!$DB->TableExists("glpi_plugin_ticketalerts_alerts")) {
        $install = true;
        $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/empty-2.0.0.sql");

        $query = "INSERT INTO `glpi_plugin_ticketalerts_alerttypes` VALUES(NULL, '" . __(
                'New comment tickets',
                'ticketalerts'
            ) . "', '');";
        $DB->doQuery($query);
        $query = "INSERT INTO `glpi_plugin_ticketalerts_alerttypes` VALUES(NULL, '" . __(
                'New incident',
                'ticketalerts'
            ) . "', '');";
        $DB->doQuery($query);
        $query = "INSERT INTO `glpi_plugin_ticketalerts_alerttypes` VALUES(NULL, '" . __(
                'New customer request',
                'ticketalerts'
            ) . "', '');";
        $DB->doQuery($query);

        if (!$DB->fieldExists("glpi_plugin_ticketalerts_alertgroupcriterias", "rule_criterion")) {
            $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/update-2.0.1.sql");
        }

        if (!$DB->fieldExists("glpi_plugin_ticketalerts_alertgroupcriterias", "rule_criterion_negation")) {
            $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/update-2.0.2.sql");
        }
    }

    if ($install) {
        $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginTicketalertsAlert' AND `name` = 'Import error'";
        $result = $DB->doQuery($query_id) or die($DB->error());
        $templateId = $DB->result($result, 0, 'id');

        $query = "INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, " . $templateId . ", '','##lang.ticketalert.title##',
                                       '##lang.ticketalert.description##

##lang.ticketalert.error## : ##ticketalert.error##',
                                       '&lt;p&gt;##lang.ticketalert.description##&lt;/p&gt;
&lt;p&gt;##lang.ticketalert.error## : ##ticketalert.error##&lt;/p&gt;');";

        $DB->doQuery($query);

        $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `comment`, `is_recursive`, `is_active`)
                                      VALUES ('Import error', 0, 'PluginTicketalertsAlert', 'importerror',
                                             '', 1, 1);";
        $DB->doQuery($query);

        $query_id = "SELECT `id` FROM `glpi_notifications` WHERE `itemtype`='PluginTicketalertsAlert' AND `name` = 'Import error'";
        $result = $DB->doQuery($query_id) or die($DB->error());
        $notificationId = $DB->result($result, 0, 'id');

        $query = "INSERT INTO `glpi_notifications_notificationtemplates`
                                      VALUES (NULL, " . $notificationId . ", 'mailing'," . $templateId . ");";
        $DB->doQuery($query);
    } else {
        if (!$DB->TableExists("glpi_plugin_ticketalerts_alertgroups") &&
            !$DB->TableExists("glpi_plugin_ticketalerts_alertgroupcriterias")) {
            $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/update-2.0.0.sql");
        }

        if (!$DB->fieldExists("glpi_plugin_ticketalerts_alertgroupcriterias", "rule_criterion")) {
            $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/update-2.0.1.sql");
        }

        if (!$DB->fieldExists("glpi_plugin_ticketalerts_alertgroupcriterias", "rule_criterion_negation")) {
            $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/update-2.0.2.sql");
        }

        if (!$DB->fieldExists("glpi_plugin_ticketalerts_alertgroupcriterias","group_criteria")) {
            $DB->runFile(GLPI_ROOT . "/plugins/ticketalerts/sql/update-2.0.3.sql");
        }
    }

    PluginTicketalertsProfile::initProfile();
    PluginTicketalertsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

    return true;
}

function plugin_ticketalerts_uninstall()
{
    global $DB;

    include_once(GLPI_ROOT . "/plugins/ticketalerts/inc/profile.class.php");
    include_once(GLPI_ROOT . "/plugins/ticketalerts/inc/menu.class.php");
    include_once(GLPI_ROOT . "/plugins/ticketalerts/inc/alert.class.php");

    $tables = [
        "glpi_plugin_ticketalerts_alerts",
        "glpi_plugin_ticketalerts_alerttypes",
        "glpi_plugin_ticketalerts_configs"
    ];

    foreach ($tables as $table) {
        $DB->doQuery("DROP TABLE IF EXISTS `$table`;");
    }

    $tables_glpi = [
        "glpi_displaypreferences",
        "glpi_notepads",
        "glpi_savedsearches",
        "glpi_logs"
    ];

    foreach ($tables_glpi as $table_glpi) {
        $DB->doQuery("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginTicketalertsAlert%';");
    }

    // Delete notifications
    $notif = new Notification();
    $options = [
        'itemtype' => 'PluginTicketalertsAlert',
        'event' => 'importerror',
        'FIELDS' => 'id'
    ];
    foreach ($DB->request('glpi_notifications', $options) as $data) {
        $notif->delete($data);
    }

    //templates
    $template = new NotificationTemplate();
    $translation = new NotificationTemplateTranslation();
    $options = [
        'itemtype' => 'PluginTicketalertsAlert',
        'FIELDS' => 'id'
    ];
    foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
        $options_template = [
            'notificationtemplates_id' => $data['id'],
            'FIELDS' => 'id'
        ];

        foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
        }
        $template->delete($data);
    }

    //Delete rights associated with the plugin
    $profileRight = new ProfileRight();
    foreach (PluginTicketalertsProfile::getAllRights() as $right) {
        $profileRight->deleteByCriteria(['name' => $right['field']]);
    }
    PluginTicketalertsMenu::removeRightsFromSession();

    PluginTicketalertsProfile::removeRightsFromSession();

    return true;
}

function plugin_ticketalerts_registerMethods()
{
    // obsolète (à l'air d'être dépendant d'un plugin qui apparemment n'est plus installé sur la prod) ?
    global $WEBSERVICES_METHOD;

    $WEBSERVICES_METHOD['ticketalerts.addAlert']
        = array('PluginTicketalertsAlert', 'methodAddAlertWS');
    $WEBSERVICES_METHOD['ticketalerts.getAlert']
        = array('PluginTicketalertsAlert', 'methodGetAlertWS');
}

// Define dropdown relations
function plugin_ticketalerts_getDatabaseRelations()
{
    $plugin = new Plugin();

    if ($plugin->isActivated("ticketalerts")) {
        return [
            "glpi_plugin_ticketalerts_alerttypes" => ["glpi_plugin_ticketalerts_alerts" => "plugin_ticketalerts_alerttypes_id"],
            "glpi_users" => ["glpi_plugin_ticketalerts_alerts" => "users_id"],
            "glpi_entities" => ["glpi_plugin_ticketalerts_alerts" => "entities_id"],
            "glpi_tickets" => ["glpi_plugin_ticketalerts_alerts" => "tickets_id"]
        ];
    } else {
        return [];
    }
}

// Define Dropdown tables to be manage in GLPI :
function plugin_ticketalerts_getDropdown()
{
    $plugin = new Plugin();

    if ($plugin->isActivated("ticketalerts")) {
        return ['PluginTicketalertsAlertType' => PluginTicketalertsAlertType::getTypeName(2)];
    } else {
        return [];
    }
}

function plugin_ticketalerts_display_central()
{
    echo "<tr><td>";
    PluginTicketalertsAlert::showCentralList();
    echo "</tr></td>";
}
