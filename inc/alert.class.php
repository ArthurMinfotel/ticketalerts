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

class PluginTicketalertsAlert extends CommonDBTM
{

    public $dohistory = true;
    static $rightname = 'plugin_ticketalerts';
    protected $usenotepad = true;
    static $types = [];
    const TODO = 1;
    const DONE = 2;

    /**
     * @param int $nb
     *
     * @return string|\translated
     */
    static function getTypeName($nb = 0)
    {
        return _n('Ticket alert', 'Tickets alerts', $nb, 'ticketalerts');
    }

    /**
     * @return \an|array
     */
    function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => self::getTypeName(2)
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __('Name'),
            'datatype' => 'itemlink',
            'itemlink_type' => $this->getType()
        ];

        $tab[] = [
            'id' => '8',
            'table' => 'glpi_plugin_ticketalerts_alerttypes',
            'field' => 'name',
            'name' => _n('Alert type', 'Alert Types', 1, 'ticketalerts'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '3',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'users_id',
            'name' => __('User'),
            'datatype' => 'dropdown'
        ];
        $tab[] = [
            'id' => '33',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'users_id_requester',
            'name' => __('Alert creator', 'ticketalert'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '4',
            'table' => 'glpi_tickets',
            'field' => 'id',
            'linkfield' => 'tickets_id',
            'name' => __('Ticket'),
            'datatype' => 'itemlink',
            'itemlink_type' => $this->getType()
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'taking_into_account_date',
            'massiveaction' => false,
            'name' => __('Date of taking into account', 'ticketalerts'),
            'datatype' => 'datetime'
        ];

        $tab[] = [
            'id' => '6',
            'table' => $this->getTable(),
            'field' => 'alert_date',
            'name' => __('Alert date', 'ticketalerts'),
            'datatype' => 'datetime'
        ];

        $tab[] = [
            'id' => '7',
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __('Description'),
            'datatype' => 'text'
        ];

        $tab[] = [
            'id' => '11',
            'table' => $this->getTable(),
            'field' => 'state',
            'name' => __('Status'),
            'searchtype' => 'equals',
            'datatype' => 'specific'
        ];

        $tab[] = [
            'id' => '12',
            'table' => $this->getTable(),
            'field' => 'date_mod',
            'massiveaction' => false,
            'name' => __('Last update'),
            'datatype' => 'datetime'
        ];

        $tab[] = [
            'id' => '18',
            'table' => $this->getTable(),
            'field' => 'is_recursive',
            'name' => __('Child entities'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => '30',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'datatype' => 'number'
        ];

        $tab[] = [
            'id' => '80',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => __('Entity'),
            'datatype' => 'dropdown'
        ];

        return $tab;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Notepad', $ong, $options);
        $this->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    /**
     * @param array|\datas $input
     *
     * @return array|\datas|\the
     */
    function prepareInputForAdd($input)
    {
        //check mandatory field
        $this->methodAddAlert($input);

        if (isset($input['alert_date']) && empty($input['alert_date'])) {
            $input['alert_date'] = 'NULL';
        }

        if (!isset($input['users_id_requester'])) {
            $input['users_id_requester'] = Session::getLoginUserID();
        }

        return $input;
    }

    /**
     * @return \nothing|\type|void
     */
    function post_addItem()
    {
        $input['id'] = $this->getID();
        $input["name"] = __('Alert', 'ticketalerts') . " " . $this->getID() . " " . __(
                'Ticket'
            ) . " " . $this->getField("tickets_id");

        $this->update($input);

        // Return the newly created object
        return PluginTicketalertsAlert::methodGetAlert(['id' => $this->getID()]);
    }

    /**
     * @param array|\datas $input
     *
     * @return array|\datas|\the
     */
    function prepareInputForUpdate($input)
    {
        if ($this->fields['users_id'] > 0) {
            Session::addMessageAfterRedirect(
                __('The alert is already taken into account', 'ticketalerts'),
                false,
                ERROR
            );
            return [];
        }

        if (isset($input['alert_date']) && empty($input['alert_date'])) {
            $input['alert_date'] = 'NULL';
        }

        return $input;
    }

    /**
     * @param       $ID
     * @param array $options
     *
     * @return bool
     */
    function showForm($ID, $options = [])
    {
        $options['canedit'] = false;
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        echo Html::input(
            'name',
            [
                'type' => 'text',
                'value' => $this->fields['name']
            ]
        );
        echo "</td>";

        echo "<td>" . _n('Alert type', 'Alert Types', 1, 'ticketalerts') . "</td><td>";
        Dropdown::show('PluginTicketalertsAlertType', [
            'name' => "plugin_ticketalerts_alerttypes_id",
            'value' => $this->fields["plugin_ticketalerts_alerttypes_id"],
            'entity' => $this->fields["entities_id"]
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __('User') . "</td><td>";
        User::dropdown([
            'name' => "users_id",
            'value' => $this->fields["users_id"],
            'entity' => $this->fields["entities_id"],
            'right' => 'interface'
        ]);
        echo "</td>";

        $options = [];
        $options['maybeempty'] = true;
        $options['display'] = true;
        $options['value'] = $this->fields["alert_date"];
        echo "<td>" . __('Alert date', 'ticketalerts');
        echo "</td>";
        echo "<td>";
        Html::showDateField("alert_date", $options);
        echo "</td>";

        echo "</tr>";
        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __('Alert creator', 'ticketalert') . "</td><td>";
//      User::dropdown(['name' => "users_id_requester",
//         'value' => $this->fields["users_id_requester"],
//         'entity' => $this->fields["entities_id"],
//         'right' => 'interface']);
        echo getUserName($this->fields["users_id_requester"]);
        echo "</td>";

        echo "<td>";
        echo "</td>";
        echo "<td>";
        echo "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>" . __('Ticket') . "</td>";
        $ticket = new Ticket();
        echo "<td>";
        if ($ticket->getFromDB($this->fields["tickets_id"])) {
            echo $ticket->getLink();
        }
        echo "</td>";

        echo "<td>" . __('Status') . "</td><td>";
        self::dropdownState("state", $this->fields["state"]);
        echo "&nbsp;";
        if ($this->fields["state"] == self::DONE) {
            printf(__('The %s', 'ticketalerts'), Html::convDateTime($this->fields["taking_into_account_date"]));
        }
        echo "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";

        echo "<td>";
        echo __('Description') . "</td>";
        echo "<td colspan = '3' class='center'>";
        echo "<textarea cols='100' rows='7' name='comment' >" . $this->fields["comment"] . "</textarea>";
        echo "</td>";

        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='center' colspan='4'>";
        printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
        echo "</td>";
        echo "</tr>";

        $this->showFormButtons($options);

        return true;
    }

    /**
     * Get the standard massive actions which are forbidden
     *
     * @return an array of massive actions
     **@since version 0.84
     *
     */
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }

    /**
     * Get planning state name
     *
     * @param $value status ID
     **/
    static function getState($value)
    {
        switch ($value) {
            case static::TODO :
                return __('To take into account', 'ticketalerts');

            case static::DONE :
                return __('Taken into account', 'ticketalerts');
        }
    }


    /**
     * Dropdown of planning state
     *
     * @param $name   select name
     * @param $value  default value (default '')
     * @param $display  display of send string ? (true by default)
     **/
    static function dropdownState($name, $value = '', $display = true)
    {
        $values = [
            static::TODO => __('To take into account', 'ticketalerts'),
            static::DONE => __('Taken into account', 'ticketalerts')
        ];

        return Dropdown::showFromArray($name, $values, [
            'value' => $value,
            'display' => $display
        ]);
    }

    //Massive action
    function getSpecificMassiveActions($checkitem = null)
    {
        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if (Session::getCurrentInterface() == 'central') {
            if ($isadmin) {
                if (Session::haveRight('transfer', READ) && Session::isMultiEntitiesMode()
                ) {
                    $actions['PluginTicketalertsAlert' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __(
                        'Transfer'
                    );
                }
            }
        }
        return $actions;
    }

    /**
     * @since version 0.85
     *
     * @see CommonDBTM::showMassiveActionsSubForm()
     * */
    static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case "transfer" :
                Dropdown::show('Entity');
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
                break;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    /**
     * @since version 0.85
     *
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     * */
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        global $DB;

        switch ($ma->getAction()) {
            case "transfer" :
                $input = $ma->getInput();
                if ($item->getType() == 'PluginTicketalertsAlert') {
                    foreach ($ids as $key) {
                        $values["id"] = $key;
                        $values["entities_id"] = $input['entities_id'];

                        if ($item->update($values)) {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    /**
     * For other plugins, add a type to the linkable types
     *
     * @param $type string class name
     * *@since version 1.3.0
     *
     */
    static function registerType($type)
    {
        if (!in_array($type, self::$types)) {
            self::$types[] = $type;
        }
    }

    /**
     * Type than could be linked to a Rack
     *
     * @param $all boolean, all type, or only allowed ones
     *
     * @return array of types
     * */
    static function getTypes($all = false)
    {
        if ($all) {
            return self::$types;
        }

        // Only allowed types
        $types = self::$types;

        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                continue;
            }

            $item = new $type();
            if (!$item->canView()) {
                unset($types[$key]);
            }
        }
        return $types;
    }

    /**
     * display a value according to a field
     *
     * @param $field     String         name of the field
     * @param $values    String / Array with the value to display
     * @param $options   Array          of option
     *
     * @return a string
     **@since version 0.83
     *
     */
    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        switch ($field) {
            case 'state' :
                return self::getState($values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }

    /**
     * @param $field
     * @param $name (default '')
     * @param $values (default '')
     * @param $options   array
     *
     * @return string
     **@since version 0.84
     *
     */
    static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'state':
                return self::dropdownState($name, $values[$field], false);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }


    /**
     * @return bool
     */
    static function showCentralList()
    {
        global $CFG_GLPI, $DB;

        if (!Session::haveRight("plugin_ticketalerts", READ)) {
            return false;
        }

        echo "<table class='tab_cadrehov' id='pluginTicketAlertsCentralList'>";
        echo "<tr>";
        echo "<th colspan='5'>" . _n('Ticket alert', 'Tickets alerts', 2, 'ticketalerts');
        echo "</th>";
        echo "</tr>";

        $alert = new self();
        $alerts = $alert->find(['users_id' => 0, 'is_deleted' => 0], "`alert_date` DESC");

        if (count($alerts) != 0) {
            $count_tab = 2;
            $group_alert = new PluginTicketalertsAlertGroup();
            $groups_alert = $group_alert->find();
            $groups_alert_criterias = [];

            if (count($groups_alert) > 0) {
                foreach ($groups_alert as $group_alert) {
                    $group_alert_criterias = new PluginTicketalertsAlertGroupCriteria();
                    $group_alert_criterias = $group_alert_criterias->find(
                        ['plugin_ticketalerts_alertgroups_id' => $group_alert['id']],
                        ['`criteria` ASC, `rule_criterion` ASC']
                    );
                    if (count($group_alert_criterias) > 0) {
                        foreach ($group_alert_criterias as $group_alert_criteria) {
                            $groups_alert_criterias[$group_alert['id']][] = $group_alert_criteria;
                        }
                    } else {
                        $groups_alert_criterias[$group_alert['id']] = [];
                    }
                }
            }

            $alerts_by_group = self::getAlertWithGroupCriteria($groups_alert_criterias);
            echo "<th colspan='5'>";
            echo "<ul id='personal-tabs'>";
            echo "<li><a id='tab1'>" . __('All') . " (" . count($alerts) . ")</a></li>";
            foreach ($alerts_by_group as $group => $alert) {
                $group_alert = new PluginTicketalertsAlertGroup();
                $group_alert->getFromDB($group);
                $count = is_array($alert) ? count($alert) : 0;
                echo " <li><a id='tab$count_tab'>" . $group_alert->getField('name') . " (" . $count . ")</a></li>";
                $count_tab++;
            }
            echo " </ul>";
            echo "</th>";

            echo "<tr>";
            echo "<th>" . __('Alert date', 'ticketalerts') . "</th>";
            echo "<th>" . __('Case number', 'ticketalerts') . "</th>";
            echo "<th>" . __('Case type', 'ticketalerts') . "</th>";
            echo "<th>" . _n('Alert type', 'Alert Types', 1, 'ticketalerts') . "</th>";

            if (Session::haveRight("plugin_ticketalerts", UPDATE)) {
                echo "<th>" . __('Action') . "</th>";
            }
            echo "</tr>";
            // all alerts in one tab
            $number_tab = self::displayAllAlertOrByGroup($alerts, false, 1);
            //  alerts by group and by tab
            self::displayAllAlertOrByGroup($alerts_by_group, true, $number_tab);

            echo Html::scriptBlock(
                "  $(function() {

               $('#personal-tabs li a:not(:first)').addClass('inactive');
               $('.container').hide();
               $('.container:first').show();

               $('#personal-tabs li a').click(function(){
                   var t = $(this).attr('id');
                     if($(this).hasClass('inactive')) {
                         $('#personal-tabs li a').addClass('inactive');
                         $(this).removeClass('inactive');
                         $('.container').hide();
                         $('#'+ t + 'C').fadeIn('slow');
                }
               });

         });"
            );
        }

        echo "</table>";
        echo "<br />";
    }

    /**
     * @return bool
     */
    static function getCentralList($id)
    {
        global $CFG_GLPI, $DB;

        $return = "";
        if (!Session::haveRight("plugin_ticketalerts", READ)) {
            return false;
        }

        $return .= "<table class='tab_cadrehov' id='pluginTicketAlertsCentralList'>";
        $return .= "<tr>";
        $return .= "<th colspan='5'>" . _n('Ticket alert', 'Tickets alerts', 2, 'ticketalerts');
        $return .= "</th>";
        $return .= "</tr>";

        $alert = new self();
        $alerts = $alert->find(['users_id' => 0, 'is_deleted' => 0], "`alert_date` DESC");

        if (count($alerts) != 0) {
            $count_tab = 2;
            $group_alert = new PluginTicketalertsAlertGroup();
            $groups_alert = $group_alert->find();
            $groups_alert_criterias = [];

            if (count($groups_alert) > 0) {
                foreach ($groups_alert as $group_alert) {
                    $group_alert_criterias = new PluginTicketalertsAlertGroupCriteria();
                    $group_alert_criterias = $group_alert_criterias->find(
                        ['plugin_ticketalerts_alertgroups_id' => $group_alert['id']],
                        ['`criteria` ASC, `rule_criterion` ASC']
                    );
                    if (count($group_alert_criterias) > 0) {
                        foreach ($group_alert_criterias as $group_alert_criteria) {
                            $groups_alert_criterias[$group_alert['id']][] = $group_alert_criteria;
                        }
                    } else {
                        $groups_alert_criterias[$group_alert['id']] = [];
                    }
                }
            }

            $alerts_by_group = self::getAlertWithGroupCriteria($groups_alert_criterias);
            $return .= "<th colspan='5'>";
            $return .= "<ul id='personal-tabs'>";
            $return .= "<li><a id='tab1'>" . __('All') . " (" . count($alerts) . ")</a></li>";
            foreach ($alerts_by_group as $group => $alert) {
                $group_alert = new PluginTicketalertsAlertGroup();
                $group_alert->getFromDB($group);
                $count = is_array($alert) ? count($alert) : 0;
                $return .= " <li><a id='tab$count_tab'>" . $group_alert->getField(
                        'name'
                    ) . " (" . $count . ")</a></li>";
                $count_tab++;
            }
            $return .= " </ul>";
            $return .= "</th>";

            $return .= "<tr>";
            $return .= "<th>" . __('Alert date', 'ticketalerts') . "</th>";
            $return .= "<th>" . __('Case number', 'ticketalerts') . "</th>";
            $return .= "<th>" . __('Case type', 'ticketalerts') . "</th>";
            $return .= "<th>" . _n('Alert type', 'Alert Types', 1, 'ticketalerts') . "</th>";

            if (Session::haveRight("plugin_ticketalerts", UPDATE)) {
                $return .= "<th>" . __('Action') . "</th>";
            }
            $return .= "</tr>";
            // all alerts in one tab
            $array = self::displayAllAlertOrByGroup($alerts, false, 1, false);
            $number_tab = $array['tab'];
            $return .= $array['display'];
            //  alerts by group and by tab
            $array = self::displayAllAlertOrByGroup($alerts_by_group, true, $number_tab, false);
            $number_tab = $array['tab'];
            $return .= $array['display'];
            $return .= Html::scriptBlock(
                "  $(function() {

               $('#personal-tabs li a:not(#$id)').addClass('inactive');

               $('.container').hide();
               $('#{$id}C').show();

               $('#personal-tabs li a').click(function(){
                   var t = $(this).attr('id');
                     if($(this).hasClass('inactive')) {
                         $('#personal-tabs li a').addClass('inactive');
                         $(this).removeClass('inactive');
                         $('.container').hide();
                         $('#'+ t + 'C').fadeIn('slow');
                }
               });

         });"
            );
        }

        $return .= "</table>";
        $return .= "<br />";

        return $return;
    }

    static function displayAlertDetails($data, $display = true)
    {
        global $CFG_GLPI;
        $return = '';
        $return .= "<tr class='tab_bg_2'>";
        $return .= "<td>";
        $return .= Html::convDateTime($data['alert_date']);
        $return .= "</td>";

        $ticket = new Ticket();
        $return .= "<td>";
        if ($ticket->getFromDB($data["tickets_id"])) {
            $return .= $ticket->getLink();
            $return .= "&nbsp;(" . $ticket->getID() . ")";
        }
        $return .= "</td>";

        $return .= "<td>";
        if ($ticket->getFromDB($data["tickets_id"])) {
            $return .= Ticket::getTicketTypeName($ticket->fields["type"]);
        }
        $return .= "</td>";

        $return .= "<td>";
        $return .= Dropdown::getDropdownName(
            "glpi_plugin_ticketalerts_alerttypes",
            $data["plugin_ticketalerts_alerttypes_id"]
        );
        $return .= "</td>";

        if (Session::haveRight("plugin_ticketalerts", UPDATE)) {
            $return .= "<td>";
            $return .= "<a class='vsubmit center'
                           onclick=\" submitAlert('" . $CFG_GLPI['root_doc'] . "/plugins/ticketalerts/front/alert.form.php',
                           {'done': 'done', 'id': '" . $data['id'] . "','tickets_id' : '" . $data['tickets_id'] . "' ,
                           '_glpi_csrf_token': '" . Session::getNewCSRFToken(
                ) . "', '_glpi_simple_form': '1'});\">" . __('To take into account', 'ticketalerts') . "</a>";
            $return .= "</td>";
        }
        $return .= "</tr>";
        if ($display) {
            echo $return;
        } else {
            return $return;
        }
    }

    static function displayAllAlertOrByGroup($alerts, $by_group, $number_tab, $display = true)
    {
        $return = "";
        if (!$by_group) {
            $return .= "<tbody  class='container' id='tab{$number_tab}C'>";
            foreach ($alerts as $data) {
                $return .= self::displayAlertDetails($data, false);
            }
            $return .= "</tbody>";
        } else {
            foreach ($alerts as $alert) {
                $return .= "<tbody  class='container' id='tab{$number_tab}C'>";
                $number_tab++;
                if (is_array($alert) > 0 && count($alert)) {
                    foreach ($alert as $data) {
                        $return .= self::displayAlertDetails($data, false);
                    }
                } else {
                    $return .= "<td colspan='5'>";
                    $return .= "<span>";
                    $return .= "<i class='fa fa-exclamation-triangle fa-1x' style='color:rgb(254,201,92);' alt='warning'></i>&nbsp&nbsp";
                    $return .= $alert;
                    $return .= "</span>";
                    $return .= "</td>";
                }
            }
        }

        //in case no datas
        $number_tab++;

        if ($display) {
            echo $return;
            return $number_tab;
        } else {
            return ['tab' => $number_tab, 'display' => $return];
        }
    }

    static function getAlertWithGroupCriteria($groups_alert_criterias)
    {
        global $DB;

        $alerts_by_group = [];

        // group by filter and = or in case
        foreach ($groups_alert_criterias as $group => $criteria) {
            $where = '';
            $where_array = [];
            if (count($criteria) > 0) {
                foreach ($criteria as $key => $value) {
                    $field = PluginTicketalertsAlertGroupCriteria::giveWhereClauseByCriteria(
                        $value['criteria']
                    );
                    if ($value['is_recursive'] == 1 && in_array($value['criteria'], PluginTicketalertsAlertGroupCriteria::$recursive_criterias)) {
                        $negation = "";
                        if ($value['rule_criterion_negation'] != "AFFIRMATION") {
                            $negation = "NOT";
                        }
                        switch($value['criteria']) {
                            case 'itilcategory' :
                                $table = 'glpi_itilcategories';
                                break;
                            case 'entity' :
                                $table = 'glpi_entities';
                                break;
                            case 'ticket_location' :
                                $table = 'glpi_locations';
                                break;
                        }
                        $childrens = getSonsOf($table, $value['filter']);
                        $where_array[$value['group_number']][$field][] = [
                            'link' => $value['rule_criterion'],
                            'condition' => ' ' . $negation . ' IN (' . implode(
                                    ',',
                                    $childrens
                                ) . ")",
                            'rule_criterion_negation' => $value['rule_criterion_negation']
                        ];
                    } else {
                        $negation = " = ";
                        if ($value['rule_criterion_negation'] != "AFFIRMATION") {
                            $negation = " != ";
                        }
                        $where_array[$value['group_number']][$field][] = [
                            'link' => $value['rule_criterion'],
                            'condition' => $negation . $value['filter'],
                            'rule_criterion_negation' => $value['rule_criterion_negation']
                        ];
                    }
                }
                $countGroup = 0;
                $alert_group = new PluginTicketalertsAlertGroup();
                $alert_group->getFromDB($group);
                $operator = $alert_group->fields['group_criterion'];
                foreach ($where_array as $group_number => $criterias) {
                    $countCriterias = 0;
                    // not the first group for the research, add operator between it and the previous group
                    if ($countGroup != 0) {
                        $where .= ") $operator (";
                    } else {
                        // open group
                        $where .= "(";
                    }
                    foreach ($criterias as $type => $value) {
                        foreach ($value as $position => $condition) {
                            // first condition on this criteria in the group
                            if ($position == 0) {
                                // separate criterias in the group if its not the first criteria in the group
                                if ($countCriterias != 0) {
                                    if ($condition['rule_criterion_negation'] == 'AFFIRMATION') {
                                        $where .= " AND ";
                                    } else {
                                        $where .= " OR ";
                                    }
                                }
                                // open the section, no link needed
                                $where .=  '(' . $type . ' ' . $condition['condition'];
                            } else {
                                $where .= ' ' . $condition['link'] . ' ' . $type . ' ' . $condition['condition'];
                            }
                            // last condition for this criteria in the group, end the section
                            if ($position == (count($value) - 1)) {
                                $where .= ')';
                            }
                        }
                        $countCriterias++;
                    }
                    $countGroup++;
                }
                $where .= ")";

                $query = "   SELECT `glpi_plugin_ticketalerts_alerts`.`alert_date`,
                             `glpi_plugin_ticketalerts_alerts`.`tickets_id`,
                             `glpi_plugin_ticketalerts_alerts`.`id`,
                             `glpi_plugin_ticketalerts_alerts`.`plugin_ticketalerts_alerttypes_id`,
                             `glpi_tickets`.`type`
                      FROM   `glpi_plugin_ticketalerts_alerts`
                      LEFT JOIN  `glpi_tickets` ON `glpi_tickets`.`id` = `glpi_plugin_ticketalerts_alerts`.`tickets_id`
                      LEFT JOIN  `glpi_plugin_ticketalerts_alerttypes` ON `glpi_plugin_ticketalerts_alerttypes`.`id` =
                                       `glpi_plugin_ticketalerts_alerts`.`plugin_ticketalerts_alerttypes_id`
                      WHERE `glpi_plugin_ticketalerts_alerts`.`users_id` = 0 AND `glpi_tickets`.`is_deleted` = 0
                      AND `glpi_plugin_ticketalerts_alerts`.`is_deleted` = 0  AND ({$where})";

                $result = $DB->doQuery($query);

                if ($result->num_rows > 0) {
                    while ($alert = $DB->fetchAssoc($result)) {
                        $alerts_by_group[$group][] = $alert;
                    }
                } else {
                    //No datas
                    $alerts_by_group[$group] = __("No datas for the this group", "ticketalerts");
                }
            } else {
                //No criterion
                $alerts_by_group[$group] = __("No criteria have been setup for this group", "ticketalerts");
            }
        }

        return $alerts_by_group;
    }

    /**
     * methodAddAlert : Add alert
     *
     * @param array $params
     * @return type
     * @global array|string[]|void
     */
    static function methodAddAlert($params)
    {
        $alert = new PluginTicketalertsAlert();
        $api = new Glpi\Api\APIRest;

        if (!isset($params['tickets_id'])) {
            $options = [
                'typeerror' => __('Missing parameter', 'ticketalerts'),
                'error' => "tickets_id",
                'params' => $params
            ];
            self::sendNotification($alert, $options);
            return $api->returnError("ticket_id is missing");
        }

        if (!isset($params['plugin_ticketalerts_alerttypes_id'])) {
            $options = [
                'typeerror' => __('Missing parameter', 'ticketalerts'),
                'error' => "plugin_ticketalerts_alerttypes_id",
                'params' => $params
            ];
            self::sendNotification($alert, $options);
            return $api->returnError("plugin_ticketalerts_alerttypes_id is missing");
        }

        if (isset($params['alert_date']) && DateTime::createFromFormat('Y-m-d G:i:s', $params['alert_date']) == false) {
            $options = [
                'typeerror' => __('Bad parameter', 'ticketalerts'),
                'error' => "alert_date",
                'params' => $params
            ];
            self::sendNotification($alert, $options);
            return $api->returnError("alert_date is missing");
        }

        if (isset($params['tickets_id']) && !is_numeric($params['tickets_id'])) {
            $options = [
                'typeerror' => __('Bad parameter', 'ticketalerts'),
                'error' => "tickets_id",
                'params' => $params
            ];
            self::sendNotification($alert, $options);
            return $api->returnError("tickets_id must be an integer");
        }

        if (isset($params['plugin_ticketalerts_alerttypes_id'])) {
            $type = new PluginTicketalertsAlertType();
            if (!$type->getFromDB($params["plugin_ticketalerts_alerttypes_id"])) {
                $options = [
                    'typeerror' => __('Alert type not found into GLPI', 'ticketalerts'),
                    'error' => "plugin_ticketalerts_alerttypes_id",
                    'params' => $params
                ];

                self::sendNotification($alert, $options);
                return $api->returnError("plugin_ticketalerts_alerttypes_id not in bdd");
            }
        }

        $ticket = new Ticket();
        if (isset($params["tickets_id"]) && !$ticket->getFromDB($params["tickets_id"])) {
            $options = [
                'typeerror' => __('Bad parameter', 'ticketalerts'),
                'error' => "tickets_id",
                'params' => $params
            ];
            self::sendNotification($alert, $options);
            return $api->returnError("Ticket not exist");
        }
        if (!$alert->cancreate()) {
            return $api->messageRightError();
        }
    }

    /**
     * methodGetAlert : Get alert
     *
     * @param type $params
     * @return type
     * @global type $DB
     */
    static function methodGetAlert($params)
    {
        $api = new Glpi\Api\APIRest;

        if (!isset($params['id'])) {
            return $api->returnError("ID not defined");
        }

        $alert = new PluginTicketalertsAlert();
        $found = false;

        if (isset($params['id'])) {
            $found = $alert->getFromDB(intval($params['id']));
        }

        if (!$alert->can($params['id'], READ)) {
            return $api->messageRightError();
        }

        if (!$found) {
            return $api->returnError("Alert not found");
        }
        return $api->returnResponse($alert->fields);
    }

    /** Notification raised when an error occured
     *
     * @param $item
     * @param $options
     */
    static function sendNotification($item, $options)
    {
        global $CFG_GLPI;
        //send notification
        if ($CFG_GLPI["notifications_mailing"]) {
            NotificationEvent::raiseEvent('importerror', $item, $options);
        }
    }

}
