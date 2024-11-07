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

class PluginTicketalertsAlertGroup extends CommonDBTM
{

    public $dohistory = true;
    static $rightname = 'plugin_ticketalerts_manage_groupalert';

    const GROUP_COLORS = [
        1 => 'rgb(0 73 73)',
        2 => 'rgb(0 146 146)',
        3 => 'rgb(0 109 219)',
        4 => 'rgb(219 109 0)',
        5 => 'rgb(255 182 0)',
        6 => 'rgb(146 0 146)',
        7 => 'rgb(73 0 146)',
        8 => 'rgb(109 73 0)',
        9 => 'rgb(182 219 0)'
    ];

    /**
     * @param int $nb
     *
     * @return string|\translated
     */
    static function getTypeName($nb = 0)
    {
        return _n('Ticket alert group', 'Tickets alerts groups', $nb, 'ticketalerts');
    }

    static function canCreate()
    {
        return Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
    }

    static function canUpdate()
    {
        return Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
    }

    static function canPurge()
    {
        return Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
    }


    /**
     * Get menu name
     *
     * @return string
     */
    static function getMenuName()
    {
        return self::getTypeName();
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
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __('Description'),
            'datatype' => 'text'
        ];

        $tab[] = [
            'id' => '3',
            'table' => $this->getTable(),
            'field' => 'date_mod',
            'massiveaction' => false,
            'name' => __('Last update'),
            'datatype' => 'datetime'
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'is_recursive',
            'name' => __('Child entities'),
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'datatype' => 'number'
        ];

        $tab[] = [
            'id' => '6',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => __('Entity'),
            'datatype' => 'dropdown'
        ];

        return $tab;
    }

    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     *
     * @return bool
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
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
     * @param int $withtemplate
     *
     * @return string
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$withtemplate) {
            $count = self::getCountCriteriaByGroup($item);
            switch ($item->getType()) {
                case __CLASS__ :
                    $ong[1] = PluginTicketalertsAlertGroup::getTypeName();
                    $ong[2] = PluginTicketalertsAlertGroupCriteria::getTypeName(
                            $count
                        ) . "<sup class='tab_nb'>$count</sup>";
                    return $ong;
            }
        }
        return '';
    }

    static function getCountCriteriaByGroup($group)
    {
        global $DB;
        $count = 0;
        $query = "SELECT COUNT(`glpi_plugin_ticketalerts_alertgroupcriterias`.`id`) as total 
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
     * @see CommonDBTM::cleanDBonPurge()
     *
     * @since 0.83.1
     **/
    function cleanDBonPurge()
    {
        // PluginTicketalertsAlertGroupCriteria does not extends CommonDBConnexity
        $agc = new PluginTicketalertsAlertGroupCriteria();
        $agc->deleteByCriteria(['plugin_ticketalerts_alertgroups_id' => $this->fields['id']]);
    }

    /**
     * @see CommonGLPI::defineTabs()
     */
    function defineTabs($options = [])
    {
        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);
        return $ong;
    }


    function showAddCriteriasForm($ID)
    {
        $criteriasArr = PluginTicketalertsAlertGroupCriteria::$criterias_types;
        $rand = mt_rand();
        $canedit = Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);

        echo "<div class='center'>";

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr>";
        echo "<th colspan='4'>" . _n("Alert group criterion", "Alert group criteria", 'ticketalerts') . "</th>";
        echo "</tr>";
        echo '</table>';

        echo "<form method='post' action=\"" . static::getFormURL() . "\">";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Rule criterion', 'ticketalerts') . "</td>";
        echo "<td>";
        $values = ['AND' => __('And', 'ticketalerts'), 'OR' => __('Or', 'ticketalerts')];
        Dropdown::showFromArray('rule_criterion', $values);
        echo "</td>";
        echo "<td>" . _n('Criterion', 'Criteria', 1, 'ticketalerts') . "</td>";
        echo "<td>";
        $rand_crit = Dropdown::showFromArray(
            'criteria',
            PluginTicketalertsAlertGroupCriteria::getCriteriasNameForDropdown($criteriasArr)
        );
        echo "</td>";
        echo "</tr>";
        $params = ['criteria' => '__VALUE__'];

        Ajax::updateItemOnSelectEvent(
            "dropdown_criteria" . $rand_crit,
            "show_filter_dropdown$rand",
            "../ajax/viewfiltercriterias.php",
            $params
        );
        Ajax::updateItemOnSelectEvent(
            "dropdown_criteria" . $rand_crit,
            "show_is_recursive_dropdown$rand",
            "../ajax/viewrecursivecriteria.php",
            $params
        );

        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo _n('Negation', 'Negations', 0, 'ticketalerts');
        echo "</td>";
        echo "<td>";
        Dropdown::showFromArray(
            'rule_criterion_negation',
            PluginTicketalertsAlertGroupCriteria::getArrayCriterionNegation()
        );
        echo "</td>";
        echo "<td>";
        echo _n('Filter', 'Filters', 0, 'ticketalerts');
        echo "</td>";
        echo "<td>";
        echo "<span id='show_filter_dropdown$rand' >";
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>";
        echo __('Recursive');
        echo "</td>";
        echo "<td>";
        echo "<span id='show_is_recursive_dropdown$rand' >";
        echo "</td>";
        echo "<td>";
        echo __('Group criteria', 'ticketalerts');
        echo "</td>";
        echo "<td>";
        $groups = [];
        // create array of groups ranging from 1.1 to 9.10, no int
        for ($main = 1; $main < 10; $main++) {
            for ($sub = 1; $sub < 11; $sub++) {
                $groups[$main.'_'.$sub] = $main.'.'.$sub;
            }
        }
        Dropdown::showFromArray(
            'group_number',
            $groups
        );
        echo "</td>";
        echo "</tr>";

        echo "<input type='hidden' name='plugin_ticketalerts_alertgroups_id' value='$ID'>";

        if ($canedit) {
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan='4'>";
            echo "<input type='submit' name='add_criteria_filter' value=\"" . _sx(
                    'button',
                    'Add'
                ) . "\" class='submit'>";
            echo "</td>";
            echo "</tr>";
        }

        echo '</table>';
        Html::closeForm();
        echo "</div>";


        $self = new self();
        $self->getFromDB($ID);

        $group_criterias = new PluginTicketalertsAlertGroupCriteria();
        $group_criterias = $group_criterias->find(
            ['plugin_ticketalerts_alertgroups_id' => $ID],
            ['`group_number` ASC, `rank` ASC, `criteria` ASC, `rule_criterion` ASC']
        );


        if (count($group_criterias)) {
            $criterias_tabsname = PluginTicketalertsAlertGroupCriteria::getCriteriasNameForDropdown(
                PluginTicketalertsAlertGroupCriteria::$criterias_types
            );

            $groupOperators = $self->getGroupsOperatorsArray($group_criterias);

            echo "<div class='center'>";

            $previousGroup = null;
            $previousMainGroup = null;
            $previousSubGroup = null;
            foreach ($group_criterias as $id => $value) {
                $currentGroup = $value['group_number'];
                $split = explode('_', $value['group_number']);
                $currentMainGroup = $split[0];
                $currentSubGroup = $split[1];
                // closing of previous
                if ($previousMainGroup !== null) {
                    if ($previousSubGroup !== null
                        && ($previousMainGroup != $currentMainGroup
                        || $previousSubGroup != $currentSubGroup)) {
                        echo "</tbody></table>"; // close previous subgroup table
                        // see Rule::showMinimalForm() (10.0.16)
                        $previousGroup = $previousMainGroup . '_' . $previousSubGroup;
                        if ($canedit) {
                            $baseUrl = Plugin::getWebDir('ticketalerts');
                            // javascript to sort element in the group
                            $js = <<<JAVASCRIPT
                         $(function() {
                            sortable('#ticketsalerts-{$previousGroup}', {
                               handle: '.grip-criteria',
                               placeholder: '<tr><td colspan="7" class="sortable-placeholder">&nbsp;</td></tr>'
                            })[0].addEventListener('sortupdate', function(e) {
                               var sort_detail          = e.detail;
                               var criteria_id              = sort_detail.item.dataset.criteriaId;
                               var criteria_group = "{$previousGroup}";
                               var new_index            = sort_detail.destination.index;
                               var old_index            = sort_detail.origin.index;
                               var ref_id               = sort_detail.destination.itemsBeforeUpdate[new_index].dataset.criteriaId;
                               var sort_action          = 'after';
                
                               if (old_index > new_index) {
                                  sort_action = 'before';
                               }
                
                               $.post('{$baseUrl}'+'/ajax/criteria.php', {
                                  'action': 'move_criteria',
                                  'criteria_id': criteria_id,
                                  'criteria_group': criteria_group,
                                  'sort_action': sort_action,
                                  'ref_id': ref_id,
                               });
                
                               displayAjaxMessageAfterRedirect();
                            });
                         });
JAVASCRIPT;
                            echo Html::scriptBlock($js);

                            if ($previousMainGroup == $currentMainGroup) {
                                echo "<div class='my-2'>";
                                $name = 'groups_operators[' . $previousGroup . '-' . $currentGroup . ']';
                                $onChange = '$.post("'. $baseUrl .'"+"/ajax/groupoperator.php", {
                                  "value": this.options[this.selectedIndex].value,
                                  "id": ' . $ID . ',
                                  "groups" : "' . $previousGroup . '-' . $currentGroup .'"
                               });
                               displayAjaxMessageAfterRedirect();';
                                Dropdown::showFromArray(
                                    $name,
                                    [
                                        'AND' => __('AND'),
                                        'AND NOT' => __('AND NOT'),
                                        'OR' => __('OR'),
                                        'OR NOT' => __('OR NOT')
                                    ],
                                    [
                                        'value' => $groupOperators[$previousGroup . '-' . $currentGroup],
                                        'rand' => $rand,
                                        'on_change' => $onChange
                                    ]
                                );
                                echo "</div>";
                            }
                        }
                    }
                    if ($previousMainGroup != $currentMainGroup) {
                        Html::closeForm();
                        // close main group accordion body
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "<div class='my-2'>";
                        $name = 'groups_operators[' . $previousMainGroup . '-' . $currentMainGroup . ']';
                        $onChange = '$.post("'. $baseUrl .'"+"/ajax/groupoperator.php", {
                                  "value": this.options[this.selectedIndex].value,
                                  "id": ' . $ID . ',
                                  "groups" : "' . $previousMainGroup . '-'. $currentMainGroup .'"
                               });
                               displayAjaxMessageAfterRedirect();';
                        Dropdown::showFromArray(
                            $name,
                            [
                                'AND' => __('AND'),
                                'AND NOT' => __('AND NOT'),
                                'OR' => __('OR'),
                                'OR NOT' => __('OR NOT')
                            ],
                            [
                                'value' => $groupOperators[$previousMainGroup . '-' . $currentMainGroup],
                                'rand' => $rand,
                                'on_change' => $onChange
                            ]
                        );
                        echo "</div>";
                    }
                }
                // opening of new
                if ($previousMainGroup !== $currentMainGroup) {
                    $previousSubGroup = null;
                    $rand = mt_rand();
                    $previousMainGroup = $currentMainGroup;
                    // start group accordion
                    echo "<div class='accordion tab_bg_2 rounded' style='border:2px solid " . self::GROUP_COLORS[$previousMainGroup] . "'>";
                    echo "<div class='accordion-item tab_bg_2' style='border: none'>";
                    echo "<h2 class='accordion-header tab_bg_2' id='header$previousMainGroup' style='border-bottom: 1px solid lightgrey'>
                    <button 
                        class='accordion-button tab_bg_2 p-1' 
                        type='button' 
                        data-bs-toggle='collapse' 
                        data-bs-target='#collapse$previousMainGroup' 
                        aria-expanded='false' 
                        aria-controls='collapse$previousMainGroup'>";
                    echo __('Group') . ' ' . $previousMainGroup;
                    echo "</button>
                    </h3>";
                    echo "<div 
                        id='collapse$previousMainGroup' 
                        class='accordion-collapse collapse show tab_bg_2' 
                        aria-labelledby='header$previousMainGroup'>
                    <div class='accordion-body tab_bg_2' style='padding-right: 0px; padding-left: 0px'>";
                    if ($canedit) {
                        Html::openMassiveActionsForm('mass' . "PluginTicketalertsAlertGroupCriteria" . $rand);
                        $massiveactionparams['container'] = 'mass' . 'PluginTicketalertsAlertGroupCriteria' . $rand;
                        $massiveactionparams['specific_actions'] = ['delete' => _x('button', 'Delete permanently')];
                        Html::showMassiveActions($massiveactionparams);
                    }
                }
                if ($previousSubGroup !== $currentSubGroup) {
                    // start subgroup table
                    $previousSubGroup = $currentSubGroup;
                    echo "<h4>" . __('Group') . ' ' . $currentGroup . "</h4>";
                    echo "<table class='table table-hover card-table'>
                    <tbody class='sortable-criterias' id='ticketsalerts-" . $currentGroup . "'>";
                }
                echo "<tr class='tab_bg_1' data-criteria-id='" . $value['id'] . "'>";
                echo "<td>";
                if ($canedit) {
                    Html::showMassiveActionCheckBox("PluginTicketalertsAlertGroupCriteria", $id);
                }
                echo "</td>";
                echo "<td>" . PluginTicketalertsAlertGroupCriteria::getRuleCriterionValueByCriterion($value) . "</td>";
                echo "<td>" . $criterias_tabsname[$value['criteria']] . "</td>";
                echo "<td>" . PluginTicketalertsAlertGroupCriteria::getValueOfNegation(
                        $value['rule_criterion_negation']
                    ) . "</td>";
                echo "<td>";
                echo PluginTicketalertsAlertGroupCriteria::getFilterValueByCriterion($value);
                echo $value['is_recursive'] ? ' ('.__('Recursive').')' : '';
                echo "</td>";
                echo "<td><i class='fas fa-grip-horizontal grip-criteria'></i></td>";
                echo "</tr>";
            }

            // close last group table
            echo "</tbody></table>"; // close previous group table
            // see Rule::showMinimalForm() (10.0.16)
            if ($canedit) {
                $previousGroup = $previousMainGroup . '_' . $previousSubGroup;
                $baseUrl = Plugin::getWebDir('ticketalerts');
                // javascript to sort element in the group
                $js = <<<JAVASCRIPT
                         $(function() {
                            sortable('#ticketsalerts-{$previousGroup}', {
                               handle: '.grip-criteria',
                               placeholder: '<tr><td colspan="7" class="sortable-placeholder">&nbsp;</td></tr>'
                            })[0].addEventListener('sortupdate', function(e) {
                               var sort_detail          = e.detail;
                               var criteria_id              = sort_detail.item.dataset.criteriaId;
                               var criteria_group = "{$previousGroup}";
                               var new_index            = sort_detail.destination.index;
                               var old_index            = sort_detail.origin.index;
                               var ref_id               = sort_detail.destination.itemsBeforeUpdate[new_index].dataset.criteriaId;
                               var sort_action          = 'after';
                
                               if (old_index > new_index) {
                                  sort_action = 'before';
                               }
                
                               $.post('{$baseUrl}'+'/ajax/criteria.php', {
                                  'action': 'move_criteria',
                                  'criteria_id': criteria_id,
                                  'criteria_group': criteria_group,
                                  'sort_action': sort_action,
                                  'ref_id': ref_id,
                               });
                
                               displayAjaxMessageAfterRedirect();
                            });
                         });
JAVASCRIPT;
                echo Html::scriptBlock($js);
                Html::closeForm();
                // close accordion body
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
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
    function showForm($ID, $options = [])
    {
        global $CFG_GLPI;

        $canedit = Session::haveRight(PluginTicketalertsAlertGroup::$rightname, READ);
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
        echo "<td>" . __('Comment') . "</td>";

        echo "<td class='center'>";
        echo "<textarea cols='90' rows='7' name='comment' >" . $this->fields["comment"] . "</textarea>";
        echo "</td>";
        echo "</tr>";

        if ($canedit) {
            $this->showFormButtons($options);
        }
    }

    //Massive action
    function getSpecificMassiveActions($checkitem = null)
    {
        $isadmin = static::canUpdate();
        $actions = parent::getSpecificMassiveActions($checkitem);

        if (Session::getCurrentInterface() == 'central') {
            if ($isadmin) {
                if (Session::haveRight('transfer', READ) && Session::isMultiEntitiesMode()) {
                    $actions['PluginTicketalertsAlertGroup' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __(
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
    static function showCentralList()
    {
        if (!Session::haveRight("plugin_ticketalerts_manage_groupalert", READ)) {
            return false;
        }

        $alertgroup = new self();
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

    /**
     * Get the array with the operators to put between each criteria group for the current instance
     * @param $criterias array all PluginTicketalertsAlertGroupCriteria associated with the current instance
     * @return array
     */
    public function getGroupsOperatorsArray($criterias) {
        $groupOperators = [];
        $previousGroup = null;
        $previousSubGroup = null;
        // create array of necessary groups based on criterias
        foreach ($criterias as $criteria) {
            $currentGroup = $criteria['group_number'];
            $split = explode('_', $currentGroup);
            if ($previousGroup !== $split[0]) {
                // operator between main groups
                if ($previousGroup) {
                    $groupOperators[$previousGroup . '-' . $split[0]] = 'AND';
                }
                $previousGroup = $split[0];
                $previousSubGroup = null;
            }
            if ($previousSubGroup !== $split[1]) {
                // operator between sub groups
                if ($previousSubGroup) {
                    $groupOperators[$previousGroup . '_' . $previousSubGroup . '-' . $currentGroup] = 'AND';
                }
                $previousSubGroup = $split[1];
            }
        }
        // update it with the values set in DB
        if ($this->fields['groups_operators']) {
            $operators = json_decode($this->fields['groups_operators'], true);
            if ($operators && is_array($operators)) {
                foreach ($groupOperators as $groups => $operator) {
                    if (isset($operators[$groups])) {
                        $groupOperators[$groups] = $operators[$groups];
                    }
                }
            }
        } else { // legacy support
            if (isset($self->fields['group_criterion'])) {
                foreach ($groupOperators as $groups => $operator) {
                    $groupOperators[$groups] = $self->fields['group_criterion'];
                }
            }
        }

        return $groupOperators;
    }
}
