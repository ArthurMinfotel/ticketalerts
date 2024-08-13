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

class PluginTicketalertsAlertGroup extends CommonDBTM {

    public $dohistory = true;
    static $rightname = 'plugin_ticketalerts_manage_groupalert';

    /**
     * @param int $nb
     *
     * @return string|\translated
     */
    static function getTypeName($nb = 0) {
        return _n('Ticket alert group', 'Tickets alerts groups', $nb, 'ticketalerts');
    }

    static function canCreate() {
        return Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
    }

    static function canUpdate() {
        return Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
    }

    static function canPurge() {
        return Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
    }


    /**
     * Get menu name
     *
     * @return string
     */
    static function getMenuName() {
        return self::getTypeName();
    }


    /**
     * @return \an|array
     */
    function rawSearchOptions() {

        $tab = [];

        $tab[] = [
            'id'   => 'common',
            'name' => self::getTypeName(2)
        ];

        $tab[] = [
            'id'              => '1',
            'table'           => $this->getTable(),
            'field'           => 'name',
            'name'            => __('Name'),
            'datatype'        => 'itemlink',
            'itemlink_type'   => $this->getType()
        ];

        $tab[]= [
            'id'               => '2',
            'table'            => $this->getTable(),
            'field'            => 'comment',
            'name'             => __('Description'),
            'datatype'         => 'text'
        ];

        $tab[]= [
            'id'               => '3',
            'table'            => $this->getTable(),
            'field'            => 'date_mod',
            'massiveaction'    => false,
            'name'             => __('Last update'),
            'datatype'         => 'datetime'
        ];

        $tab[]= [
            'id'               => '4',
            'table'            => $this->getTable(),
            'field'            => 'is_recursive',
            'name'             => __('Child entities'),
            'datatype'         => 'bool'
        ];

        $tab[]= [
            'id'               => '5',
            'table'            => $this->getTable(),
            'field'            => 'id',
            'name'             => __('ID'),
            'datatype'         => 'number'
        ];

        $tab[]= [
            'id'               => '6',
            'table'            => 'glpi_entities',
            'field'            => 'completename',
            'name'             => __('Entity'),
            'datatype'         => 'dropdown'
        ];

        return $tab;
    }

    /**
     * @param CommonGLPI $item
     * @param int        $tabnum
     * @param int        $withtemplate
     *
     * @return bool
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        if ($item->getType() == __CLASS__) {
            switch ($tabnum) {
                case 1 :
                    $item->showForm($item->getID());
                    break;
                case 2 :
                    $item->showAddCriteriasForm($item->getID());
                    break;
            }
        }
        return true;
    }

    /**
     * @param CommonGLPI $item
     * @param int        $withtemplate
     *
     * @return string
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if (!$withtemplate) {
            $count  = self::getCountCriteriaByGroup($item);
            switch ($item->getType()) {
                case __CLASS__ :
                    $ong[1] = PluginTicketalertsAlertGroup::getTypeName();
                    $ong[2] = PluginTicketalertsAlertGroupCriteria::getTypeName($count) . "<sup class='tab_nb'>$count</sup>";
                    return $ong;
            }
        }
        return '';
    }

    static function getCountCriteriaByGroup($group) {
        global $DB;
        $count  = 0;
        $query  = "SELECT COUNT(`glpi_plugin_ticketalerts_alertgroupcriterias`.`id`) as total 
                      FROM `glpi_plugin_ticketalerts_alertgroupcriterias`
                      LEFT JOIN `glpi_plugin_ticketalerts_alertgroups` ON `glpi_plugin_ticketalerts_alertgroups`.`id` = 
                      `glpi_plugin_ticketalerts_alertgroupcriterias`.`plugin_ticketalerts_alertgroups_id` 
                      WHERE `glpi_plugin_ticketalerts_alertgroups`.`id` = " . $group->getID();
        $result = $DB->doQuery($query);
        while ($c = $DB->fetchAssoc($result)) {
            $count = $c['total'];
        }
        return $count;
    }


    /**
     * @param array|\datas $input
     *
     * @return array|\datas|\the
     */
    function prepareInputForAdd($input) {
        $input['name'] = isset($input['name']) ? trim($input['name']) : '';
        if (empty($input["name"])) {
            Session::addMessageAfterRedirect(__("You can't add a group without name", 'ticketalerts'),
                false, ERROR);
            return false;
        }
        return $input;
    }


    /**
     * @param array|\datas $input
     *
     * @return array|\datas|\the
     */
    function prepareInputForUpdate($input) {
        if ($this->fields['users_id'] > 0) {
            Session::addMessageAfterRedirect(__('The alert is already taken into account', 'ticketalerts'), false, ERROR);
            return [];
        }
        if (isset($input['alert_date']) && empty($input['alert_date'])) {
            $input['alert_date'] = 'NULL';
        }
        return $input;
    }

    /**
     * @see CommonDBTM::cleanDBonPurge()
     *
     * @since 0.83.1
     **/
    function cleanDBonPurge() {

        // PluginTicketalertsAlertGroupCriteria does not extends CommonDBConnexity
        $agc = new PluginTicketalertsAlertGroupCriteria();
        $agc->deleteByCriteria(['plugin_ticketalerts_alertgroups_id' => $this->fields['id']]);
    }

    /**
     * @see CommonGLPI::defineTabs()
     */
    function defineTabs($options = []) {

        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        return $ong;
    }


    function showAddCriteriasForm($ID) {
        $criteriasArr = PluginTicketalertsAlertGroupCriteria::$criterias_types;
        $rand         = mt_rand();
        $canedit      = Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);

        echo "<div class='center'>";

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr>";
        echo "<th colspan='4'>" . _n("Alert group criterion", "Alert group criteria", 'ticketalerts') . "</th>";
        echo "</tr>";
        echo '</table>';

        echo "<form method='post' action=\"".static::getFormURL()."\">";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . _n('Criterion', 'Criteria', 1, 'ticketalerts') . "</td>";
        echo "<td>";
        $rand_crit = Dropdown::showFromArray('criteria', PluginTicketalertsAlertGroupCriteria::getCriteriasNameForDropdown($criteriasArr));
        echo "</td>";
        echo "<td>" . __('Rule criterion', 'ticketalerts') . "</td>";
        echo "<td>";
        $values = ['AND' => __('And', 'ticketalerts'), 'OR' => __('Or', 'ticketalerts')];
        Dropdown::showFromArray('rule_criterion', $values);
        echo "</td>";
        echo "</tr>";
        $params = ['criteria' => '__VALUE__'];

        Ajax::updateItemOnSelectEvent("dropdown_criteria". $rand_crit, "show_filter_dropdown$rand", "../ajax/viewfiltercriterias.php", $params);
        Ajax::updateItemOnSelectEvent("dropdown_criteria". $rand_crit, "show_is_recursive_dropdown$rand", "../ajax/viewrecursivecriteria.php", $params);

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo _n('Filter', 'Filters', 0, 'ticketalerts');
        echo "</td>";
        echo "<td>";
        echo "<span id='show_filter_dropdown$rand' >";
        echo "</td>";
        echo "<td>";
        echo __('Recursive');
        echo "</td>";
        echo "<td>";
        echo "<span id='show_is_recursive_dropdown$rand' >";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo _n('Negation', 'Negations', 0, 'ticketalerts');
        echo "</td>";
        echo "<td>";
        Dropdown::showFromArray('rule_criterion_negation',PluginTicketalertsAlertGroupCriteria::getArrayCriterionNegation());
        echo "</td>";
        echo "<td>";
        echo __('Group criteria', 'ticketalerts');
        echo "</td>";
        echo "<td>";
        Dropdown::showNumber('group_number',['min' => 1, 'max']);
        echo "</td>";
        echo "</tr>";

        echo "<input type='hidden' name='plugin_ticketalerts_alertgroups_id' value='$ID'>";

        if ($canedit) {
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan='4'>";
            echo "<input type='submit' name='add_criteria_filter' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td>";
            echo "</tr>";
        }

        echo '</table>';
        Html::closeForm();
        echo "</div>";


        $group_criterias = new PluginTicketalertsAlertGroupCriteria();
        $group_criterias = $group_criterias->find(['plugin_ticketalerts_alertgroups_id' => $ID]);


        if (count($group_criterias)) {

            $nb_crit            = sizeof($group_criterias);
            $criterias_tabsname = PluginTicketalertsAlertGroupCriteria::getCriteriasNameForDropdown(PluginTicketalertsAlertGroupCriteria::$criterias_types);

            echo "<div class='center'>";

            if ($canedit) {
                Html::openMassiveActionsForm('mass' . "PluginTicketalertsAlertGroupCriteria" . $rand);
                $massiveactionparams['container'] = 'mass' . 'PluginTicketalertsAlertGroupCriteria' . $rand;
                $massiveactionparams['specific_actions'] =  ['delete' => _x('button', 'Delete permanently')] ;
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'>";
            echo "<th class='center b' colspan='10'>" . _n('Criterion', 'Criteria', $nb_crit) . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_2'>";
            echo "<th width='10'>";
            if ($canedit) {
                echo Html::getCheckAllAsCheckbox('mass' . "PluginTicketalertsAlertGroupCriteria" . $rand);
            }
            echo "</th>";
            echo "<th class='center b'>" ._n('Criterion', 'Criteria', 0) . "</th>";
            echo "<th class='center b'>" . __('Filter', 'ticketalerts') . "</th>";
            echo "<th class='center b'>" . __('Rule Criterion', 'ticketalerts') . "</th>";
            echo "<th class='center b'>" . __('Recursive') . "</th>";
            echo "<th class='center b'>" . __('Rule Criterion Negation') . "</th>";
            echo "<th class='center b'>" . __('Group criteria') . "</th>";

            foreach ($group_criterias as $id => $value) {
                echo "<tr class='tab_bg_1'>";
                echo "<td width='10'>";
                if ($canedit) {
                    Html::showMassiveActionCheckBox("PluginTicketalertsAlertGroupCriteria", $id);
                }
                echo "</td>";
                echo "<td>" . $criterias_tabsname[$value['criteria']] . "</td>";
                echo "<td>" . PluginTicketalertsAlertGroupCriteria::getFilterValueByCriterion($value) . "</td>";
                echo "<td>" . PluginTicketalertsAlertGroupCriteria::getRuleCriterionValueByCriterion($value) . "</td>";
                echo "<td>" . Dropdown::getYesNo($value['is_recursive']) . "</td>";
                echo "<td>" . PluginTicketalertsAlertGroupCriteria::getValueOfNegation($value['rule_criterion_negation']) . "</td>";
                echo "<td>" . $value['group_number'] . "</td>";

                echo "</tr>";
            }

            if ($canedit) {
                $massiveactionparams['ontop'] = false;
                $massiveactionparams['specific_actions'] =  ['delete' => _x('button', 'Delete permanently')] ;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }
            echo "</table>";
            echo "</div>";

        } else {
            echo "<div class='center first-bloc'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr  class='tab_bg_1'><td class='center'>" . __('No item to display') . "</td></tr>";
            echo "</table>";
            echo "</div>";
        }
    }

    /**
     * @param       $ID
     * @param array $options
     *
     * @return bool
     */
    function showForm($ID, $options = []) {
        global $CFG_GLPI;

        $canedit = Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "</td>";
        echo "<td>";
        Html::autocompletionTextField($this, "name");
        echo "</td>";
        echo "<td>". __('Comment') . "</td>";

        echo "<td class='center'>";
        echo "<textarea cols='90' rows='7' name='comment' >" . $this->fields["comment"] . "</textarea>";
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Group operator') . "</td>";
        echo "<td>";
        $values = ['AND' => __('And', 'ticketalerts'), 'OR' => __('Or', 'ticketalerts')];
        Dropdown::showFromArray('group_criterion', $values,['value' => $this->fields["group_criterion"]]);
        echo "</td>";
        echo "<td></td>";

        echo "<td class='center'>";

        echo "</td>";
        echo "</tr>";
        if ($canedit) {
            $this->showFormButtons($options);
        }
    }

    //Massive action
    function getSpecificMassiveActions($checkitem = null) {
        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if (Session::getCurrentInterface() == 'central') {
            if ($isadmin) {
                if (Session::haveRight('transfer', READ) && Session::isMultiEntitiesMode()) {
                    $actions['PluginTicketalertsAlertGroup' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
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
    static function showMassiveActionsSubForm(MassiveAction $ma) {

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
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
        global $DB;

        switch ($ma->getAction()) {
            case "transfer" :
                $input = $ma->getInput();
                if ($item->getType() == 'PluginTicketalertsAlertGroup') {
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
     * @return bool
     */
    static function showCentralList() {

        if (! Session::haveRight("plugin_ticketalerts_manage_groupalert", READ)) {
            return false;
        }

        $alertgroup  = new self();
        $alertgroups = $alertgroup->find();

        echo "<table class='tab_cadrehov' id='pluginTicketAlertsCentralList'>";
        echo "<tr><th colspan='5'>" . self::getTypeName();
        echo "</th></tr>";

        if (count($alertgroups) != 0) {
            echo "<tr>";
            echo "<th>" . __('Name') . "</th>";
            echo "<th>" . __('Comment') . "</th>";

            if (Session::haveRight("plugin_ticketalerts_manage_groupalert", READ)) {
                echo "<th>" . __('Action') . "</th>";
            }

            echo "</tr>";
            foreach ($alertgroups as $data) {
                echo "<tr class='tab_bg_2'>";

                echo "<td>";
                echo Html::cleanInputText($data['name']);
                echo "</td>";

                echo "<td>";
                echo Html::cleanInputText($data['comment']);
                echo "</td>";
                echo "</tr>";
            }
        }

        echo "</table>";
        echo "<br />";

    }

}
